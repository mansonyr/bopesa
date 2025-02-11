<?php

namespace App\EventSubscriber;

use App\Entity\Channel;
use App\Entity\User;
use App\Service\DefaultChannelService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use EasyCorp\Bundle\EasyAdminBundle\Event\AfterEntityPersistedEvent;

class UserRegistrationSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private DefaultChannelService $defaultChannelService
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            AfterEntityPersistedEvent::class => ['onAfterEntityPersisted'],
        ];
    }

    public function onAfterEntityPersisted(AfterEntityPersistedEvent $event): void
    {
        $entity = $event->getEntityInstance();

        if (!$entity instanceof User) {
            return;
        }

        // Cloner les modèles par défaut pour le nouvel utilisateur
        $this->defaultChannelService->copyDefaultChannelsForUser($entity);
    }
}
