<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Post extends Model
{
    use HasFactory;

    protected $primaryKey = 'post_id';

    protected $fillable = [
        'topic_id',
        'author_id',
        'content',
        'attachment_url',
        'posted_at',
        'is_flagged'
    ];

    protected $casts = [
        'posted_at' => 'datetime',
        'is_flagged' => 'boolean'
    ];



    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function topic()
    {
        return $this->belongsTo(Topic::class,'topic_id','topic_id');
    }

    public function author()
    {
        return $this->belongsTo(User::class,'author_id','user_id');
    }

    public function replies()
    {
        return $this->hasMany(Reply::class,'post_id','post_id');
    }

    public function exclusions()
    {
        return $this->hasMany(PostExclusion::class,'post_id','post_id');
    }
}
