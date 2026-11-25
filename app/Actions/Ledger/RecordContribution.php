<?php

declare(strict_types=1);

namespace App\Actions\Ledger;

use App\Enums\LedgerDirection;
use App\Enums\LedgerEntryType;
use App\Enums\MemberStatus;
use App\Models\LedgerEntry;
use App\Models\Member;
use App\Models\User;
use App\Support\CurrentGroup;
use Carbon\CarbonInterface;
use DomainException;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

final class RecordContribution
{
    public function __construct(private readonly CurrentGroup $currentGroup) {}

    public function handle(
        Member $member,
        int $amount,
        CarbonInterface $occurredOn,
        User $recordedBy,
        ?string $reference = null,
    ): LedgerEntry {
        if ($amount <= 0) {
            throw new InvalidArgumentException('Contribution amount must be positive.');
        }

        if ($member->group_id !== $this->currentGroup->id()) {
            throw new DomainException('Member does not belong to the current group.');
        }

        if ($member->status !== MemberStatus::Active) {
            throw new DomainException('Only active members can contribute.');
        }

        return DB::transaction(function () use ($member, $amount, $occurredOn, $recordedBy, $reference): LedgerEntry {
            if ($reference !== null && LedgerEntry::where('type', LedgerEntryType::Contribution)
                ->where('reference', $reference)->exists()) {
                throw new DomainException('This payment reference has already been recorded.');
            }

            return LedgerEntry::create([
                'member_id' => $member->id,
                'type' => LedgerEntryType::Contribution,
                'direction' => LedgerDirection::In,
                'amount' => $amount,
                'reference' => $reference,
                'occurred_on' => $occurredOn->toDateString(),
                'recorded_by' => $recordedBy->id,
            ]);
        });
    }
}
