<?php

namespace App\Repository;

use App\Entity\PhoenixToken;
use DateTimeImmutable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\Persistence\ManagerRegistry;

class PhoenixTokenRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PhoenixToken::class);
    }

    public function upsertTokenForUser(int $userId, string $token, DateTimeImmutable $modifyDate): void
    {
        $upsertSql = "
        INSERT INTO phoenix_token (user_id, token, modify_date)
        VALUES (:userId, :token, :modifyDate)
        ON CONFLICT (user_id) DO UPDATE
                                 SET token       = :token,
                                     modify_date = :modifyDate
        ";
        $qry = $this->_em->createNativeQuery($upsertSql, new ResultSetMapping());
        $qry->setParameter(":userId", $userId);
        $qry->setParameter(":token", $token);
        $qry->setParameter(":modifyDate", $modifyDate);
        $qry->execute();
    }

}
