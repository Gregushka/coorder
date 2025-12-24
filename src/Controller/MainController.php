<?php

namespace App\Controller;

use App\Entity\MediaCard;
use App\Entity\Trip;
use App\Form\MediaType;
use App\Form\TripType;
use App\Service\MediaImportService; // Import the new service
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\File\UploadedFile;

final class MainController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {}

    #[Route('/main', name: 'app_main')]
    public function index(): Response
    {
        $trips = $this->entityManager->getRepository(Trip::class)->findAll();
        return $this->render('main/index.html.twig', ['trips' => $trips]);
    }

    #[Route('/view-trip/{id}', name: 'app_view_trip')]
    public function viewTrip(Trip $trip): Response
    {
        return $this->render('main/view_trip.html.twig', ['trip' => $trip]);
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

    // --- REFACTORED METHOD USING SERVICE ---
    #[Route('/trip/{id}/media/save', name: 'app_save_new_media', methods: ['POST'])]
    public function saveNewMedia(
        Request $request, 
        Trip $trip, 
        MediaImportService $mediaImportService
    ): Response
    {
        $form = $this->createForm(MediaType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $file */
            $file = $form->get('file')->getData();
            $userMediaName = $form->get('userMediaName')->getData();

            if ($file) {
                try {
                    // Delegate logic to service
                    $mediaImportService->importMedia($file, $trip, $userMediaName);
                    
                    $this->addFlash('success', 'Media imported successfully!');
                    return $this->redirectToRoute('app_list_media', ['id' => $trip->getId()]);

                } catch (\Exception $e) {
                    $this->addFlash('error', 'Error processing file: ' . $e->getMessage());
                    // Fall through to re-render form
                }
            }
        }

        return $this->render('main/media_form.html.twig', [
            'form' => $form->createView(),
            'trip' => $trip,
        ]);
    }
    
    #[Route('/media/edit/{id}', name: 'app_edit_media')]
    public function editMedia(MediaCard $mediaCard): Response
    {
        return new Response("Edit Media Placeholder for " . $mediaCard->getId());
    }
}