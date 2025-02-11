<?php

namespace App\Controller;

use App\Entity\Task;
use App\Entity\SubTask;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/subtasks')]
final class SubtaskController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager
    ) {}

    #[Route('/task/{taskId}', name: 'app_subtask_list', methods: ['GET'])]
    public function list(int $taskId): JsonResponse
    {
        $task = $this->entityManager->getRepository(Task::class)->find($taskId);
        if (!$task) {
            return $this->json(['error' => 'Task not found'], Response::HTTP_NOT_FOUND);
        }

        return $this->json([
            'subTasks' => array_map(fn(SubTask $subTask) => [
                'id' => $subTask->getId(),
                'title' => $subTask->getTitle(),
                'description' => $subTask->getDescription(),
                'resources' => $subTask->getResources(),
                'isCompleted' => $subTask->isIsCompleted(),
                'createdAt' => $subTask->getCreatedAt()->format('Y-m-d H:i:s'),
                'updatedAt' => $subTask->getUpdatedAt()->format('Y-m-d H:i:s'),
            ], $task->getSubTasks()->toArray())
        ]);
    }

    #[Route('/{id}', name: 'app_subtask_show', methods: ['GET'])]
    public function show(SubTask $subTask): JsonResponse
    {
        return $this->json([
            'subTask' => [
                'id' => $subTask->getId(),
                'title' => $subTask->getTitle(),
                'description' => $subTask->getDescription(),
                'resources' => $subTask->getResources(),
                'isCompleted' => $subTask->isIsCompleted(),
                'createdAt' => $subTask->getCreatedAt()->format('Y-m-d H:i:s'),
                'updatedAt' => $subTask->getUpdatedAt()->format('Y-m-d H:i:s'),
            ]
        ]);
    }

    #[Route('/task/{taskId}', name: 'app_subtask_create', methods: ['POST'])]
    public function create(Request $request, int $taskId): JsonResponse
    {
        $task = $this->entityManager->getRepository(Task::class)->find($taskId);
        if (!$task) {
            return $this->json(['error' => 'Task not found'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);
        
        $subTask = new SubTask();
        $subTask->setTitle($data['title']);
        $subTask->setDescription($data['description'] ?? '');
        $subTask->setResources($data['resources'] ?? null);
        $subTask->setIsCompleted(false);
        $subTask->setTask($task);
        
        $this->entityManager->persist($subTask);
        $this->entityManager->flush();
        
        // Mise à jour de la progression de la tâche parente
        $this->updateTaskProgress($task);
        
        return $this->json([
            'subTask' => [
                'id' => $subTask->getId(),
                'title' => $subTask->getTitle(),
                'description' => $subTask->getDescription(),
                'resources' => $subTask->getResources(),
                'isCompleted' => $subTask->isIsCompleted(),
                'createdAt' => $subTask->getCreatedAt()->format('Y-m-d H:i:s'),
                'updatedAt' => $subTask->getUpdatedAt()->format('Y-m-d H:i:s'),
            ]
        ], Response::HTTP_CREATED);
    }

    #[Route('/{id}', name: 'app_subtask_update', methods: ['PUT'])]
    public function update(Request $request, SubTask $subTask): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        
        if (isset($data['title'])) {
            $subTask->setTitle($data['title']);
        }
        
        if (isset($data['description'])) {
            $subTask->setDescription($data['description']);
        }
        
        if (isset($data['resources'])) {
            $subTask->setResources($data['resources']);
        }
        
        if (isset($data['isCompleted'])) {
            $subTask->setIsCompleted($data['isCompleted']);
        }
        
        $subTask->setUpdatedAt(new \DateTime());
        $this->entityManager->flush();
        
        // Mise à jour de la progression de la tâche parente
        $this->updateTaskProgress($subTask->getTask());
        
        return $this->json([
            'subTask' => [
                'id' => $subTask->getId(),
                'title' => $subTask->getTitle(),
                'description' => $subTask->getDescription(),
                'resources' => $subTask->getResources(),
                'isCompleted' => $subTask->isIsCompleted(),
                'createdAt' => $subTask->getCreatedAt()->format('Y-m-d H:i:s'),
                'updatedAt' => $subTask->getUpdatedAt()->format('Y-m-d H:i:s'),
            ]
        ]);
    }

    #[Route('/{id}', name: 'app_subtask_delete', methods: ['DELETE'])]
    public function delete(SubTask $subTask): JsonResponse
    {
        $task = $subTask->getTask();
        $this->entityManager->remove($subTask);
        $this->entityManager->flush();
        
        // Mise à jour de la progression de la tâche parente
        $this->updateTaskProgress($task);
        
        return $this->json(null, Response::HTTP_NO_CONTENT);
    }

    private function updateTaskProgress(Task $task): void
    {
        $completedSubTasks = count(array_filter(
            $task->getSubTasks()->toArray(),
            fn($subTask) => $subTask->isIsCompleted()
        ));
        
        $totalSubTasks = count($task->getSubTasks());
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
    }
}
