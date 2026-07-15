<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Observers\PostObserver;

/**
 * Post - one message written by a member inside a topic. See SDD 4.2 "Posts" table.
 */
class Post extends Model
{
    protected $table = 'posts';
    protected $primaryKey = 'post_id';

    protected $fillable = ['topic_id', 'author_id', 'content', 'attachment_url', 'posted_at', 'is_flagged'];

    protected $casts = ['posted_at' => 'datetime', 'is_flagged' => 'boolean'];


   protected static function booted(): void
    {
        static::observe(PostObserver::class);
    }

    public function topic(): BelongsTo
    {
        return $this->belongsTo(Topic::class, 'topic_id', 'topic_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id', 'user_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(Reply::class, 'post_id', 'post_id');
    }

    public function exclusions(): HasMany
    {
        return $this->hasMany(PostExclusion::class, 'post_id', 'post_id');
    }

    public function socialShares(): HasMany
    {
        return $this->hasMany(SocialShare::class, 'post_id', 'post_id');
    }

    /** Whether a given user has been excluded from seeing this post. */
    public function isExcludedFor(int $userId): bool
    {
        return $this->exclusions()->where('excluded_user_id', $userId)->exists();
    }
}
