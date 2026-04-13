<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\LikeService;
use App\Service\PhotoService;
use App\Service\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PhotoController extends AbstractController
{
    public function __construct(
        private readonly UserService  $userService,
        private readonly LikeService  $likeService,
        private readonly PhotoService $photoService,
    )
    {
    }

    #[Route('/photo/{id}/like', name: 'photo_like')]
    public function like($id, Request $request): Response
    {
        $session = $request->getSession();
        $userId = $session->get('user_id');

        if (!$userId) {
            $this->addFlash('error', 'You must be logged in to like photos.');
            return $this->redirectToRoute('home');
        }

        $user = $this->userService->findById($userId);
        if (!$user) {
            $this->addFlash('error', 'User not found!');
            throw $this->createAccessDeniedException();
        }

        $photo = $this->photoService->findById($id);
        if (!$photo) {
            throw $this->createNotFoundException('Photo not found');
        }

        $liked = $this->likeService->like($user, $photo);
        if ($liked) {
            $this->addFlash('success', 'Photo liked!');
        } else {
            $this->addFlash('info', 'Photo unliked!');
        }

        return $this->redirectToRoute('home');
    }
}
