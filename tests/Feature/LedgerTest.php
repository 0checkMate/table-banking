<?php

declare(strict_types=1);

use App\Actions\Ledger\RecordContribution;
use App\Enums\LedgerDirection;
use App\Enums\LedgerEntryType;
use App\Enums\MemberStatus;
use App\Models\Group;
use App\Models\LedgerEntry;
use App\Models\Member;
use App\Models\User;
use App\Support\CurrentGroup;

beforeEach(function () {
    $this->group = Group::factory()->create();
    app(CurrentGroup::class)->set($this->group);
    $this->member = Member::factory()->for($this->group)->create();
    $this->user = User::factory()->create();
});

function contribute(Member $member, User $user, int $amount = 50000, ?string $reference = null): LedgerEntry
{
    return app(RecordContribution::class)->handle($member, $amount, now(), $user, $reference);
}

it('records a contribution as an inflow entry', function () {
    $entry = contribute($this->member, $this->user, 50000, 'SBK1A2B3C4');

    expect($entry->type)->toBe(LedgerEntryType::Contribution)
        ->and($entry->direction)->toBe(LedgerDirection::In)
        ->and($entry->amount)->toBe(50000)
        ->and($entry->group_id)->toBe($this->group->id)
        ->and($entry->member_id)->toBe($this->member->id)
        ->and($entry->recorded_by)->toBe($this->user->id);
});

it('rejects a zero or negative amount', function (int $amount) {
    contribute($this->member, $this->user, $amount);
})->with([0, -100])->throws(InvalidArgumentException::class);

it('rejects a contribution from an exited member', function () {
    $exited = Member::factory()->for($this->group)->create(['status' => MemberStatus::Exited]);

    contribute($exited, $this->user);
})->throws(DomainException::class);

it('rejects a member outside the current group', function () {
    $foreign = Member::factory()->for(Group::factory()->create())->create();

    contribute($foreign, $this->user);
})->throws(DomainException::class);

it('rejects a duplicate payment reference', function () {
    contribute($this->member, $this->user, 50000, 'SBK1A2B3C4');
    contribute($this->member, $this->user, 50000, 'SBK1A2B3C4');
})->throws(DomainException::class);

it('does not allow a ledger entry to be updated', function () {
    $entry = contribute($this->member, $this->user);

    $entry->update(['amount' => 1]);
})->throws(LogicException::class);

it('does not allow a ledger entry to be deleted', function () {
    $entry = contribute($this->member, $this->user);

    $entry->delete();
})->throws(LogicException::class);
