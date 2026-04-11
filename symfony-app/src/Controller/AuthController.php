<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\AuthService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AuthController extends AbstractController
{
    public function __construct(
        private readonly AuthService $authService
    )
    {
    }

    #[Route('/auth/{username}/{token}', name: 'auth_login')]
    public function login(string $username, string $token, Request $request): Response
    {
        $authResult = $this->authService->login($username, $token);

        if (!$authResult->tokenId) {
            return new Response('Invalid token', 401);
        }
        if (!$authResult->userId) {
            return new Response('User not found', 404);
        }

        $session = $request->getSession();
        $session->set('user_id', $authResult->userId);
        $session->set('username', $authResult->username);

        $this->addFlash('success', 'Welcome back, ' . $authResult->username . '!');

        return $this->redirectToRoute('home');
    }

    #[Route('/logout', name: 'logout')]
    public function logout(Request $request): Response
    {
        $session = $request->getSession();
        $session->clear();

        $this->addFlash('info', 'You have been logged out successfully.');

        return $this->redirectToRoute('home');
    }
}
