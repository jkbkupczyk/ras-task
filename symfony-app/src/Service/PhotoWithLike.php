<?php

namespace App\Service;

use App\Entity\Photo;

final class PhotoWithLike
{
    public function __construct(
        public readonly Photo $photo,
        public readonly bool $likedByCurrentUser,
    )
    {
    }
}
