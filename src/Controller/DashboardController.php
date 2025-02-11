<?php

namespace App\Controller;

use App\Entity\Channel;
use App\Entity\Task;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractController
{
    #[Route('/dashboard', name: 'app_dashboard')]
    public function index(Request $request, EntityManagerInterface $entityManager): Response
    {
        $channels = $entityManager->getRepository(Channel::class)->findAll();
        
        // Get current channel from query parameter or first channel
        $currentChannelId = $request->query->get('channel');
        $currentChannel = null;
        
        if ($currentChannelId) {
            $currentChannel = $entityManager->getRepository(Channel::class)->find($currentChannelId);
        } elseif (count($channels) > 0) {
            $currentChannel = $channels[0];
        }



        // Get active tab and selected task
        $activeTab = $request->query->get('tab', 'tasks');
        $selectedTask = null;


        
        if ($activeTab === 'subtasks') {
            $taskId = $request->query->get('task');
            if ($taskId) {
                $selectedTask = $entityManager->getRepository(Task::class)->find($taskId);
            } elseif ($currentChannel && count($currentChannel->getTasks()) > 0) {
                $selectedTask = $currentChannel->getTasks()[0];
            }
        }

        // Préparer les tâches pour la vue
        $filteredTasks = [];
        if ($currentChannel) {
            $filteredTasks = $currentChannel->getTasks()->toArray();
        }

        return $this->render('dashboard/index.html.twig', [
            'channels' => $channels,
            'currentChannel' => $currentChannel,
            'activeTab' => $activeTab,
            'selectedTask' => $selectedTask,
            'filteredTasks' => $filteredTasks,
        ]);
    }

    #[Route('/subtask/{id}/toggle', name: 'app_subtask_toggle', methods: ['POST'])]
    public function toggleSubtask(int $id, EntityManagerInterface $entityManager): Response
    {
        $subtask = $entityManager->getRepository(SubTask::class)->find($id);
        
        if (!$subtask) {
            throw $this->createNotFoundException('Sous-tâche non trouvée');
        }

        // Toggle status between 'todo' and 'done'
        $subtask->setStatus($subtask->getStatus() === 'done' ? 'todo' : 'done');
        
        // Update task progress
        $task = $subtask->getTask();
        $totalSubtasks = count($task->getSubTasks());
        $completedSubtasks = count(array_filter($task->getSubTasks()->toArray(), function($st) {
            return $st->getStatus() === 'done';
        }));
        
        $progress = ($totalSubtasks > 0) ? ($completedSubtasks / $totalSubtasks) * 100 : 0;
        $task->setProgress($progress);
        
        // Update channel progress
        $channel = $task->getChannel();
        $totalTasks = count($channel->getTasks());
        $channelProgress = 0;
        
        if ($totalTasks > 0) {
            $totalProgress = array_reduce($channel->getTasks()->toArray(), function($sum, $t) {
                return $sum + $t->getProgress();
            }, 0);
            $channelProgress = $totalProgress / $totalTasks;
        }
        
        $channel->setProgress($channelProgress);

        $entityManager->flush();

        return $this->json([
            'success' => true,
            'taskProgress' => $progress,
            'channelProgress' => $channelProgress,
        ]);
    }
}
