<?php

namespace App\Connectors;

final class PhoenixPhoto
{
    public function __construct(
        public readonly ?int $id,
        public readonly ?string $url,
    )
    {
    }

}
