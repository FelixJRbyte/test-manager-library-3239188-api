<?php

declare(strict_types=1);

namespace Tests\Fakes;

use App\Application\Ports\Clock;
use DateTimeImmutable;

final class FakeClock implements Clock
{
    private DateTimeImmutable $current;

    public function __construct(?DateTimeImmutable $initial = null)
    {
        $this->current = $initial ?? new DateTimeImmutable('2026-10-14T14:15:00Z');
    }

    public function now(): DateTimeImmutable
    {
        return $this->current;
    }

    public function advanceDays(int $days): void
    {
        $this->current = $this->current->modify("+{$days} days");
    }

    public function setNow(DateTimeImmutable $instant): void
    {
        $this->current = $instant;
    }
}
