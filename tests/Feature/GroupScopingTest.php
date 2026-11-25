<?php

declare(strict_types=1);

use App\Models\Group;
use App\Models\Member;
use App\Models\Scopes\GroupScope;
use App\Support\CurrentGroup;

beforeEach(fn () => app(CurrentGroup::class)->forget());

it('returns only members of the current group', function () {
    $a = Group::factory()->create();
    $b = Group::factory()->create();
    Member::factory()->count(2)->for($a)->create();
    Member::factory()->count(3)->for($b)->create();

    app(CurrentGroup::class)->set($a);

    expect(Member::count())->toBe(2);
});

it('cannot load another group member by id', function () {
    $a = Group::factory()->create();
    $b = Group::factory()->create();
    $foreign = Member::factory()->for($b)->create();

    app(CurrentGroup::class)->set($a);

    expect(Member::find($foreign->id))->toBeNull();
});

it('returns nothing when no group is set', function () {
    Member::factory()->for(Group::factory()->create())->create();

    expect(Member::count())->toBe(0);
});

it('assigns the current group on create', function () {
    $a = Group::factory()->create();
    app(CurrentGroup::class)->set($a);

    $member = Member::factory()->make(['group_id' => null]);
    $member->save();

    expect($member->group_id)->toBe($a->id);
});

it('refuses to create a member without a current group', function () {
    Member::factory()->make(['group_id' => null])->save();
})->throws(LogicException::class);

it('allows an explicit bypass of the scope', function () {
    Member::factory()->for(Group::factory()->create())->create();

    expect(Member::withoutGlobalScope(GroupScope::class)->count())->toBe(1);
});
