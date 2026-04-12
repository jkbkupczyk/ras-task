<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\PhotoService;
use App\Service\PhotosListFilter;
use App\Service\UserService;
use DateTimeImmutable;
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

        $filter = $this->getPhotosListFilter($request);
        $photos = $this->photoService->getPhotosWithLikes($currentUser, $filter);

        return $this->render('home/index.html.twig', [
            'photos' => $photos,
            'currentUser' => $currentUser,
            'filters' => $filter
        ]);
    }

    private function getPhotosListFilter(Request $request): PhotosListFilter
    {
        $takenAt = null;
        $takenAtValue = $request->query->get('taken_at');

        if (is_string($takenAtValue) && trim($takenAtValue) !== '') {
            $parsedDate = DateTimeImmutable::createFromFormat('!Y-m-d', $takenAtValue);
            $takenAt = $parsedDate ?: null;
        }

        return new PhotosListFilter(
            location: $this->trimToNull($request->query->get('location')),
            camera: $this->trimToNull($request->query->get('camera')),
            description: $this->trimToNull($request->query->get('description')),
            takenAt: $takenAt,
            username: $this->trimToNull($request->query->get('username'))
        );
    }

    private function trimToNull(mixed $value): ?string
    {
        if (!is_string($value)) {
            return null;
        }

        $trimmedValue = trim($value);

        return $trimmedValue === '' ? null : $trimmedValue;
    }
}
