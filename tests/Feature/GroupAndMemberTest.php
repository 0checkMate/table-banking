<?php

use App\Enums\InterestMethod;
use App\Models\Group;
use App\Models\Member;
use Illuminate\Database\QueryException;
use Illuminate\Database\UniqueConstraintViolationException;

it('defaults a new group to flat interest at 10% a month', function () {
    $group = Group::create(['name' => 'Umoja Chama'])->refresh();

    expect($group->interest_method)->toBe(InterestMethod::Flat)
        ->and($group->interest_rate_bps)->toBe(1000)
        ->and($group->currency)->toBe('KES');
});

it('allows a group to use reducing balance interest', function () {
    $group = Group::factory()->create(['interest_method' => InterestMethod::Reducing]);

    expect($group->fresh()->interest_method)->toBe(InterestMethod::Reducing);
});

it('rejects a duplicate phone number within the same group', function () {
    $group = Group::factory()->create();
    Member::factory()->for($group)->create(['phone' => '+254712345678']);

    Member::factory()->for($group)->create(['phone' => '+254712345678']);
})->throws(UniqueConstraintViolationException::class);

it('allows the same phone number in different groups', function () {
    Member::factory()->create(['phone' => '+254712345678']);
    Member::factory()->create(['phone' => '+254712345678']);

    expect(Member::count())->toBe(2);
});

it('prevents deleting a group that still has members', function () {
    $member = Member::factory()->create();

    $member->group->delete();
})->throws(QueryException::class);
