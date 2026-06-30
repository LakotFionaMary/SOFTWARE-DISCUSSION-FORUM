<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Quiz extends Model
{
    use HasFactory;

    protected $fillable = [
        'lecturer_id', 'title', 'category', 'quiz_date',
        'start_time', 'duration', 'instructions',
        'attempts_allowed', 'shuffle', 'show_results', 'status',
    ];

    protected $casts = [
        'quiz_date'   => 'date',
        'shuffle'     => 'boolean',
        'show_results'=> 'boolean',
    ];

    // ── Relationships ──────────────────────────────────
    public function lecturer()
    {
        return $this->belongsTo(User::class, 'lecturer_id');
    }

    public function questions()
    {
        return $this->hasMany(Question::class)->orderBy('order');
    }

    public function attempts()
    {
        return $this->hasMany(QuizAttempt::class);
    }

    // ── Computed helpers ───────────────────────────────
    public function getTotalMarksAttribute(): int
    {
        return $this->questions->sum('marks');
    }

    public function getStartsAtAttribute(): Carbon
    {
        return Carbon::parse($this->quiz_date->format('Y-m-d') . ' ' . $this->start_time);
    }

    public function getEndsAtAttribute(): Carbon
    {
        return $this->starts_at->copy()->addMinutes($this->duration);
    }

    public function getStatusLiveAttribute(): string
    {
        $now = Carbon::now();
        if ($this->status !== 'published') return $this->status;
        if ($now->lt($this->starts_at))   return 'upcoming';
        if ($now->gt($this->ends_at))     return 'closed';
        return 'open';
    }

    public function getAverageScoreAttribute(): ?float
    {
        $submitted = $this->attempts()->where('status', '!=', 'in_progress');
        if ($submitted->count() === 0) return null;
        $totalMarks = $this->total_marks;
        if ($totalMarks === 0) return null;
        return round(($submitted->avg('score') / $totalMarks) * 100, 1);
    }

    // ── Scopes ─────────────────────────────────────────
    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function scopeForStudent($query, $categoryOrAll = null)
    {
        return $query->published();
    }
}
