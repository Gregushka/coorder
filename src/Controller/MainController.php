<?php

namespace App\Controller;

use App\Entity\AttachedFile;
use App\Entity\MediaCard;
use App\Entity\Trip;
use App\Form\MediaType;
use App\Form\TripType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\MimeTypes;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

final class MainController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {}

    #[Route('/main', name: 'app_main')]
    public function index(): Response
    {
        $trips = $this->entityManager->getRepository(Trip::class)->findAll();

        return $this->render('main/index.html.twig', [
            'trips' => $trips,
        ]);
    }

    #[Route('/view-trip/{id}', name: 'app_view_trip')]
    public function viewTrip(Trip $trip): Response
    {
        return $this->render('main/view_trip.html.twig', [
            'trip' => $trip,
        ]);
    }

    #[Route('/new-trip', name: 'app_new_trip')]
    public function newTrip(): Response
    {
        $trip = new Trip();
        $form = $this->createForm(TripType::class, $trip, [
            'action' => $this->generateUrl('app_save_new_trip'),
            'method' => 'POST'
        ]);

        return $this->render('main/trip_form.html.twig', [
            'form' => $form->createView(),
            'title' => 'New Trip',
            'is_edit' => false
        ]);
    }

    #[Route('/new-trip/save', name: 'app_save_new_trip', methods: ['POST'])]
    public function newTripSave(Request $request): Response
    {
        $trip = new Trip();
        $form = $this->createForm(TripType::class, $trip);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($trip);
            $this->entityManager->flush();

            return $this->redirectToRoute('app_main');
        }

        // On failure, re-render the form with errors (shadowed red logic handled in twig/theme)
        return $this->render('main/trip_form.html.twig', [
            'form' => $form->createView(),
            'title' => 'New Trip',
            'is_edit' => false
        ]);
    }

    #[Route('/edit-trip/{id}', name: 'app_edit_trip')]
    public function editTrip(Request $request, Trip $trip): Response
    {
        $form = $this->createForm(TripType::class, $trip);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();
            return $this->redirectToRoute('app_main');
        }

        return $this->render('main/trip_form.html.twig', [
            'form' => $form->createView(),
            'title' => 'Edit Trip',
            'is_edit' => true
        ]);
    }

    #[Route('/trip/{id}/media', name: 'app_list_media')]
    public function listMedia(Trip $trip): Response
    {
        return $this->render('main/media_list.html.twig', [
            'trip' => $trip,
            'mediaCards' => $trip->getMediaCards(),
        ]);
    }

    #[Route('/trip/{id}/media/new', name: 'app_new_media')]
    public function newMedia(Trip $trip): Response
    {
        $form = $this->createForm(MediaType::class, null, [
            'action' => $this->generateUrl('app_save_new_media', ['id' => $trip->getId()]),
        ]);

        return $this->render('main/media_form.html.twig', [
            'form' => $form->createView(),
            'trip' => $trip,
        ]);
    }

    #[Route('/trip/{id}/media/save', name: 'app_save_new_media', methods: ['POST'])]
    public function saveNewMedia(
        Request $request, 
        Trip $trip, 
        SluggerInterface $slugger,
        #[Autowire('%uploads_directory%')] string $uploadsDirectory
    ): Response
    {
        $form = $this->createForm(MediaType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $file */
            $file = $form->get('file')->getData();
            $userMediaName = $form->get('userMediaName')->getData();

            if ($file) {
                $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$file->guessExtension();
                $fullPath = $uploadsDirectory . '/' . $newFilename;

                try {
                    // 1. Move file
                    $file->move($uploadsDirectory, $newFilename);

                    // 2. Process File Logic
                    $attachedFile = new AttachedFile();
                    $attachedFile->setName($file->getClientOriginalName());
                    $attachedFile->setPath($newFilename); // Storing relative filename or full path based on pref
                    $attachedFile->setSize(filesize($fullPath));
                    $attachedFile->setHash(md5_file($fullPath));
                    $attachedFile->setUpdatedAt(\DateTimeImmutable::createFromFormat('U', filemtime($fullPath)));

                    // Mime Type
                    $mimeTypes = new MimeTypes();
                    $mimeType = $mimeTypes->guessMimeType($fullPath);
                    if ($mimeType) {
                        $parts = explode('/', $mimeType);
                        $attachedFile->setMimeType($parts[0]);
                        $attachedFile->setMimeSubtype($parts[1] ?? '');
                    }

                    // EXIF
                    // Suppress warnings as not all files have EXIF
                    $exifData = @\exif_read_data($fullPath);
                    $attachedFile->setExif($exifData ?: []);

                    // 3. Process XML and Create MediaCard
                    // Note: This assumes the uploaded file is the XML file described
                    $xmlContent = file_get_contents($fullPath);
                    $xml = simplexml_load_string($xmlContent);
                    
                    if ($xml === false) {
                        throw new \Exception("Invalid XML file");
                    }

                    $mediaCard = new MediaCard();
                    $mediaCard->setTrip($trip);
                    $mediaCard->setName($userMediaName); // User input name
                    
                    // Parse XML Attributes (MediaProfile)
                    $mediaCard->setMediaCreated(new \DateTimeImmutable((string)$xml['createdAt']));

                    // Namespaces might be needed depending on how simplexml reads it, 
                    // but often direct property access works if no prefix usage in code.
                    // Accessing Properties -> System
                    if (isset($xml->Properties->System)) {
                        $mediaCard->setSystemId((string)$xml->Properties->System['systemId']);
                        $mediaCard->setSystemKind((string)$xml->Properties->System['systemKind']);
                        $mediaCard->setMasterVersion((string)$xml->Properties->System['masterVersion']);
                    }

                    // Accessing Properties -> Attached
                    if (isset($xml->Properties->Attached)) {
                        $mediaCard->setMediaId((string)$xml->Properties->Attached['mediaId']);
                        $mediaCard->setMediaKind((string)$xml->Properties->Attached['mediaKind']);
                        $mediaCard->setMediaName((string)$xml->Properties->Attached['mediaName']);
                    }

                    // Accessing Contents
                    $contents = [];
                    if (isset($xml->Contents->Material)) {
                        foreach ($xml->Contents->Material as $material) {
                            $item = [
                                'uri' => (string)$material['uri'],
                                'type' => (string)$material['type'],
                                'videoType' => (string)$material['videoType'],
                                'dur' => (string)$material['dur'],
                                'umid' => (string)$material['umid'],
                            ];
                            $contents[] = $item;
                        }
                    }
                    $mediaCard->setContents($contents);

                    // Persist relations
                    // MediaCard must exist before AttachedFile if validation requires it, 
                    // but usually persist order is handled by Doctrine. 
                    // However, AttachedFile needs a MediaCard relation.
                    $attachedFile->setMediaCard($mediaCard);

                    $this->entityManager->persist($mediaCard);
                    $this->entityManager->persist($attachedFile);
                    $this->entityManager->flush();

                    return $this->redirectToRoute('app_list_media', ['id' => $trip->getId()]);

                } catch (\Exception $e) {
                   // Add flash error or form error
                   $this->addFlash('error', 'Error processing file: ' . $e->getMessage());
                }
            }
        }

        // On failure
        return $this->render('main/media_form.html.twig', [
            'form' => $form->createView(),
            'trip' => $trip,
        ]);
    }
    
    // Placeholder for future Media Edit method mentioned in prompt
    #[Route('/media/edit/{id}', name: 'app_edit_media')]
    public function editMedia(MediaCard $mediaCard): Response
    {
        return new Response("Edit Media Placeholder for " . $mediaCard->getId());
    }
}