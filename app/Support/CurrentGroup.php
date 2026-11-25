<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\Group;

final class CurrentGroup
{
    private ?int $id = null;

    public function set(Group $group): void
    {
        $this->id = (int) $group->getKey();
    }

    public function id(): ?int
    {
        return $this->id;
    }

    public function forget(): void
    {
        $this->id = null;
    }
}
