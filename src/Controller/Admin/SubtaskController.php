<?php

namespace App\Controller\Admin;

use App\Entity\Task;
use App\Entity\Subtask;
use App\Form\Admin\SubtaskType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/channels/{channelId}/tasks/{taskId}/subtasks')]
class SubtaskController extends AbstractController
{
    #[Route('/new', name: 'app_admin_subtask_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $entityManager,
        int $channelId,
        int $taskId
    ): Response {
        $task = $entityManager->getRepository(Task::class)->find($taskId);
        
        if (!$task) {
            throw $this->createNotFoundException('Tâche non trouvée');
        }

        if ($task->getChannel()->getId() != $channelId) {
            throw $this->createAccessDeniedException('Cette tâche n\'appartient pas à ce canal');
        }

        $subtask = new Subtask();
        $subtask->setTask($task);
        $subtask->setCreatedAt(new \DateTime());
        $subtask->setUpdatedAt(new \DateTime());
        $subtask->setIsDefault(false);
        
        $form = $this->createForm(SubtaskType::class, $subtask);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($subtask);
            $entityManager->flush();

            $this->addFlash('success', 'La sous-tâche a été créée avec succès.');
            return $this->redirectToRoute('app_admin_task_index', ['channelId' => $channelId]);
        }

        return $this->render('admin/subtask/new.html.twig', [
            'channel' => $task->getChannel(),
            'task' => $task,
            'subtask' => $subtask,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_admin_subtask_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        Subtask $subtask,
        EntityManagerInterface $entityManager,
        int $channelId,
        int $taskId
    ): Response {
        if ($subtask->getTask()->getId() != $taskId || $subtask->getTask()->getChannel()->getId() != $channelId) {
            throw $this->createAccessDeniedException('Cette sous-tâche n\'appartient pas à cette tâche ou ce canal');
        }

        $form = $this->createForm(SubtaskType::class, $subtask);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $subtask->setUpdatedAt(new \DateTime());
            $entityManager->flush();

            $this->addFlash('success', 'La sous-tâche a été modifiée avec succès.');
            return $this->redirectToRoute('app_admin_task_index', ['channelId' => $channelId]);
        }

        return $this->render('admin/subtask/edit.html.twig', [
            'channel' => $subtask->getTask()->getChannel(),
            'task' => $subtask->getTask(),
            'subtask' => $subtask,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_admin_subtask_delete', methods: ['POST'])]
    public function delete(
        Request $request,
        Subtask $subtask,
        EntityManagerInterface $entityManager,
        int $channelId,
        int $taskId
    ): Response {
        if ($subtask->getTask()->getId() != $taskId || $subtask->getTask()->getChannel()->getId() != $channelId) {
            throw $this->createAccessDeniedException('Cette sous-tâche n\'appartient pas à cette tâche ou ce canal');
        }

        if ($this->isCsrfTokenValid('delete'.$subtask->getId(), $request->request->get('_token'))) {
            if ($subtask->isDefault()) {
                // Pour les sous-tâches par défaut, on crée une archive au lieu de supprimer
                $archivedItem = new ArchivedItem();
                $archivedItem->setUser($this->getUser());
                $archivedItem->setItemType('subtask');
                $archivedItem->setItemId($subtask->getId());
                $entityManager->persist($archivedItem);
                $entityManager->flush();
                $this->addFlash('success', 'La sous-tâche a été archivée avec succès.');
            } else {
                $entityManager->remove($subtask);
                $entityManager->flush();
                $this->addFlash('success', 'La sous-tâche a été supprimée avec succès.');
            }
        }

        return $this->redirectToRoute('app_admin_task_index', ['channelId' => $channelId]);
    }
}
