<?php

namespace App\Controller;

use App\Repository\UserRepository;
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

    #[Route('/utilisateurs', name:'app_liste_utilisateurs')]
    public function listeUtilisateurs(UserRepository $user): Response {
        $users = $user->findBy(
            [],
            ['username' => 'ASC']
        );
        return $this->render('home/listeUtilisateurs.html.twig', [
            'users' => $users,
        ]);
    }
}
