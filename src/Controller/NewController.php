<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class NewController extends AbstractController
{
    /**
     * @Route("/", name="techno_home")
     */
    public function index(): Response
    {
        return $this->render('new/home.html.twig', [
            'controller_name' => 'NewController',
        ]);
    }
}
