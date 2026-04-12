<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use App\Repository\LikeRepository;
use App\Repository\PhotoRepository;
use App\Service\UserService;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    public function __construct(
        private readonly UserService     $userService,
        private readonly ManagerRegistry $managerRegistry
    )
    {
    }

    /**
     * @Route("/", name="home")
     * @return Response
     */
    public function index(Request $request): Response
    {
        $photoRepository = new PhotoRepository($this->managerRegistry);
        $likeRepository = new LikeRepository($this->managerRegistry);

        $photos = $photoRepository->findAllWithUsers();

        $session = $request->getSession();
        $userId = $session->get('user_id');
        $currentUser = null;
        $userLikes = [];

        if (!$userId) {
            return $this->renderHome($photos, $currentUser, $userLikes);
        }

        $currentUser = $this->userService->findById($userId);
        if (!$currentUser) {
            return $this->renderHome($photos, $currentUser, $userLikes);
        }

        // problem n+1!!!!
        foreach ($photos as $photo) {
            $likeRepository->setUser($currentUser);
            $userLikes[$photo->getId()] = $likeRepository->hasUserLikedPhoto($photo);
        }

        return $this->renderHome($photos, $currentUser, $userLikes);
    }

    public function renderHome(array $photos, ?User $currentUser, array $userLikes): Response
    {
        return $this->render('home/index.html.twig', [
            'photos' => $photos,
            'currentUser' => $currentUser,
            'userLikes' => $userLikes,
        ]);
    }
}
