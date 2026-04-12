<?php

namespace App\Service;

use App\Entity\Photo;
use App\Entity\User;
use App\Repository\LikeRepositoryInterface;
use App\Repository\PhotoRepository;

class PhotoService
{
    public function __construct(
        private readonly PhotoRepository         $photoRepository,
        private readonly LikeRepositoryInterface $likeRepository,
    )
    {
    }

    public function getPhotosWithLikes(?User $user): array
    {
        // TODO: add pagination or some type of limit
        $photos = $this->photoRepository->findAllWithUsers();

        if (!$user) {
            return array_map($this->mapToPhotoWithLikes(...), $photos);
        }

        $likedPhotoIds = $this->likeRepository->findLikedPhotoIdsByUser($user);

        return array_map(
            function (Photo $photo) use ($likedPhotoIds) {
                $liked = in_array($photo->getId(), $likedPhotoIds, strict: true);
                return $this->mapToPhotoWithLikes($photo, $liked);
            },
            $photos
        );
    }

    private function mapToPhotoWithLikes(Photo $photo, bool $likedByCurrentUser = false): PhotoWithLike
    {
        return new PhotoWithLike($photo, $likedByCurrentUser);
    }

}
