<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\LedgerDirection;
use App\Enums\LedgerEntryType;
use App\Models\Concerns\BelongsToGroup;
use Illuminate\Database\Eloquent\Model;
use LogicException;

class LedgerEntry extends Model
{
    use BelongsToGroup;

    public const UPDATED_AT = null;

    protected $fillable = [
        'member_id',
        'type',
        'direction',
        'amount',
        'reference',
        'occurred_on',
        'recorded_by',
        'reverses_entry_id',
    ];

    protected function casts(): array
    {
        return [
            'type' => LedgerEntryType::class,
            'direction' => LedgerDirection::class,
            'amount' => 'integer',
            'occurred_on' => 'date',
        ];
    }

    protected static function booted(): void
    {
        static::updating(fn () => throw new LogicException('Ledger entries are append-only; record a reversal instead.'));
        static::deleting(fn () => throw new LogicException('Ledger entries are append-only; record a reversal instead.'));
    }
}
