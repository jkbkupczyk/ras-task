<?php

namespace App\Service;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class UserService
{
    public function __construct(
        private readonly EntityManagerInterface $em
    )
    {
    }

    public function findById(int $userId): ?User {
        return $this->em->getRepository(User::class)->find($userId);
    }

}
