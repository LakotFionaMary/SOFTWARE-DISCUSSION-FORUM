<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\GroupAdmin;
use App\Models\Membership;
use Illuminate\Http\Request;

/**
 * Group management (supports SDD 5.1 On-boarding and 5.2 Moderation modules,
 * since groups own the inactivity/blacklist configuration).
 */
class GroupController extends Controller
{
    public function index(Request $request)
    {
        $groups = Group::withCount(['members', 'topics'])->paginate(20);
        // Let the dashboard tell members apart from groups the student
        // hasn't joined yet, since only members may open a group's topics.
        $userId = $request->user()->user_id;
        $groups->getCollection()->transform(function (Group $group) use ($request, $userId) {
            $group->is_member = $request->user()->hasRole('Administrator')
                || $group->members()->where('users.user_id', $userId)->exists();

            return $group;
        });

        return response()->json($groups );
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'inactivity_warning_period' => 'nullable|integer|min:1',
            'blacklist_duration_days' => 'nullable|integer|min:1',
        ]);

        $group = Group::create([
            'name' => $request->name,
            'description' => $request->description,
            'admin_id' => $request->user()->user_id,
            'inactivity_warning_period' => $request->input('inactivity_warning_period', 7),
            'blacklist_duration_days' => $request->input('blacklist_duration_days', 14),
        ]);

        GroupAdmin::create([
            'user_id' => $request->user()->user_id,
            'group_id' => $group->group_id,
            'appointed_at' => now(),
            'appointed_by' => $request->user()->user_id,
            'is_active' => true,
        ]);

        Membership::create([
            'user_id' => $request->user()->user_id,
            'group_id' => $group->group_id,
            'rules_accepted' => true,
            'joined_at' => now(),
            'role' => 'Administrator',
        ]);

        return response()->json($group, 201);
    }

    public function show(Group $group)
    {
        return response()->json($group->load(['admin', 'topics' => fn ($q) => $q->latest()->limit(10)]));
    }

    public function joining(Group $group)
   {
    $user = auth()->user();

    $user->groups()->syncWithoutDetaching([
        $group->id
    ]);

    return response()->json([
        'message' => 'Joined group successfully'
    ]);
    }

    /** Join a group; requires the member to accept the group's rules (SDD "Membership" table). */
    public function join(Request $request, Group $group)
    {
        $request->validate(['rules_accepted' => 'required|boolean|accepted']);

        if ($request->user()->isBlacklistedIn($group->group_id)) {
            return response()->json(['message' => 'You are currently blacklisted from this group.'], 403);
        }

        $membership = Membership::firstOrCreate(
            ['user_id' => $request->user()->user_id, 'group_id' => $group->group_id],
            ['rules_accepted' => true, 'joined_at' => now(), 'role' => 'Member']
        );

        return response()->json($membership, 201);
    }

    public function members(Group $group)
    {
        return response()->json($group->members()->withPivot(['role', 'joined_at'])->paginate(50));
    }
}
