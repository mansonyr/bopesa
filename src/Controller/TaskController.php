<?php

namespace App\Controller;

use App\Entity\Channel;
use App\Entity\Task;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/tasks')]
final class TaskController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager
    ) {}

    #[Route('/channel/{channelId}', name: 'app_task_list', methods: ['GET'])]
    public function list(int $channelId): JsonResponse
    {
        $channel = $this->entityManager->getRepository(Channel::class)->find($channelId);
        if (!$channel) {
            return $this->json(['error' => 'Channel not found'], Response::HTTP_NOT_FOUND);
        }

        return $this->json([
            'tasks' => array_map(fn(Task $task) => [
                'id' => $task->getId(),
                'title' => $task->getTitle(),
                'description' => $task->getDescription(),
                'status' => $task->getStatus(),
                'progress' => $task->getProgress(),
                'createdAt' => $task->getCreatedAt()->format('Y-m-d H:i:s'),
                'updatedAt' => $task->getUpdatedAt()->format('Y-m-d H:i:s'),
            ], $channel->getTasks()->toArray())
        ]);
    }

    #[Route('/{id}', name: 'app_task_show', methods: ['GET'])]
    public function show(Task $task): JsonResponse
    {
        return $this->json([
            'task' => [
                'id' => $task->getId(),
                'title' => $task->getTitle(),
                'description' => $task->getDescription(),
                'status' => $task->getStatus(),
                'progress' => $task->getProgress(),
                'createdAt' => $task->getCreatedAt()->format('Y-m-d H:i:s'),
                'updatedAt' => $task->getUpdatedAt()->format('Y-m-d H:i:s'),
                'subTasks' => array_map(fn($subTask) => [
                    'id' => $subTask->getId(),
                    'title' => $subTask->getTitle(),
                    'description' => $subTask->getDescription(),
                    'isCompleted' => $subTask->isIsCompleted(),
                    'resources' => $subTask->getResources(),
                ], $task->getSubtasks()->toArray())
            ]
        ]);
    }

    #[Route('/channel/{channelId}', name: 'app_task_create', methods: ['POST'])]
    public function create(Request $request, int $channelId): JsonResponse
    {
        $channel = $this->entityManager->getRepository(Channel::class)->find($channelId);
        if (!$channel) {
            return $this->json(['error' => 'Channel not found'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);
        
        $task = new Task();
        $task->setTitle($data['title']);
        $task->setDescription($data['description'] ?? '');
        $task->setStatus('not_started');
        $task->setProgress(0);
        $task->setChannel($channel);
        
        $this->entityManager->persist($task);
        $this->entityManager->flush();
        
        return $this->json([
            'task' => [
                'id' => $task->getId(),
                'title' => $task->getTitle(),
                'description' => $task->getDescription(),
                'status' => $task->getStatus(),
                'progress' => $task->getProgress(),
                'createdAt' => $task->getCreatedAt()->format('Y-m-d H:i:s'),
                'updatedAt' => $task->getUpdatedAt()->format('Y-m-d H:i:s'),
            ]
        ], Response::HTTP_CREATED);
    }

    #[Route('/{id}', name: 'app_task_update', methods: ['PUT'])]
    public function update(Request $request, Task $task): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        
        if (isset($data['title'])) {
            $task->setTitle($data['title']);
        }
        
        if (isset($data['description'])) {
            $task->setDescription($data['description']);
        }
        
        if (isset($data['status'])) {
            $task->setStatus($data['status']);
        }
        
        // Mise à jour automatique de la progression basée sur les sous-tâches
        $completedSubTasks = count(array_filter(
            $task->getSubtasks()->toArray(),
            fn($subTask) => $subTask->isIsCompleted()
        ));
        
        $totalSubtasks = count($task->getSubtasks());
        $progress = $totalSubTasks > 0 ? ($completedSubTasks / $totalSubTasks) * 100 : 0;
        $task->setProgress($progress);
        
        // Mise à jour du statut en fonction de la progression
        if ($progress === 0) {
            $task->setStatus('not_started');
        } elseif ($progress === 100) {
            $task->setStatus('completed');
        } else {
            $task->setStatus('in_progress');
        }
        
        $task->setUpdatedAt(new \DateTime());
        $this->entityManager->flush();
        
        // Mise à jour de la progression du canal
        $channel = $task->getChannel();
        $channelProgress = array_reduce(
            $channel->getTasks()->toArray(),
            fn($carry, $task) => $carry + $task->getProgress(),
            0
        ) / count($channel->getTasks());
        
        $channel->setProgress($channelProgress);
        $this->entityManager->flush();
        
        return $this->json([
            'task' => [
                'id' => $task->getId(),
                'title' => $task->getTitle(),
                'description' => $task->getDescription(),
                'status' => $task->getStatus(),
                'progress' => $task->getProgress(),
                'createdAt' => $task->getCreatedAt()->format('Y-m-d H:i:s'),
                'updatedAt' => $task->getUpdatedAt()->format('Y-m-d H:i:s'),
            ]
        ]);
    }

    #[Route('/{id}', name: 'app_task_delete', methods: ['DELETE'])]
    public function delete(Task $task): JsonResponse
    {
        $channel = $task->getChannel();
        $this->entityManager->remove($task);
        $this->entityManager->flush();
        
        // Recalcul de la progression du canal après suppression
        if (count($channel->getTasks()) > 0) {
            $channelProgress = array_reduce(
                $channel->getTasks()->toArray(),
                fn($carry, $task) => $carry + $task->getProgress(),
                0
            ) / count($channel->getTasks());
            
            $channel->setProgress($channelProgress);
            $this->entityManager->flush();
        }
        
        return $this->json(null, Response::HTTP_NO_CONTENT);
    }
}
