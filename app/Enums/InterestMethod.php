<?php

declare(strict_types=1);

namespace App\Enums;

enum InterestMethod: string
{
    case Flat = 'flat';
    case Reducing = 'reducing';

    public function label(): string
    {
        return match ($this) {
            self::Flat => 'Flat rate',
            self::Reducing => 'Reducing balance',
        };
    }
}
