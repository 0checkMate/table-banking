<?php

namespace App\Models;

use App\Actions\Groups\ProvisionGroupRoles;
use App\Enums\InterestMethod;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Group extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'currency',
        'interest_method',
        'interest_rate_bps',
        'late_penalty_bps',
        'loan_limit_multiplier',
    ];

    protected static function booted(): void
    {
        static::created(function (Group $group): void {
            app(ProvisionGroupRoles::class)->handle($group);
        });
    }

    protected function casts(): array
    {
        return [
            'interest_method' => InterestMethod::class,
            'interest_rate_bps' => 'integer',
            'late_penalty_bps' => 'integer',
            'loan_limit_multiplier' => 'integer',
        ];
    }

    public function members(): HasMany
    {
        return $this->hasMany(Member::class);
    }
}
