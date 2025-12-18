<?php

namespace App\Controller;

use App\Entity\Friendship;
use App\Repository\FriendshipRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class FriendshipController extends AbstractController {
    #[Route('/amis', name: 'app_liste_amis')]
    public function listeAmis(FriendshipRepository $friendshipRepository): Response {
        $user = $this->getUser();
        
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        // Récupérer toutes les amitiés acceptées où l'utilisateur est impliqué
        $friendships = $friendshipRepository->findAcceptedFriendshipsFor($user);
        
        // Extraire les utilisateurs amis (l'autre côté de la relation)
        $amis = [];
        foreach ($friendships as $friendship) {
            if ($friendship->getRequester() === $user) {
                // Si l'utilisateur est le requester, l'ami est le receiver
                $amis[] = $friendship->getReceiver();
            } else {
                // Si l'utilisateur est le receiver, l'ami est le requester
                $amis[] = $friendship->getRequester();
            }
        }

        return $this->render('home/listeAmis.html.twig', [
            'amis' => $amis,
        ]);
    }

    #[Route('amis/ajouter/{id}', name:'app_ajouter_amis')]
    public function ajouterAmis($id, EntityManagerInterface $em, UserRepository $userRepository): Response {
        $user = $this->getUser();
        $friend = $userRepository->find($id);
        $users = $userRepository->findBy(
            [],
            ['username' => 'ASC']
        );


        $friendship = new Friendship();

        $friendship->setRequester($user);
        $friendship->setReceiver($friend);
        $friendship->setStatus('pending');

        $em->persist($friendship);
        $em->flush();

        return $this->redirectToRoute('app_liste_utilisateurs');
    }

    #[Route('amis/accapter/{id}', name:'app_demande_acceptee')]
    public function accepterAmis($id, EntityManagerInterface $em, FriendshipRepository $friendshipRepository): Response {
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }
        
        //récupère la demande d'ami ou le user a son id en receiver
        $friendshipRepository->getReceiver($user);

        return $this->render('home/listeAmis.html.twig');
    }

}