<?php

declare(strict_types=1);

namespace App\Actions\Groups;

use App\Enums\GroupRole;
use App\Models\Group;
use Spatie\Permission\Models\Role;

final class ProvisionGroupRoles
{
    public function handle(Group $group): void
    {
        foreach (GroupRole::cases() as $role) {
            Role::firstOrCreate([
                'name' => $role->value,
                'guard_name' => 'web',
                'group_id' => $group->id,
            ]);
        }
    }
}