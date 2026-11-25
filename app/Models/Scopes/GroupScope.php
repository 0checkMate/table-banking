<?php

declare(strict_types=1);

namespace App\Models\Scopes;

use App\Support\CurrentGroup;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

final class GroupScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $groupId = app(CurrentGroup::class)->id();

        if ($groupId === null) {
            $builder->whereRaw('0 = 1'); // fail closed

            return;
        }

        $builder->where($model->qualifyColumn('group_id'), $groupId);
    }
}
