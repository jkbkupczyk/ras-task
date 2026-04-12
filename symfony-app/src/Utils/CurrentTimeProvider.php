<?php

namespace App\Utils;

use DateTimeImmutable;

interface CurrentTimeProvider
{
    public function now(): DateTimeImmutable;
}
