<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Table 21: Role table
 */
class Role extends Model
{
    use HasFactory;

    protected $primaryKey = 'role_id';

    protected $fillable = ['role_name', 'description', 'permissions'];

    /** Users who hold this role. */
    public function users()
    {
        return $this->belongsToMany(User::class, 'user_roles', 'role_id', 'user_id')
            ->withPivot('assigned_at', 'assigned_by')
            ->withTimestamps();
    }
}
