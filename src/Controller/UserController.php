<?php

namespace App\Controller;


use App\Entity\User;
use App\Form\EditUserType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

class UserController extends AbstractController {
    #[Route('/utilisateurs', name:'app_liste_utilisateurs')]
    public function listeUtilisateurs(UserRepository $user): Response {
        $currentUser = $this->getUser();
        if (!$currentUser) {
            return $this->redirectToRoute('app_register');
        }

        $users = $user->findBy(
            [],
            ['username' => 'ASC']
        );
        return $this->render('user/listeUtilisateurs.html.twig', [
            'users' => $users,
        ]);
    }


    #[Route('/profil', name:'app_profil')]
    public function afficherProfil(): Response {
        $currentUser = $this->getUser();
        if (!$currentUser) {
           return $this->redirectToRoute('app_register');;
        }
        
        return $this->render('user/profil.html.twig', [
            'user'=> $currentUser,
        ]);
    }

    #[Route('/modifier/profil/{id}', name:'app_modifier_profil', methods: ['GET', 'POST'])]
    public function modifierUtilisateur($id, EntityManagerInterface $em, Request $request, SluggerInterface $slugger, UserRepository $u, UserPasswordHasherInterface $passwordHasher): Response {
        $user = $u->find($id);

        if (!$user) {
            throw $this->createAccessDeniedException();
        }

        $form = $this->createForm(EditUserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $avatarFile = $form->get('avatarFile')->getData();

            if ($avatarFile) {
                $originalAvatarName = pathinfo($avatarFile->getClientOriginalName(), PATHINFO_FILENAME);
                $dossier = __DIR__ .'/../../public/img/avatar';

                $safeFilename = $slugger->slug($originalAvatarName);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$avatarFile->guessExtension();

                $avatarFile->move($dossier, $newFilename);
                
                $user->setAvatar($newFilename);
            }

            $newPlainPassword = $form->get('password')->getData();

            if ($newPlainPassword) {
            $hashedPassword = $passwordHasher->hashPassword($user, $newPlainPassword);
            $user->setPassword($hashedPassword);
    }

            $em->persist($user);
            $em->flush();

            return $this->redirectToRoute('app_profil');
        }

        return $this->render('user/modifierProfil.html.twig', [
            'form'=> $form,
            'user' => $user,
        ]); 
    }

    #[Route('/supprimer/utilisateur/{id}', name:'app_supprimer_utilisateur')]
    public function supprimerUtiliseur(EntityManagerInterface $em, User $user): Response {
        $em->remove($user);
        $em->flush();
        return $this->redirectToRoute('app_liste_utilisateurs');
    }
}

