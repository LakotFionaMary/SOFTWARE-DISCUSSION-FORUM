<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuizAttempt extends Model
{
    protected $fillable = [
        'quiz_id', 'student_id', 'attempt_number',
        'score', 'total_marks', 'correct_count',
        'wrong_count', 'skipped_count',
        'status', 'started_at', 'submitted_at',
    ];

    protected $casts = [
        'started_at'   => 'datetime',
        'submitted_at' => 'datetime',
    ];

    public function quiz()
    {
        return $this->belongsTo(Quiz::class);
    }

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function answers()
    {
        return $this->hasMany(StudentAnswer::class, 'attempt_id');
    }

    public function getPercentageAttribute(): float
    {
        if ($this->total_marks === 0) return 0;
        return round(($this->score / $this->total_marks) * 100, 1);
    }

    public function getPassedAttribute(): bool
    {
        return $this->percentage >= 50;
    }
}
