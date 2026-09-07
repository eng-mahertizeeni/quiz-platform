<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Http\Resources\QuestionResource;
use App\Models\Category;
use App\Models\Question;
use App\Models\UserQuestionAnswer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PracticeController extends Controller
{
    
    public function categories(): JsonResponse
    {
        $user = Auth::user();
        $categories = Category::active()
            ->withCount(['questions' => fn($q) => $q->active()])
            ->orderBy('sort_order')
            ->get()
            ->filter(fn($c) => $c->questions_count > 0)
            ->values();

        $answers = UserQuestionAnswer::where('user_id', $user->id)
            ->whereHas('question', fn($q) => $q->active())
            ->with('question.category')
            ->get()
            ->groupBy(fn($a) => $a->question->category_id);

        $categories->each(function ($cat) use ($answers) {
            $catAnswers = $answers->get($cat->id, collect());
            $cat->answered_count = $catAnswers->count();
            $cat->correct_count = $catAnswers->where('is_correct', true)->count();
            $cat->progress = $cat->questions_count > 0
                ? round(($cat->answered_count / $cat->questions_count) * 100, 1)
                : 0;
        });

        return response()->json([
            'categories' => CategoryResource::collection($categories),
        ]);
    }

    public function nextQuestion(Category $category): JsonResponse
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
            return response()->json([
                'done'     => true,
                'progress' => 100,
                'answered' => $total,
                'total'    => $total,
            ]);
        }

        return response()->json([
            'done'     => false,
            'question' => new QuestionResource($question),
            'progress' => $progress,
            'answered' => $answered,
            'total'    => $total,
        ]);
    }

    public function answer(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'question_id' => 'required|exists:questions,id',
            'answer'      => 'required|in:a,b,c,d',
        ]);

        $question = Question::findOrFail($validated['question_id']);
        $user = Auth::user();

        $exists = UserQuestionAnswer::where('user_id', $user->id)
            ->where('question_id', $question->id)
            ->exists();

        if (!$exists) {
            $isCorrect = $validated['answer'] === $question->correct_answer;

            UserQuestionAnswer::create([
                'user_id'    => $user->id,
                'question_id' => $question->id,
                'is_correct'  => $isCorrect,
            ]);
        } else {
            $isCorrect = UserQuestionAnswer::where('user_id', $user->id)
                ->where('question_id', $question->id)
                ->value('is_correct');
        }

        return response()->json([
            'is_correct'      => (bool) $isCorrect,
            'correct_answer'  => $question->correct_answer,
            'correct_text'    => $question->correct_answer_text,
        ]);
    }
}
