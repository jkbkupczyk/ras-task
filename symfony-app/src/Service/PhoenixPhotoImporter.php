<?php

namespace App\Service;

use App\Connectors\PhoenixClient;
use App\Connectors\PhoenixPhoto;
use App\Entity\Photo;
use App\Entity\User;
use App\Repository\PhoenixTokenRepository;
use App\Repository\PhotoRepository;
use App\Utils\CurrentTimeProvider;
use Exception;

class PhoenixPhotoImporter
{
    public function __construct(
        private readonly PhoenixClient          $phoenixClient,
        private readonly CurrentTimeProvider    $currentTimeProvider,
        private readonly UserService            $userService,
        private readonly PhoenixTokenRepository $phoenixTokenRepository,
        private readonly PhotoRepository        $photoRepository
    )
    {
    }

    public function importPhotos(int $userId, string $token): int
    {
        $now = $this->currentTimeProvider->now();

        $user = $this->userService->findById($userId);
        if (!$user) {
           throw new Exception("User with id $userId does not exists!");
        }

        $this->phoenixTokenRepository->upsertTokenForUser($userId, $token, $now);
        $phoenixPhotos = $this->phoenixClient->getPhotos($token);

        $photoEntities = array_map(
            function (PhoenixPhoto $phoenixPhoto) use ($user) {
                return $this->mapToPhotoEntity($phoenixPhoto, $user);
            },
            $phoenixPhotos
        );

        $this->photoRepository->saveAll($photoEntities);

        return count($photoEntities);
    }

    private function mapToPhotoEntity(PhoenixPhoto $photo, User $user): Photo
    {
        return (new Photo())
            ->setImageUrl($photo->url)
            ->setDescription("Photo from Phoenix, id: $photo->id")
            ->setTakenAt($this->currentTimeProvider->now())
            ->setUser($user);
    }

}
