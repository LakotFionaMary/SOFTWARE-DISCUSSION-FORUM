<?php

namespace App\Http\Controllers;

use App\Models\Quiz;
use Illuminate\Support\Facades\Auth;

class LecturerController extends Controller
{
    public function index()
    {
        $quizzes   = Quiz::where('lecturer_id', Auth::id())->latest()->get();
        $published = $quizzes->where('status', 'published')->count();
        $drafts    = $quizzes->where('status', 'draft')->count();

        return view('dashboards.lecturer', compact('quizzes', 'published', 'drafts'));
    }
}