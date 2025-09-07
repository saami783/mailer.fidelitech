<?php

namespace App\Service;

use App\Entity\Log;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class LogService
{

    public function __construct(private EntityManagerInterface $entityManager) {

    }

    public function log(string $action, UserInterface $user, string $message): void
    {
        $log = new Log();
        $log->setAction($action);
        $log->setUser(/** @var User $user */ $user);
        $log->setMessage($message);
        $log->setCreatedAt(new \DateTimeImmutable());
        $this->entityManager->persist($log);
        $this->entityManager->flush();
    }

}