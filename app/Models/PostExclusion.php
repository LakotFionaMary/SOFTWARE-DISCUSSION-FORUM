<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * PostExclusion - hides a post from specific members (selective communication).
 * See SDD 4.2 "PostExclusion" table.
 */
class PostExclusion extends Model
{
    protected $table = 'post_exclusions';
    protected $primaryKey = 'exclusion_id';

    protected $fillable = ['post_id', 'excluded_user_id'];

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class, 'post_id', 'post_id');
    }

    public function excludedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'excluded_user_id', 'user_id');
    }
}