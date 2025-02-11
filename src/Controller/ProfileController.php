<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\ChannelRepository;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('controller.service_arguments')]
class ProfileController extends AbstractController
{
    #[Route('/profile', name: 'app_profile')]
    public function index(ChannelRepository $channelRepository): Response
    {
        return $this->render('profile/index.html.twig', [
            'user' => $this->getUser(),
            'channels' => $channelRepository->findAll(),
        ]);
    }
}
