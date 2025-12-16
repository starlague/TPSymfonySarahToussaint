<?php

namespace App\Controller;


use App\Form\EditUserType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

class UserController extends AbstractController {
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


    #[Route('/profil', name:'app_profil')]
    public function afficherProfil(): Response {
        $user = $this->getUser();

        if (!$user) {
            throw $this->createAccessDeniedException();
        }
        return $this->render('home/profil.html.twig', [
            'user'=> $user,
        ]);
    }

    #[Route('/modifier/{id}', name:'app_modifier_profil', methods: ['GET', 'POST'])]
    public function modifierUtilisateur($id, EntityManagerInterface $em, Request $request, SluggerInterface $slugger, UserRepository $u): Response {
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

            $em->persist($user);
            $em->flush();

            return $this->redirectToRoute('app_profil');
        }

        return $this->render('home/modifierProfil.html.twig', [
            'form'=> $form,
            'user' => $user,
        ]);
    }


}

