<?php

namespace App\Controller\Admin;

use App\Entity\Channel;
use App\Form\Admin\ChannelType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/channels')]
#[IsGranted('ROLE_SUPER_ADMIN')]
class ChannelController extends AbstractController
{
    #[Route('/', name: 'app_admin_channel_index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $channels = $entityManager->getRepository(Channel::class)->findAll();

        return $this->render('admin/channel/index.html.twig', [
            'channels' => $channels,
        ]);
    }

    #[Route('/new', name: 'app_admin_channel_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $channel = new Channel();
        $form = $this->createForm(ChannelType::class, $channel);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($channel);
            $entityManager->flush();

            $this->addFlash('success', 'Le canal a été créé avec succès.');
            return $this->redirectToRoute('app_admin_channel_index');
        }

        return $this->render('admin/channel/new.html.twig', [
            'channel' => $channel,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_admin_channel_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Channel $channel, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ChannelType::class, $channel);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Le canal a été modifié avec succès.');
            return $this->redirectToRoute('app_admin_channel_index');
        }

        return $this->render('admin/channel/edit.html.twig', [
            'channel' => $channel,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_admin_channel_delete', methods: ['POST'])]
    public function delete(Request $request, Channel $channel, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$channel->getId(), $request->request->get('_token'))) {
            $entityManager->remove($channel);
            $entityManager->flush();
            $this->addFlash('success', 'Le canal a été supprimé avec succès.');
        }

        return $this->redirectToRoute('app_admin_channel_index');
    }
}
