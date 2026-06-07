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
    /**
     * @OA\Get(
     *      path="/api/practice/categories",
     *      description="Get categories with user's practice progress.",
     *      tags={"Practice"},
     *      security={{"bearer_token":{}}},
     *      @OA\Response(response=200, description="Categories with progress"),
     *      @OA\Response(response=401, description="Unauthenticated")
     * )
     */
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

    /**
     * @OA\Get(
     *      path="/api/practice/{category}",
     *      description="Get next unanswered question for a category.",
     *      tags={"Practice"},
     *      security={{"bearer_token":{}}},
     *      @OA\Parameter(name="category", in="path", required=true, @OA\Schema(type="integer")),
     *      @OA\Response(response=200, description="Question data"),
     *      @OA\Response(response=401, description="Unauthenticated"),
     *      @OA\Response(response=404, description="Category not found")
     * )
     */
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

    /**
     * @OA\Post(
     *      path="/api/practice/answer",
     *      description="Submit an answer for a practice question.",
     *      tags={"Practice"},
     *      security={{"bearer_token":{}}},
     *      @OA\RequestBody(
     *           required=true,
     *           @OA\MediaType(
     *              mediaType="multipart/form-data",
     *              @OA\Schema(
     *                  @OA\Property(property="question_id", type="integer", description="Question ID"),
     *                  @OA\Property(property="answer", type="string", enum={"a","b","c","d"}, description="Selected answer"),
     *              )
     *          )
     *      ),
     *      @OA\Response(response=200, description="Answer result"),
     *      @OA\Response(response=401, description="Unauthenticated"),
     *      @OA\Response(response=422, description="Validation error")
     * )
     */
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
