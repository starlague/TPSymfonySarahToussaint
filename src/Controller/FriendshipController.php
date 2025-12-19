<?php

namespace App\Controller;

use App\Entity\Friendship;
use App\Entity\User;
use App\Repository\FriendshipRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class FriendshipController extends AbstractController {
    #[Route('/amis/{id}', name: 'app_liste_amis')]
    public function listeAmis($id, FriendshipRepository $friendship): Response {
        $currentUser = $this->getUser();
        
        if (!$currentUser) {
            return $this->redirectToRoute('app_register');
        }
        
        $friendships = $friendship->findAcceptedFriendships($id);
        //récuperer l'id des users où le user son id en receiver ou requester et que la demande à pour status 'accepted'
        $friends = array_map(function($f) use ($id) {
            return $f->getRequester()->getId() === $id
                ? $f->getReceiver()
                : $f->getRequester();
        }, $friendships);

        return $this->render('friendship/listeAmis.html.twig', [
            'friends' => $friends,
        ]);
    }

    #[Route('amis/ajouter/{id}', name:'app_ajouter_amis')]
    public function ajouterAmis($id, EntityManagerInterface $em, UserRepository $user): Response {
        $currentUser = $this->getUser();
        $friend = $user->find($id);
        $users = $user->findBy(
            [],
            ['username' => 'ASC']
        );


        $friendship = new Friendship();

        $friendship->setRequester($currentUser);
        $friendship->setReceiver($friend);
        $friendship->setStatus('pending');

        $em->persist($friendship);
        $em->flush();

        return $this->redirectToRoute('app_liste_utilisateurs');
    }

    #[Route('amis/demande/{id}', name:'app_demande')]
    public function demandeAmis($id, FriendshipRepository $friendship): Response {
        $currentUser = $this->getUser();
        if (!$currentUser) {
            return $this->redirectToRoute('app_register');
        }
        
        //récupère la demande d'ami ou le user a son id en receiver
        $friendships = $friendship->findBy(
             array('receiver'=> $id,
                            'status'=>'pending'),
        );

        //récupère l'id des requester user qui ont le user en receiver
        $friends = array_map(fn($f) => $f->getRequester(), $friendships);

        return $this->render('friendship/demandesAmis.html.twig', [
            'friends' => $friends
        ]);
    }

    #[Route('amis/accepte/{id}', name:'app_accepte')]
    public function accepteAmis($id, EntityManagerInterface $em, FriendshipRepository $friendship, User $user): Response {
        $currentUser = $this->getUser();
        if (!$currentUser) {
            return $this->redirectToRoute('app_register');
        }

        //id reciver et id requester pour set le status en 'accepted'
        $receiverId = $currentUser->getId();
        $requesterId = $id;

        $friendship = $friendship->findOneBy([
            'requester' => $requesterId,
            'receiver' => $receiverId,
            'status' => 'pending',
        ]);

        if ($friendship) {
            $friendship->setStatus('accepted');
            $em->flush();
        }

        return $this->redirectToRoute('app_liste_amis', ['id' => $receiverId]);
    }
    

}