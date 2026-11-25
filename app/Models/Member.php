<?php

namespace App\Models;

use App\Enums\MemberStatus;
use App\Models\Concerns\BelongsToGroup;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Member extends Model
{
    use BelongsToGroup;
    use HasFactory;

    protected $fillable = [
        'group_id',
        'user_id',
        'name',
        'phone',
        'status',
        'joined_on',
    ];

    protected function casts(): array
    {
        return [
            'status' => MemberStatus::class,
            'joined_on' => 'date',
        ];
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
