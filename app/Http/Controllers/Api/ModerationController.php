<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Blacklist;
use App\Models\Group;
use App\Models\User;
use App\Models\Warning;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Moderation and Inactivity Management Module (SDD 5.2).
 *
 * Implements the "Watchdog Daemon" behaviour: issues progressive warnings
 * to members inactive beyond a group's inactivity_warning_period, and
 * automatically blacklists members who accumulate two unresolved warnings.
 */
class ModerationController extends Controller
{
    public function __construct(private NotificationService $notifications)
    {
    }

    /**
     * Scans a group's membership for inactive members and issues warnings,
     * auto-blacklisting anyone who reaches two unresolved warnings. Intended
     * to be invoked by a scheduled command (see console kernel) as well as
     * on demand by an administrator.
     */
    public function scanInactivity(Group $group)
    {
        $cutoff = now()->subDays($group->inactivity_warning_period);

        $inactiveMembers = $group->members()
            ->where(function ($q) use ($cutoff) {
                $q->where('last_active_at', '<', $cutoff)->orWhereNull('last_active_at');
            })
            ->get();

        $blacklistedCount = 0;
        $warnedCount = 0;

        foreach ($inactiveMembers as $member) {
            if ($member->isBlacklistedIn($group->group_id)) {
                continue;
            }

            $unresolvedCount = Warning::where('user_id', $member->user_id)
                ->where('group_id', $group->group_id)
                ->where('resolved', false)
                ->count();

            if ($unresolvedCount >= 2) {
                // Two ignored warnings -> automatic blacklist.
                DB::transaction(function () use ($member, $group) {
                    Blacklist::create([
                        'user_id' => $member->user_id,
                        'group_id' => $group->group_id,
                        'reason' => Blacklist::REASON_INACTIVITY,
                        'start_date' => now(),
                        'duration_days' => $group->blacklist_duration_days,
                        'end_date' => now()->addDays($group->blacklist_duration_days),
                    ]);
                });

                $this->notifications->send(
                    $member,
                    'Blacklist',
                    "You have been suspended from '{$group->name}' for {$group->blacklist_duration_days} days due to prolonged inactivity.",
                    'Group',
                    $group->group_id
                );

                $blacklistedCount++;

                continue;
            }

            $warning = Warning::create([
                'user_id' => $member->user_id,
                'group_id' => $group->group_id,
                'sequence_number' => $unresolvedCount + 1,
                'issue_date' => now(),
                'resolved' => false,
            ]);

            $this->notifications->send(
                $member,
                'Warning',
                "You have been inactive in '{$group->name}' for {$group->inactivity_warning_period}+ days. This is warning #{$warning->sequence_number}.",
                'Warning',
                $warning->warning_id
            );

            $warnedCount++;
        }

        return response()->json([
            'message' => "Inactivity scan complete for '{$group->name}'.",
            'warned' => $warnedCount,
            'blacklisted' => $blacklistedCount,
        ]);
    }

 
    public function warningsIndex()
    {
        $warnings = Warning::with(['user', 'group'])
            ->latest('issue_date')
            ->get();

        return response()->json($warnings);
    }

    /**
     * Lists currently-active blacklists (across all groups) so an admin or
     * lecturer can find one to lift early — in particular reason='inactivity'
     * blacklists, which lock the member's whole account and previously had
     * no way to be located from the UI before end_date passed on its own.
     */
    public function blacklistsIndex()
    {
        $blacklists = Blacklist::with(['user', 'group'])
            ->where('end_date', '>', now())
            ->latest('start_date')
            ->get();

        return response()->json($blacklists);
    }

    /** Resolve a warning once the member becomes active again. */
    public function resolveWarning(Warning $warning)
    {
        $warning->update(['resolved' => true]);

        return response()->json($warning);
    }

    public function blacklistUser(Request $request, Group $group, User $user)
    {
        $request->validate([
            'duration_days' => 'nullable|integer|min:1',
            'reason' => 'nullable|in:manual,inactivity',
        ]);

        $days = $request->input('duration_days', $group->blacklist_duration_days);

        $blacklist = Blacklist::create([
            'user_id' => $user->user_id,
            'group_id' => $group->group_id,
            'reason' => $request->input('reason', Blacklist::REASON_MANUAL),
            'start_date' => now(),
            'duration_days' => $days,
            'end_date' => now()->addDays($days),
        ]);

        $this->notifications->send($user, 'Blacklist', "You have been suspended from '{$group->name}' for {$days} days.", 'Group', $group->group_id);

        return response()->json($blacklist, 201);
    }

    public function liftBlacklist(Blacklist $blacklist)
    {
        $blacklist->update(['end_date' => now()]);

        return response()->json(['message' => 'Blacklist lifted.']);
    }
}
