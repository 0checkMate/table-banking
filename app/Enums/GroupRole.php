<?php

declare(strict_types=1);

namespace App\Enums;

enum GroupRole: string
{
    case Admin = 'admin';
    case Treasurer = 'treasurer';
    case Secretary = 'secretary';
    case Member = 'member';
}