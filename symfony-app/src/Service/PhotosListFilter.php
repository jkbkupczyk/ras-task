<?php

namespace App\Service;

use DateTimeImmutable;

class PhotosListFilter
{
    public function __construct(
        public readonly ?string            $location = null,
        public readonly ?string            $camera = null,
        public readonly ?string            $description = null,
        public readonly ?DateTimeImmutable $takenAt = null,
        public readonly ?string            $username = null,
    )
    {
    }
}
