<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\PhotoService;
use App\Service\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    public function __construct(
        private readonly UserService  $userService,
        private readonly PhotoService $photoService,
    )
    {
    }

    #[Route(path: '/', name: 'home')]
    public function index(Request $request): Response
    {
        $session = $request->getSession();
        $userId = $session->get('user_id');
        $currentUser = $userId
            ? $this->userService->findById($userId)
            : null;

        $photos = $this->photoService->getPhotosWithLikes($currentUser);

        return $this->render('home/index.html.twig', [
            'photos' => $photos,
            'currentUser' => $currentUser
        ]);
    }
}
