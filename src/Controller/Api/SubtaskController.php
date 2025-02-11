<?php

namespace App\Controller\Api;

use App\Entity\Subtask;
use App\Entity\Task;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/subtasks')]
class SubtaskController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager
    ) {}

    #[Route('', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        
        $task = $this->entityManager->getRepository(Task::class)->find($data['taskId']);
        if (!$task) {
            return $this->json(['error' => 'Task not found'], 404);
        }
        
        $subtask = new Subtask();
        $subtask->setTitle($data['title']);
        $subtask->setDescription($data['description']);
        $subtask->setStatus($data['status']);
        $subtask->setTask($task);
        
        $this->entityManager->persist($subtask);
        $this->entityManager->flush();
        
        // Update task progress
        $this->updateTaskProgress($task);
        
        return $this->json(['success' => true, 'subtask' => [
            'id' => $subtask->getId(),
            'title' => $subtask->getTitle(),
            'description' => $subtask->getDescription(),
            'status' => $subtask->getStatus(),
        ]]);
    }
    
    #[Route('/{id}', methods: ['GET'])]
    public function show(Subtask $subtask): JsonResponse
    {
        return $this->json([
            'id' => $subtask->getId(),
            'title' => $subtask->getTitle(),
            'description' => $subtask->getDescription(),
            'status' => $subtask->getStatus(),
        ]);
    }
    
    #[Route('/{id}', methods: ['PUT'])]
    public function update(Subtask $subtask, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        
        $subtask->setTitle($data['title']);
        $subtask->setDescription($data['description']);
        $subtask->setStatus($data['status']);
        
        $this->entityManager->flush();
        
        // Update task progress
        $this->updateTaskProgress($subtask->getTask());
        
        return $this->json(['success' => true, 'subtask' => [
            'id' => $subtask->getId(),
            'title' => $subtask->getTitle(),
            'description' => $subtask->getDescription(),
            'status' => $subtask->getStatus(),
        ]]);
    }
    
    #[Route('/{id}', methods: ['DELETE'])]
    public function delete(Subtask $subtask): JsonResponse
    {
        $task = $subtask->getTask();
        
        $this->entityManager->remove($subtask);
        $this->entityManager->flush();
        
        // Update task progress
        $this->updateTaskProgress($task);
        
        return $this->json(['success' => true]);
    }
    
    private function updateTaskProgress(Task $task): void
    {
        $subtasks = $task->getSubtasks();
        $totalSubtasks = count($subtasks);
        
        if ($totalSubtasks === 0) {
            $task->setProgress(0);
            $task->setStatus(Task::STATUS_TODO);
        } else {
            $completedSubtasks = count(array_filter(
                $subtasks->toArray(),
                fn(Subtask $st) => $st->getStatus() === Subtask::STATUS_DONE
            ));
            
            $inProgressSubtasks = count(array_filter(
                $subtasks->toArray(),
                fn(Subtask $st) => $st->getStatus() === Subtask::STATUS_IN_PROGRESS
            ));
            
            $progress = ($completedSubtasks / $totalSubtasks) * 100;
            $task->setProgress($progress);
            
            if ($completedSubtasks === $totalSubtasks) {
                $task->setStatus(Task::STATUS_DONE);
            } elseif ($completedSubtasks > 0 || $inProgressSubtasks > 0) {
                $task->setStatus(Task::STATUS_IN_PROGRESS);
            } else {
                $task->setStatus(Task::STATUS_TODO);
            }
        }
        
        $this->entityManager->flush();
    }
}
