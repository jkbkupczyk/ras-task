<?php

namespace App\Connectors;

interface PhoenixClient
{
    public function getPhotos(string $userAccessToken): array;
}
