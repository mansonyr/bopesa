<?php

namespace App\EventSubscriber;

use App\Entity\Channel;
use App\Entity\Client;
use App\Service\DefaultChannelService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use EasyCorp\Bundle\EasyAdminBundle\Event\AfterEntityPersistedEvent;

class ClientRegistrationSubscriber implements EventSubscriberInterface
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

        if (!$entity instanceof Client) {
            return;
        }

        // Cloner les modèles par défaut pour le nouvel utilisateur
        $this->defaultChannelService->copyDefaultChannelsForUser($entity);
    }
}
