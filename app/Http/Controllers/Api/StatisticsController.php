<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\GroupAdmin;
use App\Models\Post;
use App\Models\Reply;
use App\Models\Topic;
use App\Models\User;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Statistics Module (SDD 5.7) - "Check Student Performance and
 * Participation" use case (Table 42).
 *
 * systemStatistics() is Administrator-only (route-protected via
 * 'role:Administrator'). groupStatistics() is open to any authenticated
 * user at the route level, but access is scoped per-group inside the
 * method itself: Administrators can see any group; a Lecturer/Student can
 * only see groups they actually administer (see authorizeGroupAccess()).
 */
class StatisticsController extends Controller
{
    /**
     * System-wide overview for the Administrator dashboard: how many users
     * of each role, how many groups/topics/posts exist, and how active the
     * platform currently is.
     */
    public function systemStatistics()
    {
        $usersByRole = User::query()
            ->join('user_roles', 'user_roles.user_id', '=', 'users.user_id')
            ->join('roles', 'roles.role_id', '=', 'user_roles.role_id')
            ->selectRaw('roles.role_name, COUNT(DISTINCT users.user_id) as total')
            ->groupBy('roles.role_name')
            ->pluck('total', 'role_name');

        return response()->json([
            'total_users' => User::count(),
            'users_by_role' => $usersByRole,
            'total_groups' => Group::count(),
            'total_topics' => Topic::count(),
            'total_posts' => Post::count(),
            'total_replies' => Reply::count(),
            'active_users_last_7_days' => User::where('last_active_at', '>=', now()->subDays(7))->count(),
            'currently_blacklisted_users' => User::whereHas('blacklists', fn ($q) => $q->where('end_date', '>', now()))
                ->count(),
        ]);
    }

    /**
     * Determines whether the given user may view stats for this specific
     * group: Administrators always can; a Lecturer or Student may only if
     * they are that group's owner or an active GroupAdmin for it.
     */
    private function authorizeGroupAccess(User $user, Group $group): bool
    {
        if ($user->hasRole('Administrator')) {
            return true;
        }

        if ($group->admin_id == $user->user_id) {
            return true;
        }

        return GroupAdmin::where('group_id', $group->group_id)
            ->where('user_id', $user->user_id)
            ->where('is_active', true)
            ->exists();
    }

    /** Aggregates live metrics: total posts, active contributors, banned individuals, unanswered topics. */
    public function groupStatistics(Request $request, Group $group)
    {
        if (! $this->authorizeGroupAccess($request->user(), $group)) {
            return response()->json([
                'message' => 'Access denied. You must be this group\'s lecturer or an active group admin to view its statistics.',
            ], Response::HTTP_FORBIDDEN);
        }

        $totalPosts = Post::whereHas('topic', fn ($q) => $q->where('group_id', $group->group_id))->count();

        $activeContributors = $group->members()
            ->where('last_active_at', '>=', now()->subDays(7))
            ->count();

        $bannedIndividuals = $group->blacklists()->where('end_date', '>', now())->count();

        $unansweredTopics = $group->topics()->doesntHave('posts')->count();

        // "Struggling Students" roster: idle for over 7 days.
        $strugglingStudents = $group->members()
            ->where(function ($q) {
                $q->where('last_active_at', '<', now()->subDays(7))->orWhereNull('last_active_at');
            })
            ->get(['users.user_id', 'users.full_name', 'users.last_active_at']);

        return response()->json([
            'group' => $group->name,
            'total_posts' => $totalPosts,
            'active_contributors' => $activeContributors,
            'banned_individuals' => $bannedIndividuals,
            'unanswered_topics' => $unansweredTopics,
            'struggling_students' => $strugglingStudents,
        ]);
    }
}
