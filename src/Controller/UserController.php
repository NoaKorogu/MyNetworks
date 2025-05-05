<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Entity\Networks;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[Route('/user')]
final class UserController extends AbstractController
{
    #[Route(name: 'app_user_index', methods: ['GET'])]
    public function index(UserRepository $userRepository): Response
    {
        return $this->render('user/index.html.twig', [
            'users' => $userRepository->findAll(),
        ]);
    }
    
    #[Route('/new', name: 'app_user_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            // Définir le réseau en fonction du rôle
            $roleToNetwork = [
                'ROLE_EDF' => 1,
                'ROLE_WATER' => 2,
                'ROLE_FILIBUS' => 3,
                'ROLE_ADMIN' => 4,
            ];
    
            $role = $user->getRole();
            if (!isset($roleToNetwork[$role])) {
                return new JsonResponse(['error' => 'Invalid role.'], Response::HTTP_BAD_REQUEST);
            }
    
            $network = $entityManager->getRepository(Networks::class)->find($roleToNetwork[$role]);
            if (!$network) {
                return new JsonResponse(['error' => 'Network not found for the given role.'], Response::HTTP_BAD_REQUEST);
            }
    
            $user->setNetwork($network);
    
            // Hacher le mot de passe avant de l'enregistrer
            $hashedPassword = $passwordHasher->hashPassword($user, $user->getPassword());
            $user->setPassword($hashedPassword);
    
            $entityManager->persist($user);
            $entityManager->flush();
    
            return $this->redirectToRoute('app_login', [], Response::HTTP_SEE_OTHER);
        }
    
        return $this->render('user/new.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[Route('/test-session', name: 'test_session')]
    public function testSession(Request $request): Response
    {
        $session = $request->getSession();
        $session->set('test_key', 'test_value');

        return new Response('Session test: ' . $session->get('test_key'));
    }

    #[Route('/{id}', name: 'app_user_show', methods: ['GET'])]
    public function show(User $user): Response
    {
        return $this->render('user/show.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_user_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('user/edit.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_user_delete', methods: ['POST'])]
    public function delete(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($user);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
    }
}
