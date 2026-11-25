<?php

declare(strict_types=1);

namespace App\Models\Concerns;

use App\Models\Scopes\GroupScope;
use App\Support\CurrentGroup;
use Illuminate\Database\Eloquent\Model;
use LogicException;

trait BelongsToGroup
{
    public static function bootBelongsToGroup(): void
    {
        static::addGlobalScope(new GroupScope);

        static::creating(function (Model $model): void {
            if ($model->getAttribute('group_id') !== null) {
                return;
            }

            $groupId = app(CurrentGroup::class)->id();

            if ($groupId === null) {
                throw new LogicException('Cannot create '.$model::class.' without a current group.');
            }

            $model->setAttribute('group_id', $groupId);
        });
    }
}
