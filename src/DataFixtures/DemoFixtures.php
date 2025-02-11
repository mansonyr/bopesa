<?php

namespace App\DataFixtures;

use App\Entity\Channel;
use App\Entity\Task;
use App\Entity\Subtask;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class DemoFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // Canal SEO
        $seoChannel = new Channel();
        $seoChannel->setName('SEO');
        $seoChannel->setDescription('Optimisation pour les moteurs de recherche');
        $seoChannel->setIsActive(true);
        $manager->persist($seoChannel);

        // Tâches SEO
        $seoTask1 = new Task();
        $seoTask1->setTitle('Configurer Google Search Console et Google Analytics');
        $seoTask1->setDescription('Mettre en place les outils de base pour le suivi SEO');
        $seoTask1->setStatus('in_progress');
        $seoTask1->setProgress(33);
        $seoTask1->setChannel($seoChannel);
        $manager->persist($seoTask1);

        // Sous-tâches SEO
        $this->createSubTask(
            $manager,
            $seoTask1,
            'Créer un compte Google',
            'Si ce n\'est pas déjà fait, créez un compte Google pour votre entreprise.',
            'done'
        );

        $this->createSubTask(
            $manager,
            $seoTask1,
            'Accéder à la Search Console',
            'Connectez-vous à la Search Console et connecter votre site.',
            'in_progress',
            'https://search.google.com/search-console'
        );

        $this->createSubTask(
            $manager,
            $seoTask1,
            'Configurer les rapports',
            'Configurez des rapports personnalisés dans Google Analytics.',
            'todo',
            'https://analytics.google.com/'
        );

        // Canal Email Marketing
        $emailChannel = new Channel();
        $emailChannel->setName('Email Marketing');
        $emailChannel->setDescription('Campagnes d\'emails et newsletters');
        $emailChannel->setIsActive(true);
        $manager->persist($emailChannel);

        // Tâches Email Marketing
        $emailTask1 = new Task();
        $emailTask1->setTitle('Configurer l\'outil d\'emailing');
        $emailTask1->setDescription('Choisir et configurer une plateforme d\'email marketing');
        $emailTask1->setStatus('todo');
        $emailTask1->setProgress(0);
        $emailTask1->setChannel($emailChannel);
        $manager->persist($emailTask1);

        // Sous-tâches Email Marketing
        $this->createSubTask(
            $manager,
            $emailTask1,
            'Comparer les solutions',
            'Comparer les différentes solutions : Mailchimp, Sendinblue, etc.',
            'todo'
        );

        $this->createSubTask(
            $manager,
            $emailTask1,
            'Créer un compte',
            'Créer un compte sur la plateforme choisie',
            'todo'
        );

        // Canal Social Media
        $socialChannel = new Channel();
        $socialChannel->setName('Social Media');
        $socialChannel->setDescription('Gestion des réseaux sociaux');
        $socialChannel->setIsActive(true);
        $manager->persist($socialChannel);

        // Tâches Social Media
        $socialTask1 = new Task();
        $socialTask1->setTitle('Optimiser les profils sociaux');
        $socialTask1->setDescription('Mettre à jour et optimiser les profils sur les réseaux sociaux');
        $socialTask1->setStatus('done');
        $socialTask1->setProgress(100);
        $socialTask1->setChannel($socialChannel);
        $manager->persist($socialTask1);

        // Sous-tâches Social Media
        $this->createSubTask(
            $manager,
            $socialTask1,
            'Optimiser LinkedIn',
            'Mettre à jour le profil LinkedIn de l\'entreprise',
            'done',
            'https://linkedin.com'
        );

        $this->createSubTask(
            $manager,
            $socialTask1,
            'Optimiser Twitter',
            'Mettre à jour le profil Twitter de l\'entreprise',
            'done',
            'https://twitter.com'
        );

        $manager->flush();
    }

    private function createSubTask(
        ObjectManager $manager,
        Task $task,
        string $title,
        string $description,
        string $status,
        ?string $resources = null
    ): void {
        $subtask = new Subtask();
        $subtask->setTitle($title);
        $subtask->setDescription($description);
        $subtask->setStatus($status);
        if ($resources) {
            $subtask->setResources($resources);
        }
        $subtask->setTask($task);
        $manager->persist($subtask);
    }
}
