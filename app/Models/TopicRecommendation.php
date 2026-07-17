<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * TopicRecommendation - per-user cache of a topic's activity ranking.
 * See SDD 4.2 "TopicRecommendation" table and 5.8 ML Classification and
 * Recommendation.
 *
 * NOTE: previously this only held rows for topics the user hadn't posted
 * in yet ("unseen"). It now holds a row for every topic in the user's
 * groups — seen and unseen alike — since the recommendation feed ranks
 * all of them by post-count share. Anything reading this table should
 * not assume a row implies the topic is unseen by that user.
 */
class TopicRecommendation extends Model
{
    protected $table = 'topic_recommendations';
    protected $primaryKey = 'recommendation_id';

    protected $fillable = ['user_id', 'topic_id', 'relevance_score', 'generated_at'];

    protected $casts = ['relevance_score' => 'decimal:3', 'generated_at' => 'datetime'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function topic(): BelongsTo
    {
        return $this->belongsTo(Topic::class, 'topic_id', 'topic_id');
    }
}
