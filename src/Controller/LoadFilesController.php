<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class LoadFilesController extends AbstractController
{
    #[Route('/load/files', name: 'app_load_files')]
    public function index(): Response
    {
        return $this->render('load_files/load_files.html.twig', [
            'controller_name' => 'LoadFilesController',
        ]);
    }
}
