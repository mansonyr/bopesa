<?php

namespace App\Controller\Web;

use App\Entity\Task;
use App\Entity\Subtask;
use App\Form\SubtaskType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/web/channels/{channelId}/tasks/{taskId}/subtasks')]
#[IsGranted('ROLE_USER')]
class SubtaskController extends AbstractController
{
    #[Route('/new', name: 'app_web_subtask_new', methods: ['GET', 'POST'])]
    public function new(Request $request, int $channelId, int $taskId, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        if (!$user) {
            throw $this->createAccessDeniedException('Vous devez être connecté pour accéder à cette page');
        }

        $task = $entityManager->getRepository(Task::class)->find($taskId);
        if (!$task || $task->getChannel()->getId() !== $channelId) {
            throw $this->createNotFoundException('Tâche non trouvée dans ce canal');
        }

        // Vérifier que l'utilisateur a accès à cette tâche
        if (!in_array('ROLE_ADMIN', $user->getRoles()) && $task->getUser() !== $user) {
            throw $this->createAccessDeniedException('Vous n\'avez pas accès à cette tâche');
        }

        $subtask = new Subtask();
        $subtask->setTask($task);
        $subtask->setIsDefault(false);
        $subtask->setUser($user);
        
        $form = $this->createForm(SubtaskType::class, $subtask);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($subtask);
            $entityManager->flush();

            $this->addFlash('success', 'La sous-tâche a été créée avec succès.');
            return $this->redirectToRoute('app_web_task_index', ['channelId' => $channelId]);
        }

        return $this->render('web/subtask/new.html.twig', [
            'task' => $task,
            'subtask' => $subtask,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_web_subtask_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, int $channelId, int $taskId, Subtask $subtask, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        if (!$user) {
            throw $this->createAccessDeniedException('Vous devez être connecté pour accéder à cette page');
        }

        if ($subtask->getTask()->getId() !== $taskId || $subtask->getTask()->getChannel()->getId() !== $channelId) {
            throw $this->createNotFoundException('Sous-tâche non trouvée dans cette tâche');
        }

        // Vérifier que l'utilisateur a accès à cette sous-tâche
        if (!in_array('ROLE_ADMIN', $user->getRoles()) && $subtask->getUser() !== $user) {
            throw $this->createAccessDeniedException('Vous n\'avez pas accès à cette sous-tâche');
        }

        // Vérifier si c'est une sous-tâche par défaut
        if ($subtask->isDefault()) {
            $this->addFlash('error', 'Les sous-tâches par défaut ne peuvent pas être modifiées.');
            return $this->redirectToRoute('app_web_task_index', ['channelId' => $channelId]);
        }

        $form = $this->createForm(SubtaskType::class, $subtask);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'La sous-tâche a été modifiée avec succès.');
            return $this->redirectToRoute('app_web_task_index', ['channelId' => $channelId]);
        }

        return $this->render('web/subtask/edit.html.twig', [
            'task' => $subtask->getTask(),
            'subtask' => $subtask,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/archive', name: 'app_web_subtask_archive', methods: ['POST'])]
    public function archive(Request $request, int $channelId, int $taskId, Subtask $subtask, EntityManagerInterface $entityManager): Response
    {
        if ($subtask->getTask()->getId() !== $taskId || $subtask->getTask()->getChannel()->getId() !== $channelId) {
            throw $this->createNotFoundException('Sous-tâche non trouvée dans cette tâche');
        }

        if ($this->isCsrfTokenValid('archive'.$subtask->getId(), $request->request->get('_token'))) {
            $this->addFlash('success', 'La sous-tâche a été supprimée avec succès.');
        }

        return $this->redirectToRoute('app_web_task_index', ['channelId' => $channelId]);
    }

    #[Route('/{id}', name: 'app_web_subtask_delete', methods: ['POST'])]
    public function delete(Request $request, int $channelId, int $taskId, Subtask $subtask, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        if (!$user) {
            throw $this->createAccessDeniedException('Vous devez être connecté pour accéder à cette page');
        }

        if ($subtask->getTask()->getId() !== $taskId || $subtask->getTask()->getChannel()->getId() !== $channelId) {
            throw $this->createNotFoundException('Sous-tâche non trouvée dans cette tâche');
        }

        // Vérifier que l'utilisateur a accès à cette sous-tâche
        if (!in_array('ROLE_ADMIN', $user->getRoles()) && $subtask->getUser() !== $user) {
            throw $this->createAccessDeniedException('Vous n\'avez pas accès à cette sous-tâche');
        }

        if ($this->isCsrfTokenValid('delete'.$subtask->getId(), $request->request->get('_token'))) {
            $entityManager->remove($subtask);
            $entityManager->flush();
            $this->addFlash('success', 'La sous-tâche a été supprimée avec succès.');
        }

        return $this->redirectToRoute('app_web_channel_show', ['id' => $channelId]);
    }
}
