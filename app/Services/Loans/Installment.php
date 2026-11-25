<?php

declare(strict_types=1);

namespace App\Services\Loans;

use Carbon\CarbonImmutable;

final readonly class Installment
{
    public function __construct(
        public int $number,
        public CarbonImmutable $dueDate,
        public int $principal,
        public int $interest,
    ) {}

    public function total(): int
    {
        return $this->principal + $this->interest;
    }
}
