<?php

declare(strict_types=1);

use App\Enums\GroupRole;
use App\Models\Group;
use App\Models\Member;
use App\Models\User;
use App\Support\CurrentGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\PermissionRegistrar;

beforeEach(function () {
    app(CurrentGroup::class)->forget();
    app(PermissionRegistrar::class)->forgetCachedPermissions();

    Route::middleware('web')->get('/_test/context', fn (Request $request) => response()->json([
        'group' => app(CurrentGroup::class)->id(),
        'treasurer' => $request->user()->hasRole('treasurer'),
    ]));
});

it('resolves the group from the active membership', function () {
    $group = Group::factory()->create();
    $user = User::factory()->create();
    Member::factory()->for($group)->create(['user_id' => $user->id]);

    $this->actingAs($user)->getJson('/_test/context')
        ->assertOk()
        ->assertJson(['group' => $group->id]);
});

it('stays fail-closed for a user with no membership', function () {
    $this->actingAs(User::factory()->create())->getJson('/_test/context')
        ->assertOk()
        ->assertJson(['group' => null]);
});

it('ignores a session group the user does not belong to', function () {
    $own = Group::factory()->create();
    $foreign = Group::factory()->create();
    $user = User::factory()->create();
    Member::factory()->for($own)->create(['user_id' => $user->id]);

    $this->actingAs($user)
        ->withSession(['current_group_id' => $foreign->id])
        ->getJson('/_test/context')
        ->assertJson(['group' => $own->id]);
});

it('applies roles of the selected group only', function () {
    $a = Group::factory()->create();
    $b = Group::factory()->create();
    $user = User::factory()->create();
    Member::factory()->for($a)->create(['user_id' => $user->id]);
    Member::factory()->for($b)->create(['user_id' => $user->id]);

    setPermissionsTeamId($a->id);
    $user->assignRole(GroupRole::Treasurer->value);

    $this->actingAs($user)->withSession(['current_group_id' => $a->id])
        ->getJson('/_test/context')->assertJson(['treasurer' => true]);

    $this->actingAs($user)->withSession(['current_group_id' => $b->id])
        ->getJson('/_test/context')->assertJson(['treasurer' => false]);
});
