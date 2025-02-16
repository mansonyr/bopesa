<?php

namespace App\Controller;

use App\Entity\Client;
use App\Entity\Project;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\DefaultChannelService;

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(
        Request $request,
        UserPasswordHasherInterface $userPasswordHasher,
        EntityManagerInterface $entityManager,
        DefaultChannelService $defaultChannelService
    ): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_web_channel_list');
        }

        $user = new Client();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            // Set default role
            $user->setRoles(['ROLE_USER']);

            // Create project
            $project = new Project();
            $project->setName($form->get('project')->getData());
            $project->setWebsite($form->get('website')->getData());
            $project->setUtilisateur($user);
            $project->setCreatedAt(new \DateTime());
            $project->setUpdatedAt(new \DateTime());

            $entityManager->persist($user);
            $entityManager->persist($project);
            $entityManager->flush();

            // Copier les canaux par défaut pour le nouvel utilisateur
            $defaultChannelService->copyDefaultChannelsForUser($user);

            // Add success message
            $this->addFlash('success', 'Votre compte a été créé avec succès. Vous pouvez maintenant vous connecter.');
            return $this->redirectToRoute('app_login');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }
}
