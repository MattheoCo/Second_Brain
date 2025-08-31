<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ShoppingController extends AbstractController
{
    #[Route('/shopping', name: 'app_shopping')]
    public function index(): Response
    {
        return $this->render('shopping/index.html.twig', [
            'title' => 'Liste de courses',
        ]);
    }
}
