<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ListFilesController extends AbstractController
{
    #[Route('/list/files', name: 'app_list_files')]
    public function index(): Response
    {
        return $this->render('list_files/list_files.html.twig', [
            'controller_name' => 'ListFilesController',
        ]);
    }
}
