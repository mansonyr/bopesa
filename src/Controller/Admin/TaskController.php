<?php

namespace App\Controller\Admin;

use App\Entity\Task;
use App\Entity\Channel;
use App\Form\Admin\TaskType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/channels/{channelId}/tasks')]
#[IsGranted('ROLE_SUPER_ADMIN')]
class TaskController extends AbstractController
{
    #[Route('/', name: 'app_admin_task_index', methods: ['GET'])]
    public function index(int $channelId, EntityManagerInterface $entityManager): Response
    {
        $channel = $entityManager->getRepository(Channel::class)->find($channelId);
        
        if (!$channel) {
            throw $this->createNotFoundException('Canal non trouvé');
        }

        $qb = $entityManager->getRepository(Task::class)->createQueryBuilder('t')
            ->where('t.channel = :channel')
            ->setParameter('channel', $channel);



        $tasks = $qb->getQuery()->getResult();

        return $this->render('admin/task/index.html.twig', [
            'channel' => $channel,
            'tasks' => $tasks,
        ]);
    }

    #[Route('/new', name: 'app_admin_task_new', methods: ['GET', 'POST'])]
    public function new(Request $request, int $channelId, EntityManagerInterface $entityManager): Response
    {
        $channel = $entityManager->getRepository(Channel::class)->find($channelId);
        
        if (!$channel) {
            throw $this->createNotFoundException('Canal non trouvé');
        }

        $task = new Task();
        $task->setChannel($channel);
        
        $form = $this->createForm(TaskType::class, $task);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($task);
            $entityManager->flush();

            $this->addFlash('success', 'La tâche a été créée avec succès.');
            return $this->redirectToRoute('app_admin_task_index', ['channelId' => $channelId]);
        }

        return $this->render('admin/task/new.html.twig', [
            'channel' => $channel,
            'task' => $task,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_admin_task_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, int $channelId, Task $task, EntityManagerInterface $entityManager): Response
    {
        if ($task->getChannel()->getId() !== $channelId) {
            throw $this->createNotFoundException('Tâche non trouvée dans ce canal');
        }

        $form = $this->createForm(TaskType::class, $task);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'La tâche a été modifiée avec succès.');
            return $this->redirectToRoute('app_admin_task_index', ['channelId' => $channelId]);
        }

        return $this->render('admin/task/edit.html.twig', [
            'channel' => $task->getChannel(),
            'task' => $task,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_admin_task_delete', methods: ['POST'])]
    public function delete(Request $request, int $channelId, Task $task, EntityManagerInterface $entityManager): Response
    {
        if ($task->getChannel()->getId() !== $channelId) {
            throw $this->createNotFoundException('Tâche non trouvée dans ce canal');
        }

        if ($this->isCsrfTokenValid('delete'.$task->getId(), $request->request->get('_token'))) {
            if ($task->isDefault()) {
                // Pour les tâches par défaut, on crée une archive au lieu de supprimer
                $archivedItem = new ArchivedItem();
                $archivedItem->setUser($this->getUser());
                $archivedItem->setItemType('task');
                $archivedItem->setItemId($task->getId());
                $entityManager->persist($archivedItem);
                $entityManager->flush();
                $this->addFlash('success', 'La tâche a été archivée avec succès.');
            } else {
                $entityManager->remove($task);
                $entityManager->flush();
                $this->addFlash('success', 'La tâche a été supprimée avec succès.');
            }
        }

        return $this->redirectToRoute('app_admin_task_index', ['channelId' => $channelId]);
    }
}
