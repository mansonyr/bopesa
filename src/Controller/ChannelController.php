<?php

namespace App\Controller;

use App\Entity\Channel;
use App\Entity\Project;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/channels')]
final class ChannelController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager
    ) {}

    #[Route('', name: 'app_channel_list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        $channels = $this->entityManager->getRepository(Channel::class)->findAll();
        
        return $this->json([
            'channels' => array_map(fn(Channel $channel) => [
                'id' => $channel->getId(),
                'name' => $channel->getName(),
                'description' => $channel->getDescription(),
                'isActive' => $channel->isActive(),
                'progress' => $channel->getProgress(),
            ], $channels)
        ]);
    }

    #[Route('/{id}', name: 'app_channel_show', methods: ['GET'])]
    public function show(Channel $channel): JsonResponse
    {
        return $this->json([
            'channel' => [
                'id' => $channel->getId(),
                'name' => $channel->getName(),
                'description' => $channel->getDescription(),
                'isActive' => $channel->isActive(),
                'progress' => $channel->getProgress(),
                'tasks' => array_map(fn($task) => [
                    'id' => $task->getId(),
                    'title' => $task->getTitle(),
                    'status' => $task->getStatus(),
                    'progress' => $task->getProgress(),
                ], $channel->getTasks()->toArray())
            ]
        ]);
    }

    #[Route('', name: 'app_channel_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        
        try {
            $channel = new Channel();
            $channel->setName($data['name']);
            // Ensure description is never null
            $channel->setDescription($data['description'] ?? '');
            $channel->setIsActive($data['isActive'] ?? true);
            $channel->setProgress(0.0);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
        
        if (isset($data['projectId'])) {
            $project = $this->entityManager->getRepository(Project::class)->find($data['projectId']);
            if ($project) {
                $channel->setProject($project);
            }
        }
        
        $this->entityManager->persist($channel);
        $this->entityManager->flush();
        
        return $this->json([
            'channel' => [
                'id' => $channel->getId(),
                'name' => $channel->getName(),
                'description' => $channel->getDescription(),
                'isActive' => $channel->isActive(),
                'progress' => $channel->getProgress(),
            ]
        ], Response::HTTP_CREATED);
    }

    #[Route('/{id}', name: 'app_channel_update', methods: ['PUT'])]
    public function update(Request $request, Channel $channel): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        
        if (isset($data['name'])) {
            $channel->setName($data['name']);
        }
        
        if (isset($data['description'])) {
            $channel->setDescription($data['description']);
        }
        
        if (isset($data['isActive'])) {
            $channel->setIsActive($data['isActive']);
        }
        
        $this->entityManager->flush();
        
        return $this->json([
            'channel' => [
                'id' => $channel->getId(),
                'name' => $channel->getName(),
                'description' => $channel->getDescription(),
                'isActive' => $channel->isActive(),
                'progress' => $channel->getProgress(),
            ]
        ]);
    }

    #[Route('/{id}', name: 'app_channel_delete', methods: ['DELETE'])]
    public function delete(Channel $channel): JsonResponse
    {
        $this->entityManager->remove($channel);
        $this->entityManager->flush();
        
        return $this->json(null, Response::HTTP_NO_CONTENT);
    }
}
