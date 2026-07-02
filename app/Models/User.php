<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * Table 5: User table
 */
class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $primaryKey = 'user_id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'full_name',
        'email',
        'password_hash',
        'presence_status',
        'last_active_at',
    ];

    protected $hidden = [
        'password_hash',
        'remember_token',
    ];

    protected $casts = [
        'last_active_at' => 'datetime',
    ];

    /**
     * Auth::attempt() compares against this column instead of the
     * Laravel-default 'password' column, since Table 5 names it
     * password_hash.
     */
    public function getAuthPassword()
    {
        return $this->password_hash;
    }

    /** Roles assigned to this user (Table 6: UserRole pivot -> Table 21: Role). */
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'user_roles', 'user_id', 'role_id')
            ->withPivot('assigned_at', 'assigned_by')
            ->withTimestamps();
    }

    public function hasRole(string $role): bool
    {
        return $this->roles()->where('role_name', ucfirst($role))->exists();
    }

    public function isAdmin(): bool    { return $this->hasRole('administrator') || $this->hasRole('admin'); }
    public function isLecturer(): bool { return $this->hasRole('lecturer'); }
    public function isStudent(): bool  { return $this->hasRole('student'); }
}
