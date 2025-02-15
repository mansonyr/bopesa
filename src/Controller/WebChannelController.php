<?php

namespace App\Controller;

use App\Entity\Channel;
use App\Entity\Task;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/web/channels')]
final class WebChannelController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager
    ) {}

    #[Route('', name: 'app_web_channel_list')]
    public function list(): Response
    {
        $user = $this->getUser();
        if (!$user) {
            throw $this->createAccessDeniedException('Vous devez être connecté pour accéder à cette page');
        }

        //  Si c'est un admin, montrer tous les canaux
        if (in_array('ROLE_ADMIN', $user->getRoles())) {
            $channels = $this->entityManager->getRepository(Channel::class)->findAll();
        } else {
            // Sinon, montrer uniquement les canaux de l'utilisateur
            $channels = $this->entityManager->getRepository(Channel::class)->findBy(['user' => $user]);
        }
        
        return $this->render('channel/index.html.twig', [
            'channels' => $channels,
        ]);
    }

    #[Route('/{id}', name: 'app_web_channel_show')]
    public function show(Channel $channel, Request $request): Response
    {
        $user = $this->getUser();
        if (!$user) {
            throw $this->createAccessDeniedException('Vous devez être connecté pour accéder à cette page');
        }

        // Vérifier que l'utilisateur a accès à ce canal
        if (!in_array('ROLE_ADMIN', $user->getRoles()) && $channel->getUser() !== $user) {
            throw $this->createAccessDeniedException('Vous n\'avez pas accès à ce canal');
        }

        // Filtrer les canaux selon le rôle de l'utilisateur
        if (in_array('ROLE_ADMIN', $user->getRoles())) {
            $channels = $this->entityManager->getRepository(Channel::class)->findAll();
        } else {
            $channels = $this->entityManager->getRepository(Channel::class)->findBy(['user' => $user]);
        }
        
        // Get active tab and selected task
        $activeTab = $request->query->get('tab', 'tasks');
        $selectedTask = null;
        
        if ($activeTab === 'subtasks') {
            $taskId = $request->query->get('task');
            if ($taskId) {
                $selectedTask = $this->entityManager->getRepository(Task::class)->find($taskId);
            } elseif (count($channel->getTasks()) > 0) {
                $selectedTask = $channel->getTasks()[0];
            }
        }
        
        return $this->render('dashboard/index.html.twig', [
            'channels' => $channels,
            'currentChannel' => $channel,
            'activeTab' => $activeTab,
            'selectedTask' => $selectedTask,
        ]);
    }
}
