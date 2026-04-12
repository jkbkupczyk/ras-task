<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\PhoenixPhotoImporter;
use App\Service\UserService;
use RuntimeException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class ProfileController extends AbstractController
{
    public function __construct(
        private readonly UserService          $userService,
        private readonly PhoenixPhotoImporter $phoenixPhotoImporter
    )
    {
    }

    #[Route('/profile', name: 'profile')]
    public function profile(Request $request): Response
    {
        $session = $request->getSession();
        $userId = $session->get('user_id');

        if (!$userId) {
            return $this->redirectHome();
        }

        $user = $this->userService->findById($userId);

        if (!$user) {
            $session->clear();
            return $this->redirectHome();
        }

        return $this->render('profile/index.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/profile/phoenix-import', name: 'profile_phoenix_import', methods: ['POST'])]
    public function importPhotos(Request $request): RedirectResponse
    {
        $userId = $request->getSession()->get('user_id');
        $token = $request->request->get('token');

        if (!$token) {
            $this->addFlash('info', 'Phoenix token is required!');
            return $this->redirectProfile();
        }

        try {
            $importedPhotosCount = $this->phoenixPhotoImporter->importPhotos($userId, $token);
            $this->addFlash('info', sprintf('Successfully imported %d photos from Phoenix!', $importedPhotosCount));
        } catch (RuntimeException $e) {
            $this->addFlash('info', 'Phoenix import failed: ' . $e->getMessage());
        }

        return $this->redirectProfile();
    }

    private function redirectHome(): RedirectResponse
    {
        return $this->redirectToRoute('home');
    }

    private function redirectProfile(): RedirectResponse
    {
        return $this->redirectToRoute('profile');
    }

}
