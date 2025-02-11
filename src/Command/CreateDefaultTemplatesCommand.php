<?php

namespace App\Command;

use App\Entity\Channel;
use App\Entity\Task;
use App\Entity\Subtask;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class CreateDefaultTemplatesCommand extends Command
{
    protected static $defaultName = 'app:create-default-templates';
    protected static $defaultDescription = 'Crée les modèles par défaut pour les canaux, tâches et sous-tâches';

    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        // Créer le canal par défaut "Projet personnel"
        $personalChannel = new Channel();
        $personalChannel->setName('Projet personnel');
        $personalChannel->setDescription('Gérez vos tâches personnelles');
        $personalChannel->setIsActive(true);
        $personalChannel->setIsDefault(true);
        $personalChannel->setProgress(0);

        // Créer une tâche par défaut "Objectifs"
        $goalsTask = new Task();
        $goalsTask->setTitle('Objectifs');
        $goalsTask->setDescription('Définissez vos objectifs personnels');
        $goalsTask->setStatus(Task::STATUS_TODO);
        $goalsTask->setProgress(0);
        $goalsTask->setIsDefault(true);
        $goalsTask->setChannel($personalChannel);

        // Créer une sous-tâche par défaut
        $subtask = new Subtask();
        $subtask->setTitle('Définir un objectif');
        $subtask->setDescription('Définissez un objectif SMART (Spécifique, Mesurable, Atteignable, Réaliste, Temporel)');
        $subtask->setStatus(Task::STATUS_TODO);
        $subtask->setProgress(0);
        $subtask->setIsDefault(true);
        $subtask->setTask($goalsTask);

        // Créer le canal par défaut "Travail"
        $workChannel = new Channel();
        $workChannel->setName('Travail');
        $workChannel->setDescription('Gérez vos tâches professionnelles');
        $workChannel->setIsActive(true);
        $workChannel->setIsDefault(true);
        $workChannel->setProgress(0);

        // Créer une tâche par défaut "Projets"
        $projectsTask = new Task();
        $projectsTask->setTitle('Projets');
        $projectsTask->setDescription('Gérez vos projets professionnels');
        $projectsTask->setStatus(Task::STATUS_TODO);
        $projectsTask->setProgress(0);
        $projectsTask->setIsDefault(true);
        $projectsTask->setChannel($workChannel);

        $this->entityManager->persist($personalChannel);
        $this->entityManager->persist($workChannel);
        $this->entityManager->persist($goalsTask);
        $this->entityManager->persist($projectsTask);
        $this->entityManager->persist($subtask);
        
        $this->entityManager->flush();

        $io->success('Les modèles par défaut ont été créés avec succès.');

        return Command::SUCCESS;
    }
}
