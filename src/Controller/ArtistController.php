<?php

namespace App\Controller;

use App\Repository\ArtistRepository;
use App\Repository\CategoryRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ArtistController extends AbstractController
{
    /**
     * @Route("/artist", name="artist")
     */
         public function index(CategoryRepository $CategoryRepository, ArtistRepository $ArtistRepository): Response
    {
           $categories = $CategoryRepository->findAll();
           $artists = $ArtistRepository->findall();
           //dd($categories);
           return $this->render('artist/artist.html.twig', [
            'categories' => $categories,
           'artists' => $artists,
            
        ]);
    }
}
