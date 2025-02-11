<?php

namespace App\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/settings')]
#[IsGranted('ROLE_ADMIN')]
class SettingsController extends AbstractController
{
    #[Route('/', name: 'app_admin_settings_index')]
    public function index(): Response
    {
        return $this->render('admin/settings/index.html.twig', [
            'controller_name' => 'SettingsController',
        ]);
    }
}
