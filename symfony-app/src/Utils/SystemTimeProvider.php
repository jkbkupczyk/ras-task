<?php

namespace App\Utils;

use DateTimeImmutable;

final class SystemTimeProvider implements CurrentTimeProvider
{
    public function now(): DateTimeImmutable
    {
        return new DateTimeImmutable();
    }
}
