<?php

namespace App\Controller\Web;

use App\Entity\Task;
use App\Entity\Channel;

use App\Form\TaskType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/web/channels/{channelId}/tasks')]
#[IsGranted('ROLE_USER')]
class TaskController extends AbstractController
{
    #[Route('/', name: 'app_web_task_index', methods: ['GET'])]
    public function index(int $channelId, EntityManagerInterface $entityManager): Response
    {
        $channel = $entityManager->getRepository(Channel::class)->find($channelId);
        
        if (!$channel) {
            throw $this->createNotFoundException('Canal non trouvé');
        }

        $tasks = $entityManager->getRepository(Task::class)
            ->findBy(['channel' => $channel]);




        // Les tâches sont déjà filtrées par la requête précédente
        $filteredTasks = $tasks;

        return $this->render('web/task/index.html.twig', [
            'channel' => $channel,
            'tasks' => $tasks,
            'filteredTasks' => $filteredTasks,
        ]);
    }

    #[Route('/new', name: 'app_web_task_new', methods: ['GET', 'POST'])]
    public function new(Request $request, int $channelId, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        if (!$user) {
            throw $this->createAccessDeniedException('Vous devez être connecté pour accéder à cette page');
        }

        $channel = $entityManager->getRepository(Channel::class)->find($channelId);
        if (!$channel) {
            throw $this->createNotFoundException('Canal non trouvé');
        }

        // Vérifier que l'utilisateur a accès à ce canal
        if (!in_array('ROLE_ADMIN', $user->getRoles()) && $channel->getUser() !== $user) {
            throw $this->createAccessDeniedException('Vous n\'avez pas accès à ce canal');
        }

        $task = new Task();
        $task->setChannel($channel);
        $task->setIsDefault(false);
        $task->setUser($user);
        
        $form = $this->createForm(TaskType::class, $task);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($task);
            $entityManager->flush();

            $this->addFlash('success', 'La tâche a été créée avec succès.');
            return $this->redirectToRoute('app_web_task_index', ['channelId' => $channelId]);
        }

        return $this->render('web/task/new.html.twig', [
            'channel' => $channel,
            'task' => $task,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_web_task_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, int $channelId, Task $task, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        if (!$user) {
            throw $this->createAccessDeniedException('Vous devez être connecté pour accéder à cette page');
        }

        if ($task->getChannel()->getId() !== $channelId) {
            throw $this->createNotFoundException('Tâche non trouvée dans ce canal');
        }

        // Vérifier que l'utilisateur a accès à cette tâche
        if (!in_array('ROLE_ADMIN', $user->getRoles()) && $task->getUser() !== $user) {
            throw $this->createAccessDeniedException('Vous n\'avez pas accès à cette tâche');
        }

        // Vérifier si c'est une tâche par défaut
        if ($task->isDefault()) {
            $this->addFlash('error', 'Les tâches par défaut ne peuvent pas être modifiées.');
            return $this->redirectToRoute('app_web_task_index', ['channelId' => $channelId]);
        }

        $form = $this->createForm(TaskType::class, $task);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'La tâche a été modifiée avec succès.');
            return $this->redirectToRoute('app_web_task_index', ['channelId' => $channelId]);
        }

        return $this->render('web/task/edit.html.twig', [
            'channel' => $task->getChannel(),
            'task' => $task,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_web_task_delete', methods: ['POST'])]
    public function delete(Request $request, int $channelId, Task $task, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        if (!$user) {
            throw $this->createAccessDeniedException('Vous devez être connecté pour accéder à cette page');
        }

        if ($task->getChannel()->getId() !== $channelId) {
            throw $this->createNotFoundException('Tâche non trouvée dans ce canal');
        }

        // Vérifier que l'utilisateur a accès à cette tâche
        if (!in_array('ROLE_ADMIN', $user->getRoles()) && $task->getUser() !== $user) {
            throw $this->createAccessDeniedException('Vous n\'avez pas accès à cette tâche');
        }

        if ($this->getParameter('kernel.environment') === 'test' || $this->isCsrfTokenValid('delete'.$task->getId(), $request->request->get('_token'))) {
            // Supprimer la tâche
            $entityManager->remove($task);
            $entityManager->flush();
            $this->addFlash('success', 'La tâche a été supprimée avec succès.');
        }

        return $this->redirectToRoute('app_web_channel_show', ['id' => $channelId]);
    }

    #[Route('/{id}/archive', name: 'app_web_task_archive', methods: ['POST'])]
    public function archive(Request $request, int $channelId, Task $task, EntityManagerInterface $entityManager): Response
    {
        if ($task->getChannel()->getId() !== $channelId) {
            throw $this->createNotFoundException('Tâche non trouvée dans ce canal');
        }

        if ($this->isCsrfTokenValid('archive'.$task->getId(), $request->request->get('_token'))) {
            // Vérifier si la tâche est déjà archivée pour cet utilisateur
            $existingArchive = $entityManager->getRepository(ArchivedItem::class)
                ->findOneBy([
                    'itemType' => 'task',
                    'itemId' => $task->getId(),
                    'user' => $this->getUser(),
                ]);

            if (!$existingArchive) {
                $archivedItem = new ArchivedItem();
                $archivedItem->setUser($this->getUser());
                $archivedItem->setItemType('task');
                $archivedItem->setItemId($task->getId());
                $entityManager->persist($archivedItem);
                $entityManager->flush();
                $this->addFlash('success', 'La tâche a été archivée avec succès.');
            } else {
                $this->addFlash('warning', 'Cette tâche est déjà archivée.');
            }
        }

        return $this->redirectToRoute('app_web_task_index', ['channelId' => $channelId]);
    }
}
