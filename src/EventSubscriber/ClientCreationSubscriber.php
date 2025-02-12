<?php

namespace App\EventSubscriber;

use App\Entity\Client;
use App\Entity\Channel;
use App\Service\DefaultChannelService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Http\SecurityEvents;
use Doctrine\ORM\EntityManagerInterface;

class ClientCreationSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private DefaultChannelService $defaultChannelService,
        private EntityManagerInterface $entityManager
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            SecurityEvents::INTERACTIVE_LOGIN => 'onSecurityInteractiveLogin',
        ];
    }

    public function onSecurityInteractiveLogin(InteractiveLoginEvent $event): void
    {
        $user = $event->getAuthenticationToken()->getUser();
        
        if (!$user instanceof Client) {
            return;
        }

        // Vérifier si l'utilisateur a déjà des canaux
        $hasChannels = $this->entityManager
            ->getRepository(Channel::class)
            ->count(['user' => $user]) > 0;

        if (!$hasChannels) {
            $this->defaultChannelService->copyDefaultChannelsForUser($user);
        }
    }
}
