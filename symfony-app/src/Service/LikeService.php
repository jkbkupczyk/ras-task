<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Photo;
use App\Entity\User;
use App\Repository\LikeRepositoryInterface;
use Exception;
use Throwable;

class LikeService
{
    public function __construct(
        private readonly LikeRepositoryInterface $likeRepository
    )
    {
    }

    public function like(User $user, Photo $photo): bool
    {
        if ($this->likeRepository->hasUserLikedPhoto($photo, $user)) {
            $this->likeRepository->unlikePhoto($photo, $user);
            return false;
        } else {
            $this->likePhoto($photo, $user);
            return true;
        }
    }

    public function likePhoto(Photo $photo, User $user): void
    {
        try {
            // TODO: make transactional!!!
            $this->likeRepository->createLike($photo, $user);

            // FIXME: prevent lost updates!!!
            $this->likeRepository->updatePhotoCounter($photo, 1);
        } catch (Throwable) {
            // TODO: replace with dedicated Exception
            throw new Exception('Something went wrong while liking the photo');
        }
    }
}
