<?php

declare(strict_types=1);

namespace Tests\Integration;

use App\Utils\CurrentTimeProvider;
use DateTimeImmutable;

final class MockTimeProvider implements CurrentTimeProvider
{
    public function __construct(
        public DateTimeImmutable $time = new DateTimeImmutable()
    )
    {
    }

    public function withTime(DateTimeImmutable $newTime): self
    {
        $this->time = $newTime;
        return $this;
    }

    public function now(): DateTimeImmutable
    {
        return $this->time;
    }
}
