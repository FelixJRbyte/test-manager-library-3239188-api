<?php

declare(strict_types=1);

namespace App\Application\Ports;

use DateTimeImmutable;

/**
 * Puerto de reloj en UTC (plan.md D-05, constitution.md Art. II y IV ISP).
 */
interface Clock
{
    public function now(): DateTimeImmutable;
}
