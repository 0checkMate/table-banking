<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Enums\MemberStatus;
use App\Models\Group;
use App\Models\Member;
use App\Models\Scopes\GroupScope;
use App\Support\CurrentGroup;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class SetCurrentGroup
{
    public function __construct(private readonly CurrentGroup $currentGroup) {}

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user !== null) {
            $groupIds = Member::withoutGlobalScope(GroupScope::class)
                ->where('user_id', $user->getKey())
                ->where('status', MemberStatus::Active->value)
                ->orderBy('group_id')
                ->pluck('group_id')
                ->map(fn ($id): int => (int) $id);

            $selected = (int) $request->session()->get('current_group_id', 0);
            $groupId = $groupIds->contains($selected) ? $selected : $groupIds->first();

            if ($groupId !== null) {
                $this->currentGroup->set(Group::findOrFail($groupId));
                setPermissionsTeamId($groupId);
                $user->unsetRelation('roles')->unsetRelation('permissions');
            }
        }

        return $next($request);
    }
}
