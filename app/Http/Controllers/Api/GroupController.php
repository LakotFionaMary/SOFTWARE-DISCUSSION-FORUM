<?php
 
namespace App\Http\Controllers\Api;
 
use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\GroupAdmin;
use App\Models\GroupJoinRequest;
use App\Models\Membership;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Http\Request;
 
/**
 * Group management (supports SDD 5.1 On-boarding and 5.2 Moderation modules,
 * since groups own the inactivity/blacklist configuration).
 */
class GroupController extends Controller
{
    public function __construct(private NotificationService $notifications)
    {
    }
 
    public function index(Request $request)
    {
        $userId = $request->user()->user_id;
 
        $myMembershipGroupIds = Membership::where('user_id', $userId)
            ->pluck('group_id')
            ->flip();
 
        $myAdminGroupIds = GroupAdmin::where('user_id', $userId)
            ->where('is_active', true)
            ->pluck('group_id')
            ->flip();
 
        // A pending join request means "Join" should show "Request pending"
        // instead of letting the user send a second request.
        $myPendingRequestGroupIds = GroupJoinRequest::where('user_id', $userId)
            ->where('status', 'pending')
            ->pluck('group_id')
            ->flip();
 
        $groups = Group::withCount(['members', 'topics'])->paginate(20);
 
        $groups->getCollection()->transform(function ($group) use ($request, $myMembershipGroupIds, $myAdminGroupIds, $myPendingRequestGroupIds) {
            $group->is_member = $myMembershipGroupIds->has($group->group_id);
            $group->is_group_admin = $myAdminGroupIds->has($group->group_id);
            $group->is_banned = $request->user()->isBlacklistedIn($group->group_id);
            $group->has_pending_request = $myPendingRequestGroupIds->has($group->group_id);
 
            $group->is_owner = $group->admin_id == $request->user()->user_id;
            $group->can_view_group_statistics = $group->is_owner || $myAdminGroupIds->has($group->group_id);
 
            return $group;
        });
 
        return response()->json($groups);
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
 
    /**
     * Request to join a group. No longer creates membership instantly —
     * it creates a pending GroupJoinRequest and notifies the group's
     * admin(s), who must approve or decline it (see approveJoinRequest /
     * declineJoinRequest below).
     */
    public function join(Request $request, Group $group)
    {
        $request->validate(['rules_accepted' => 'required|boolean|accepted']);
 
        $user = $request->user();
 
        if ($user->isBlacklistedIn($group->group_id)) {
            return response()->json(['message' => 'You are currently blacklisted from this group.'], 403);
        }
 
        if (Membership::where('user_id', $user->user_id)->where('group_id', $group->group_id)->exists()) {
            return response()->json(['message' => 'You are already a member of this group.'], 409);
        }
 
        $existing = GroupJoinRequest::where('user_id', $user->user_id)
            ->where('group_id', $group->group_id)
            ->where('status', 'pending')
            ->first();
 
        if ($existing) {
            return response()->json(['message' => 'Your request to join is already pending approval.', 'status' => 'pending'], 200);
        }
 
        $joinRequest = GroupJoinRequest::create([
            'user_id' => $user->user_id,
            'group_id' => $group->group_id,
            'status' => 'pending',
            'rules_accepted' => true,
            'requested_at' => now(),
        ]);
 
        // Notify this group's admin(s): its owning creator (admin_id) plus
        // any active GroupAdmin rows — mirrors authorizeGroupAdmin() below.
        $adminIds = GroupAdmin::where('group_id', $group->group_id)
            ->where('is_active', true)
            ->pluck('user_id')
            ->push($group->admin_id)
            ->unique();
 
        $admins = User::whereIn('user_id', $adminIds)->get();
 
        $this->notifications->sendToMany(
            $admins,
            // 'General' — same reason as flag notifications elsewhere in
            // this codebase: notifications.type is a strict DB enum with
            // no 'Join Request' value.
            'General',
            "{$user->full_name} requested to join '{$group->name}'.",
            'GroupJoinRequest',
            $joinRequest->join_request_id
        );
 
        return response()->json([
            'message' => 'Your request to join has been sent for approval.',
            'status' => 'pending',
            'join_request' => $joinRequest,
        ], 201);
    }
 
   public function members(Group $group)
    {
        // Real group-admin status (owner OR active GroupAdmin row) — not
        // the membership pivot's 'role' column, which only reflects who
        // created the group vs joined it, not who currently administers it.
        $adminIds = GroupAdmin::where('group_id', $group->group_id)
            ->where('is_active', true)
            ->pluck('user_id')
            ->push($group->admin_id)
            ->unique();

        $members = $group->members()->withPivot(['role', 'joined_at'])->paginate(50);

        $members->getCollection()->transform(function ($member) use ($adminIds) {
            $member->is_admin = $adminIds->contains($member->user_id);
            return $member;
        });

        return response()->json($members);
    }
 
    /** Mirrors StatisticsController::authorizeGroupAccess() — same rule (owner OR active GroupAdmin), reused here for join-request approval. */
    private function authorizeGroupAdmin(User $user, Group $group): bool
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
 
    /** List this group's pending join requests. Group-admin only. */
    public function joinRequests(Request $request, Group $group)
    {
        if (! $this->authorizeGroupAdmin($request->user(), $group)) {
            return response()->json(['message' => 'Access denied. Only this group\'s admin can view join requests.'], 403);
        }
 
        return response()->json(
            GroupJoinRequest::where('group_id', $group->group_id)
                ->where('status', 'pending')
                ->with('user')
                ->latest('requested_at')
                ->get()
        );
    }
 
    public function approveJoinRequest(Request $request, Group $group, GroupJoinRequest $joinRequest)
    {
        if (! $this->authorizeGroupAdmin($request->user(), $group)) {
            return response()->json(['message' => 'Access denied.'], 403);
        }
 
        if ($joinRequest->group_id !== $group->group_id) {
            return response()->json(['message' => 'This request does not belong to this group.'], 404);
        }
 
        if ($joinRequest->status !== 'pending') {
            return response()->json(['message' => 'This request has already been resolved.'], 409);
        }
 
        Membership::firstOrCreate(
            ['user_id' => $joinRequest->user_id, 'group_id' => $group->group_id],
            ['rules_accepted' => true, 'joined_at' => now(), 'role' => 'Member']
        );
 
        $joinRequest->update([
            'status' => 'approved',
            'resolved_at' => now(),
            'resolved_by' => $request->user()->user_id,
        ]);
 
        $this->notifications->send(
            $joinRequest->user,
            'General',
            "Your request to join '{$group->name}' was approved.",
            'GroupJoinRequest',
            $joinRequest->join_request_id
        );
 
        return response()->json(['message' => 'Request approved.', 'join_request' => $joinRequest]);
    }
 
    public function declineJoinRequest(Request $request, Group $group, GroupJoinRequest $joinRequest)
    {
        if (! $this->authorizeGroupAdmin($request->user(), $group)) {
            return response()->json(['message' => 'Access denied.'], 403);
        }
 
        if ($joinRequest->group_id !== $group->group_id) {
            return response()->json(['message' => 'This request does not belong to this group.'], 404);
        }
 
        if ($joinRequest->status !== 'pending') {
            return response()->json(['message' => 'This request has already been resolved.'], 409);
        }
 
        $joinRequest->update([
            'status' => 'declined',
            'resolved_at' => now(),
            'resolved_by' => $request->user()->user_id,
        ]);
 
        $this->notifications->send(
            $joinRequest->user,
            'General',
            "Your request to join '{$group->name}' was declined.",
            'GroupJoinRequest',
            $joinRequest->join_request_id
        );
 
        return response()->json(['message' => 'Request declined.', 'join_request' => $joinRequest]);
    }
}
 
