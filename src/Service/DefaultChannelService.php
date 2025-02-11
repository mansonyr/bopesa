<?php

namespace App\Service;

use App\Entity\Channel;
use App\Entity\Task;
use App\Entity\Subtask;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class DefaultChannelService
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
    }

    public function copyDefaultChannelsForUser(User $user): void
    {
        // Récupérer uniquement les canaux par défaut qui n'appartiennent à aucun utilisateur
        $defaultChannels = $this->entityManager->getRepository(Channel::class)
            ->findBy(['isDefault' => true, 'user' => null]);

        foreach ($defaultChannels as $defaultChannel) {
            $newChannel = new Channel();
            $newChannel->setName($defaultChannel->getName());
            $newChannel->setDescription($defaultChannel->getDescription());
            $newChannel->setIsActive(true);
            $newChannel->setIsDefault(false);
            $newChannel->setProgress(0);
            $newChannel->setUser($user);
            
            // Copier les tâches
            foreach ($defaultChannel->getTasks() as $defaultTask) {
                if (!$defaultTask->isDefault() || $defaultTask->getUser() !== null) {
                    continue; // Ignorer les tâches non par défaut ou appartenant à un utilisateur
                }

                $newTask = new Task();
                $newTask->setTitle($defaultTask->getTitle());
                $newTask->setDescription($defaultTask->getDescription());
                $newTask->setStatus(Task::STATUS_TODO);
                $newTask->setProgress(0);
                $newTask->setChannel($newChannel);
                $newTask->setUser($user);
                $newTask->setIsDefault(false);
                
                // Copier les sous-tâches
                foreach ($defaultTask->getSubtasks() as $defaultSubtask) {
                    if (!$defaultSubtask->isDefault() || $defaultSubtask->getUser() !== null) {
                        continue; // Ignorer les sous-tâches non par défaut ou appartenant à un utilisateur
                    }

                    $newSubtask = new Subtask();
                    $newSubtask->setTitle($defaultSubtask->getTitle());
                    $newSubtask->setDescription($defaultSubtask->getDescription());
                    $newSubtask->setStatus(Task::STATUS_TODO);
                    $newSubtask->setProgress(0);
                    $newSubtask->setTask($newTask);
                    $newSubtask->setUser($user);
                    $newSubtask->setIsDefault(false);
                    
                    $this->entityManager->persist($newSubtask);
                }
                
                $this->entityManager->persist($newTask);
            }
            
            $this->entityManager->persist($newChannel);
        }
        
        $this->entityManager->flush();
    }
}
