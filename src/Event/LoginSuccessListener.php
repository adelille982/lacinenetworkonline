<?php

namespace App\Event;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;

class LoginSuccessListener
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function __invoke(LoginSuccessEvent $event): void
    {
        $user = $event->getUser();

        if (!$user instanceof \App\Entity\User) {
            return;
        }

        $parisTime = new \DateTimeImmutable('now', new \DateTimeZone('Europe/Paris'));
        $user->setConditionValidated($parisTime);

        $this->em->flush();
    }
}
