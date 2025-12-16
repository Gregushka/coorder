<?php

namespace App\Controller;

use App\Entity\Trip;
use App\Entity\MediaCard;
use App\Entity\AttachedFile;
use App\Form\TripType;
use App\Form\MediaCardType;
use App\Repository\TripRepository;
use App\Repository\MediaCardRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Mime\MimeTypes;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\DomCrawler\Crawler;

final class MainController extends AbstractController
{
    #[Route('/main', name: 'app_main')]
    public function index(TripRepository $tripRepository): Response
    {
        $trips = $tripRepository->findAll();
        
        return $this->render('main/index.html.twig', [
            'trips' => $trips,
        ]);
    }

    #[Route('/main/trip/new', name: 'app_new_trip')]
    public function newTrip(Request $request): Response
    {
        $trip = new Trip();
        $form = $this->createForm(TripType::class, $trip);
        $form->handleRequest($request);

        return $this->render('main/new_trip.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/main/trip/save', name: 'app_save_new_trip', methods: ['POST'])]
    public function saveNewTrip(Request $request, EntityManagerInterface $entityManager): Response
    {
        $trip = new Trip();
        $form = $this->createForm(TripType::class, $trip);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($trip);
            $entityManager->flush();

            $this->addFlash('success', 'Trip created successfully!');
            return $this->redirectToRoute('app_main');
        }

        // If form is invalid, re-render with errors
        return $this->render('main/new_trip.html.twig', [
            'form' => $form->createView(),
            'errors' => true,
        ]);
    }

    #[Route('/main/trip/edit/{id}', name: 'app_edit_trip')]
    public function editTrip(Request $request, Trip $trip, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(TripType::class, $trip);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Trip updated successfully!');
            return $this->redirectToRoute('app_main');
        }

        return $this->render('main/edit_trip.html.twig', [
            'form' => $form->createView(),
            'trip' => $trip,
        ]);
    }

    #[Route('/main/trip/{id}/media', name: 'app_list_media')]
    public function listMedia(Trip $trip, MediaCardRepository $mediaCardRepository): Response
    {
        $mediaCards = $mediaCardRepository->findBy(['trip' => $trip]);

        return $this->render('main/list_media.html.twig', [
            'trip' => $trip,
            'mediaCards' => $mediaCards,
        ]);
    }

    #[Route('/main/trip/{id}/media/new', name: 'app_new_media')]
    public function newMedia(Request $request, Trip $trip): Response
    {
        $form = $this->createForm(MediaCardType::class);
        $form->handleRequest($request);

        return $this->render('main/new_media.html.twig', [
            'form' => $form->createView(),
            'trip' => $trip,
        ]);
    }

    #[Route('/main/trip/{id}/media/save', name: 'app_save_new_media', methods: ['POST'])]
    public function saveNewMedia(
        Request $request, 
        Trip $trip, 
        EntityManagerInterface $entityManager,
        SluggerInterface $slugger,
        string $projectDir
    ): Response {
        $form = $this->createForm(MediaCardType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $uploadedFile = $form->get('file')->getData();

            if (!$uploadedFile) {
                $this->addFlash('error', 'Please select a file to upload.');
                return $this->render('main/new_media.html.twig', [
                    'form' => $form->createView(),
                    'trip' => $trip,
                ]);
            }

            try {
                // Process file upload
                $originalFilename = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$uploadedFile->guessExtension();

                // Create upload directory if it doesn't exist
                $uploadDirectory = $projectDir . '/public/uploads/media';
                $filesystem = new Filesystem();
                $filesystem->mkdir($uploadDirectory, 0755);

                // Move file to upload directory
                $uploadedFile->move($uploadDirectory, $newFilename);
                $filePath = $uploadDirectory . '/' . $newFilename;

                // Create AttachedFile entity
                $attachedFile = new AttachedFile();
                $attachedFile->setName($uploadedFile->getClientOriginalName());
                $attachedFile->setHash(hash_file('sha256', $filePath));
                $attachedFile->setSize($uploadedFile->getSize());
                $attachedFile->setPath('/uploads/media/' . $newFilename);
                $attachedFile->setUpdatedAt(new \DateTimeImmutable());

                // Get MIME type
                $mimeTypes = new MimeTypes();
                $mimeType = $mimeTypes->guessMimeType($filePath);
                if ($mimeType) {
                    [$mimeMain, $mimeSub] = explode('/', $mimeType);
                    $attachedFile->setMimeType($mimeMain);
                    $attachedFile->setMimeSubtype($mimeSub);
                }

                // Get EXIF data if it's an image
                if (function_exists('exif_read_data') && in_array($mimeType, ['image/jpeg', 'image/tiff'])) {
                    try {
                        $exifData = exif_read_data($filePath, 'EXIF', true);
                        $attachedFile->setExif($exifData ?: []);
                    } catch (\Exception $e) {
                        // EXIF reading failed, leave empty array
                        $attachedFile->setExif([]);
                    }
                }

                // Parse XML file to create MediaCard
                if ($mimeType === 'text/xml' || $mimeType === 'application/xml') {
                    try {
                        $xmlContent = file_get_contents($filePath);
                        $crawler = new Crawler($xmlContent);
                        
                        // Extract data from XML
                        $mediaCard = new MediaCard();
                        $mediaCard->setTrip($trip);
                        $mediaCard->setName($data['userMediaName'] ?? null);
                        $mediaCard->setMediaCreated(new \DateTimeImmutable());
                        
                        // Extract system info
                        $systemElement = $crawler->filter('Properties System');
                        if ($systemElement->count() > 0) {
                            $mediaCard->setSystemId($systemElement->attr('systemId') ?? '');
                            $mediaCard->setSystemKind($systemElement->attr('systemKind') ?? '');
                            $mediaCard->setMasterVersion($systemElement->attr('masterVersion') ?? '');
                        }
                        
                        // Extract attached media info
                        $attachedElement = $crawler->filter('Properties Attached');
                        if ($attachedElement->count() > 0) {
                            $mediaCard->setMediaId($attachedElement->attr('mediaId') ?? '');
                            $mediaCard->setMediaKind($attachedElement->attr('mediaKind') ?? '');
                            $mediaCard->setMediaName($attachedElement->attr('mediaName') ?? '');
                        }
                        
                        // Extract contents as JSON
                        $contents = [];
                        $crawler->filter('Contents Material')->each(function (Crawler $material) use (&$contents) {
                            $contents[] = [
                                'uri' => $material->attr('uri'),
                                'type' => $material->attr('type'),
                                'videoType' => $material->attr('videoType'),
                                'audioType' => $material->attr('audioType'),
                                'fps' => $material->attr('fps'),
                                'dur' => $material->attr('dur'),
                                'ch' => $material->attr('ch'),
                                'aspectRatio' => $material->attr('aspectRatio'),
                                'offset' => $material->attr('offset'),
                                'umid' => $material->attr('umid'),
                                'relevantInfo' => $material->filter('RelevantInfo')->each(function (Crawler $info) {
                                    return [
                                        'uri' => $info->attr('uri'),
                                        'type' => $info->attr('type'),
                                    ];
                                }),
                            ];
                        });
                        
                        $mediaCard->setContents($contents);
                        $attachedFile->setMediaCard($mediaCard);
                        
                        // Persist entities
                        $entityManager->persist($mediaCard);
                        $entityManager->persist($attachedFile);
                        $entityManager->flush();
                        
                        $this->addFlash('success', 'Media card created successfully!');
                        return $this->redirectToRoute('app_list_media', ['id' => $trip->getId()]);
                        
                    } catch (\Exception $e) {
                        $this->addFlash('error', 'Error parsing XML file: ' . $e->getMessage());
                        return $this->render('main/new_media.html.twig', [
                            'form' => $form->createView(),
                            'trip' => $trip,
                        ]);
                    }
                } else {
                    // For non-XML files, create a basic MediaCard
                    $mediaCard = new MediaCard();
                    $mediaCard->setTrip($trip);
                    $mediaCard->setName($data['userMediaName'] ?? null);
                    $mediaCard->setMediaCreated(new \DateTimeImmutable());
                    $mediaCard->setMediaId(Uuid::v4()->toRfc4122());
                    $mediaCard->setSystemId('');
                    $mediaCard->setSystemKind('Unknown');
                    $mediaCard->setMasterVersion('');
                    $mediaCard->setMediaKind('Unknown');
                    $mediaCard->setMediaName($uploadedFile->getClientOriginalName());
                    $mediaCard->setContents([]);
                    
                    $attachedFile->setMediaCard($mediaCard);
                    
                    $entityManager->persist($mediaCard);
                    $entityManager->persist($attachedFile);
                    $entityManager->flush();
                    
                    $this->addFlash('success', 'Media card created successfully!');
                    return $this->redirectToRoute('app_list_media', ['id' => $trip->getId()]);
                }
                
            } catch (FileException $e) {
                $this->addFlash('error', 'File upload failed: ' . $e->getMessage());
                return $this->render('main/new_media.html.twig', [
                    'form' => $form->createView(),
                    'trip' => $trip,
                ]);
            }
        }

        // If form is invalid, re-render with errors
        return $this->render('main/new_media.html.twig', [
            'form' => $form->createView(),
            'trip' => $trip,
        ]);
    }

    #[Route('/main/trip/view/{id}', name: 'app_view_trip')]
    public function viewTrip(Trip $trip): Response
    {
        return $this->render('main/view_trip.html.twig', [
            'trip' => $trip,
        ]);
    }
}