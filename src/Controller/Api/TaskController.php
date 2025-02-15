<?php

namespace App\Controller\Api;

use App\Entity\Task;
use App\Entity\Channel;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/tasks')]
class TaskController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager
    ) {}

    #[Route('', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        try {
            if (!$request->getContent()) {
                return $this->json(['error' => 'No data provided'], 400);
            }

            $data = json_decode($request->getContent(), true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return $this->json(['error' => 'Invalid JSON'], 400);
            }

            if (!isset($data['channelId'], $data['title'], $data['description'], $data['status'])) {
                return $this->json(['error' => 'Missing required fields'], 400);
            }

            if (!in_array($data['status'], [Task::STATUS_TODO, Task::STATUS_IN_PROGRESS, Task::STATUS_DONE])) {
                return $this->json(['error' => 'Invalid status'], 400);
            }
            
            $channel = $this->entityManager->getRepository(Channel::class)->find($data['channelId']);
            if (!$channel) {
                return $this->json(['error' => 'Channel not found'], 404);
            }
            
            $task = new Task();
            $task->setTitle($data['title']);
            $task->setDescription($data['description']);
            $task->setStatus($data['status']);
            if (isset($data['priority'])) {
                $task->setPriority($data['priority']);
            }
            $task->setProgress($data['status'] === Task::STATUS_DONE ? 100 : 0);
            $task->setChannel($channel);
            $task->setCreatedAt(new \DateTime());
            $task->setUpdatedAt(new \DateTime());
            
            $this->entityManager->persist($task);
            $this->updateChannelProgress($channel);
            $this->entityManager->flush();
            
            return $this->json(['success' => true, 'task' => [
                'id' => $task->getId(),
                'title' => $task->getTitle(),
                'description' => $task->getDescription(),
                'status' => $task->getStatus(),
                'priority' => $task->getPriority(),
                'progress' => $task->getProgress(),
            ]]);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 500);
        }
    }
    
    #[Route('/{id}', methods: ['GET'])]
    public function show(Task $task): JsonResponse
    {
        return $this->json([
            'id' => $task->getId(),
            'title' => $task->getTitle(),
            'description' => $task->getDescription(),
            'status' => $task->getStatus(),
            'priority' => $task->getPriority(),
            'progress' => $task->getProgress(),
        ]);
    }
    
    #[Route('/{id}', methods: ['PUT'])]
    public function update(Task $task, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        
        $task->setTitle($data['title']);
        $task->setDescription($data['description']);
        $task->setStatus($data['status']);
        if (isset($data['priority'])) {
            $task->setPriority($data['priority']);
        }
        $task->setProgress($data['status'] === Task::STATUS_DONE ? 100 : ($data['status'] === Task::STATUS_IN_PROGRESS ? 50 : 0));
        
        $this->updateChannelProgress($task->getChannel());
        $this->entityManager->flush();
        
        return $this->json(['success' => true, 'task' => [
            'id' => $task->getId(),
            'title' => $task->getTitle(),
            'description' => $task->getDescription(),
            'status' => $task->getStatus(),
            'priority' => $task->getPriority(),
            'progress' => $task->getProgress(),
        ]]);
    }
    
    #[Route('/{id}', methods: ['DELETE'])]
    public function delete(Task $task): JsonResponse
    {
        $channel = $task->getChannel();
        $this->entityManager->remove($task);
        $this->updateChannelProgress($channel);
        $this->entityManager->flush();
        
        return $this->json(['success' => true]);
    }

    private function updateChannelProgress(Channel $channel): void
    {
        $tasks = $channel->getTasks();
        $totalTasks = count($tasks);

        if ($totalTasks === 0) {
            $channel->setProgress(0);
            return;
        }

        $completedTasks = count(array_filter(
            $tasks->toArray(),
            fn(Task $task) => $task->getStatus() === Task::STATUS_DONE
        ));

        $inProgressTasks = count(array_filter(
            $tasks->toArray(),
            fn(Task $task) => $task->getStatus() === Task::STATUS_IN_PROGRESS
        ));

        // Calcul de la progression :
        // - Tâches terminées = 100%
        // - Tâches en cours = 50%
        // - Tâches à faire = 0%
        $progress = (($completedTasks * 100) + ($inProgressTasks * 50)) / $totalTasks;
        $channel->setProgress($progress);
    }
}
