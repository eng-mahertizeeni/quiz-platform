<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Http\Resources\SubmittedQuestionResource;
use App\Models\Category;
use App\Models\SubmittedQuestion;
use App\Services\QuestionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class QuestionSubmissionController extends Controller
{
    public function __construct(private QuestionService $questionService) {}

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'category_id'    => 'required|exists:categories,id',
            'question_text'  => 'required|string|min:10|max:1000',
            'answer_a'       => 'required|string|max:300',
            'answer_b'       => 'required|string|max:300',
            'answer_c'       => 'required|string|max:300',
            'answer_d'       => 'required|string|max:300',
            'correct_answer' => 'required|in:a,b,c,d',
            'difficulty'     => 'required|in:medium,hard,very_hard',
            'image'          => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('submitted-questions', 'public');
        }

        $submission = $this->questionService->submitQuestion(Auth::user(), $validated, $imagePath);

        return response()->json([
            'message'    => 'تم إرسال سؤالك بنجاح! سيتم مراجعته من قبل الإدارة.',
            'submission' => new SubmittedQuestionResource($submission->load('category')),
        ], 201);
    }

    public function mySubmissions(): JsonResponse
    {
        $submissions = SubmittedQuestion::where('user_id', Auth::id())
            ->with('category')
            ->latest()
            ->paginate(15);

        return response()->json([
            'submissions' => SubmittedQuestionResource::collection($submissions),
            'pagination'  => [
                'current_page' => $submissions->currentPage(),
                'last_page'    => $submissions->lastPage(),
                'per_page'     => $submissions->perPage(),
                'total'        => $submissions->total(),
            ],
        ]);
    }
}
