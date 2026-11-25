<?php

declare(strict_types=1);

use App\Enums\GroupRole;
use App\Models\Group;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

beforeEach(fn () => app(PermissionRegistrar::class)->forgetCachedPermissions());

it('provisions the four standard roles when a group is created', function () {
    $group = Group::factory()->create();

    $names = Role::where('group_id', $group->id)->pluck('name')->sort()->values()->all();

    expect($names)->toBe(['admin', 'member', 'secretary', 'treasurer']);
});

it('keeps role assignments separate between groups', function () {
    $a = Group::factory()->create();
    $b = Group::factory()->create();
    $user = User::factory()->create();

    setPermissionsTeamId($a->id);
    $user->assignRole(GroupRole::Treasurer->value);

    expect($user->hasRole('treasurer'))->toBeTrue();

    setPermissionsTeamId($b->id);
    $user->unsetRelation('roles');

    expect($user->hasRole('treasurer'))->toBeFalse();
});