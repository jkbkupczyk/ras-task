<?php

namespace App\Service;

use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;

class AuthService
{
    public function __construct(
        private readonly EntityManagerInterface $em
    )
    {
    }

    /**
     * @throws Exception
     */
    public function login(string $username, string $token): AuthResult
    {
        $tokenSql = "SELECT t.id as tokenId, t.user_id as userId FROM auth_tokens t WHERE t.token = :token";
        $tokenStmt = $this->em->getConnection()->prepare($tokenSql);
        $tokenStmt->bindValue(":token", $token);
        $tokenResult = $tokenStmt->executeQuery()->fetchAssociative();
        if (!$tokenResult) {
            return new AuthResult(
                userId: null,
                tokenId: null,
                username: $username,
            );
        }

        $tokenId = $tokenResult["tokenid"];

        $userSql = "SELECT u.id as userId FROM users u WHERE u.id = :userId AND u.username = :username";
        $userStmt = $this->em->getConnection()->prepare($userSql);
        $userStmt->bindValue(":userId", $tokenResult['userid']);
        $userStmt->bindValue(":username", $username);
        $userResult = $userStmt->executeQuery()->fetchAssociative();
        if (!$userResult) {
            return new AuthResult(
                userId: null,
                tokenId: $tokenId,
                username: $username,
            );
        }

        return new AuthResult(
            userId: $userResult['userid'],
            tokenId: $tokenId,
            username: $username,
        );
    }
}
