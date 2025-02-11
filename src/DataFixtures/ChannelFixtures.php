<?php

namespace App\DataFixtures;

use App\Entity\Channel;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class ChannelFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $channels = [
            [
                'name' => 'Marketing Digital',
                'description' => 'Stratégies marketing pour les plateformes numériques',
                'isActive' => true,
                'progress' => 75.0
            ],
            [
                'name' => 'Réseaux Sociaux',
                'description' => 'Gestion et optimisation des réseaux sociaux',
                'isActive' => true,
                'progress' => 60.0
            ],
            [
                'name' => 'Email Marketing',
                'description' => 'Campagnes email et newsletters',
                'isActive' => true,
                'progress' => 45.0
            ]
        ];

        foreach ($channels as $channelData) {
            $channel = new Channel();
            $channel->setName($channelData['name']);
            $channel->setDescription($channelData['description']);
            $channel->setIsActive($channelData['isActive']);
            $channel->setProgress($channelData['progress']);
            
            $manager->persist($channel);
        }

        $manager->flush();
    }
}
