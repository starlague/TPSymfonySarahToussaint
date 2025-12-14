<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(): Response {
        return $this->render('home/index.html.twig', [
            'web_site_name' => 'carnet d\'amis',
        ]);
    }

    #[Route('/amis', name: 'app_liste_amis')]
    public function listeAmis(): Response {
        return $this->render('home/listeAmis.html.twig');
    }

    #[Route('/inscription', name:'app_inscription')]
    public function inscription(): Response {
        return $this->render('home/inscription.html.twig');
    }
    #[Route('/connexion', name:'app_connexion')]
    public function connexion(): Response {
        return $this->render('home/connexion.html.twig');
    }
}
