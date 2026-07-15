<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

/**
 * User - any person who uses the system: member, lecturer, or admin.
 * See SDD 4.2 "User" table.
 */
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'users';
    protected $primaryKey = 'user_id';

    protected $fillable = [
        'full_name',
        'email',
        'password_hash',
        'presence_status',
        'last_active_at',
        'rules_accepted',
        'rules_accepted_at',
        'bio',
        'phone',
        'phone_public',
        'profile_picture',
        'department',
    ];

    protected $hidden = [
        'password_hash',
        'remember_token',
    ];

    protected $casts = [
        'last_active_at' => 'datetime',
        'rules_accepted_at' => 'datetime',
        'rules_accepted' => 'boolean',
        'phone_public' => 'boolean',
    ];

    // Laravel's auth system expects getAuthPassword(); we store the hash in password_hash.
    public function getAuthPassword()
    {
        return $this->password_hash;
    }

    /* ---------------- Relationships ---------------- */

    public function userRoles(): HasMany
    {
        return $this->hasMany(UserRole::class, 'user_id', 'user_id');
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'user_roles', 'user_id', 'role_id')
            ->withPivot(['assigned_at', 'assigned_by']);
    }

    public function memberships(): HasMany
    {
        return $this->hasMany(Membership::class, 'user_id', 'user_id');
    }

    public function groups()
    {
        return $this->belongsToMany(Group::class, 'memberships', 'user_id', 'group_id');
    }

    public function adminGroups(): HasMany
    {
        return $this->hasMany(Group::class, 'admin_id', 'user_id');
    }

    public function topics(): HasMany
    {
        return $this->hasMany(Topic::class, 'created_by', 'user_id');
    }

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class, 'author_id', 'user_id');
    }
  public function topicRecommendations()
{
    return $this->hasMany(TopicRecommendation::class, 'user_id', 'user_id');
}

    public function replies(): HasMany
    {
        return $this->hasMany(Reply::class, 'author_id', 'user_id');
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class, 'user_id', 'user_id');
    }

    public function warnings(): HasMany
    {
        return $this->hasMany(Warning::class, 'user_id', 'user_id');
    }

    public function blacklists(): HasMany
    {
        return $this->hasMany(Blacklist::class, 'user_id', 'user_id');
    }

    public function quizAttempts(): HasMany
    {
        return $this->hasMany(QuizAttempt::class, 'user_id', 'user_id');
    }

    public function participationScores(): HasMany
    {
        return $this->hasMany(ParticipationScore::class, 'user_id', 'user_id');
    }

    public function syncRecords(): HasMany
    {
        return $this->hasMany(SyncRecord::class, 'user_id', 'user_id');
    }

    /* ---------------- Helpers ---------------- */

    public function hasRole(string $roleName): bool
    {
        return $this->roles()->where('role_name', $roleName)->exists();
    }

    public function isBlacklistedIn(int $groupId): bool
    {
        return $this->blacklists()
            ->where('group_id', $groupId)
            ->where('end_date', '>', now())
            ->exists();
    }
}