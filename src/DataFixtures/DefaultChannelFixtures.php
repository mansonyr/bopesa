<?php

namespace App\DataFixtures;

use App\Entity\Channel;
use App\Entity\Task;
use App\Entity\Subtask;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class DefaultChannelFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // Créer le canal par défaut
        $channel = new Channel();
        $channel->setName('Mon premier projet');
        $channel->setDescription('Un projet pour bien démarrer');
        $channel->setIsActive(true);
        $channel->setIsDefault(true);
        $channel->setProgress(0);

        // Créer une tâche par défaut
        $task = new Task();
        $task->setTitle('Première tâche');
        $task->setDescription('Une tâche pour commencer');
        $task->setStatus(Task::STATUS_TODO);
        $task->setProgress(0);
        $task->setChannel($channel);
        $task->setIsDefault(true);

        // Créer une sous-tâche par défaut
        $subtask = new Subtask();
        $subtask->setTitle('Première sous-tâche');
        $subtask->setDescription('Une sous-tâche pour commencer');
        $subtask->setStatus(Task::STATUS_TODO);
        $subtask->setProgress(0);
        $subtask->setTask($task);
        $subtask->setIsDefault(true);

        $manager->persist($channel);
        $manager->persist($task);
        $manager->persist($subtask);
        $manager->flush();
    }
}
