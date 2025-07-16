<?php

namespace App\Event;

use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeEntityPersistedEvent;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeEntityUpdatedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserPasswordHashSubscriber implements EventSubscriberInterface
{
    public function __construct(private UserPasswordHasherInterface $passwordHasher) {}

    public static function getSubscribedEvents(): array
    {
        return [
            BeforeEntityPersistedEvent::class => ['handlePasswordHash'],
            BeforeEntityUpdatedEvent::class => ['handlePasswordHash'],
        ];
    }

    public function handlePasswordHash(BeforeEntityPersistedEvent|BeforeEntityUpdatedEvent $event): void
    {
        $entity = $event->getEntityInstance();

        if (!$entity instanceof User) {
            return;
        }

        $plainPassword = $entity->getPassword();

        if (!empty($plainPassword) && !$this->isPasswordHashed($plainPassword)) {
            $hashedPassword = $this->passwordHasher->hashPassword($entity, $plainPassword);
            $entity->setPassword($hashedPassword);
        }
    }

    private function isPasswordHashed(string $password): bool
    {
        return str_starts_with($password, '$2y$') || str_starts_with($password, '$argon2');
    }
}
