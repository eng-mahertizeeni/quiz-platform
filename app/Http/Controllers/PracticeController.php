<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Question;
use App\Models\UserQuestionAnswer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PracticeController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $categories = Category::active()
            ->withCount(['questions' => fn($q) => $q->active()])
            ->orderBy('sort_order')
            ->get();

        $answeredCounts = UserQuestionAnswer::where('user_id', $user->id)
            ->whereHas('question', fn($q) => $q->active())
            ->with('question.category')
            ->get()
            ->groupBy(fn($a) => $a->question->category_id)
            ->map(fn($items) => $items->count());

        foreach ($categories as $cat) {
            $cat->answered_count = $answeredCounts->get($cat->id, 0);
            $cat->correct_count = UserQuestionAnswer::where('user_id', $user->id)
                ->whereHas('question', fn($q) => $q->where('category_id', $cat->id)->active())
                ->where('is_correct', true)
                ->count();
            $cat->progress = $cat->questions_count > 0
                ? round(($cat->answered_count / $cat->questions_count) * 100, 1)
                : 0;
        }

        return view('practice.index', compact('categories'));
    }

    public function practice(Category $category)
    {
        $user = Auth::user();

        $answeredIds = UserQuestionAnswer::where('user_id', $user->id)
            ->whereHas('question', fn($q) => $q->where('category_id', $category->id))
            ->pluck('question_id');

        $question = Question::where('category_id', $category->id)
            ->where('status', 'active')
            ->whereNotIn('id', $answeredIds)
            ->inRandomOrder()
            ->first();

        $total = Question::where('category_id', $category->id)->where('status', 'active')->count();
        $answered = $answeredIds->count();
        $progress = $total > 0 ? round(($answered / $total) * 100, 1) : 0;

        $correct_count = UserQuestionAnswer::where('user_id', $user->id)
            ->whereHas('question', fn($q) => $q->where('category_id', $category->id)->active())
            ->where('is_correct', true)
            ->count();

        if (!$question) {
            return redirect()->route('practice.index')
                ->with('success', '🎉 لقد أجبت على جميع الأسئلة في هذه الفئة!')
                ->with('category_completed', $category->name);
        }

        return view('practice.quiz', compact('question', 'category', 'progress', 'answered', 'total', 'correct_count'));
    }

    public function answer(Request $request)
    {
        $validated = $request->validate([
            'question_id' => 'required|exists:questions,id',
            'answer' => 'required|in:a,b,c,d',
        ]);

        $question = Question::findOrFail($validated['question_id']);
        $user = Auth::user();

        $exists = UserQuestionAnswer::where('user_id', $user->id)
            ->where('question_id', $question->id)
            ->exists();

        if (!$exists) {
            $isCorrect = $validated['answer'] === $question->correct_answer;

            UserQuestionAnswer::create([
                'user_id' => $user->id,
                'question_id' => $question->id,
                'is_correct' => $isCorrect,
            ]);
        } else {
            $isCorrect = UserQuestionAnswer::where('user_id', $user->id)
                ->where('question_id', $question->id)
                ->value('is_correct');
        }

        return response()->json([
            'is_correct' => $isCorrect,
            'correct_answer' => $question->correct_answer,
            'correct_text' => $question->correctAnswerText,
        ]);
    }

    public function next(Category $category)
    {
        $user = Auth::user();

        $answeredIds = UserQuestionAnswer::where('user_id', $user->id)
            ->whereHas('question', fn($q) => $q->where('category_id', $category->id))
            ->pluck('question_id');

        $question = Question::where('category_id', $category->id)
            ->where('status', 'active')
            ->whereNotIn('id', $answeredIds)
            ->inRandomOrder()
            ->first();

        $total = Question::where('category_id', $category->id)->where('status', 'active')->count();
        $answered = $answeredIds->count();
        $progress = $total > 0 ? round(($answered / $total) * 100, 1) : 0;

        if (!$question) {
            return response()->json(['done' => true, 'progress' => 100, 'answered' => $total, 'total' => $total]);
        }

        return response()->json([
            'done' => false,
            'question' => [
                'id' => $question->id,
                'question_text' => $question->question_text,
                'answer_a' => $question->answer_a,
                'answer_b' => $question->answer_b,
                'answer_c' => $question->answer_c,
                'answer_d' => $question->answer_d,
                'difficulty' => $question->difficulty,
                'difficulty_label' => $question->difficulty_label,
                'difficulty_color' => $question->difficulty_color,
                'points' => $question->points,
            ],
            'progress' => $progress,
            'answered' => $answered,
            'total' => $total,
        ]);
    }
}
