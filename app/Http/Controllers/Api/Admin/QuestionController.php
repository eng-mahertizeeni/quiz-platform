<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Http\Resources\QuestionResource;
use App\Http\Resources\SubmittedQuestionResource;
use App\Models\Category;
use App\Models\Question;
use App\Models\SubmittedQuestion;
use App\Services\QuestionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class QuestionController extends Controller
{
    public function __construct(private QuestionService $questionService) {}

    public function index(Request $request): JsonResponse
    {
        $query = Question::with(['category', 'creator'])->withTrashed();

        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        if ($request->filled('difficulty')) {
            $query->where('difficulty', $request->difficulty);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $query->where('question_text', 'like', '%' . $request->search . '%');
        }

        $questions = $query->latest()->paginate($request->per_page ?? 20);

        return response()->json([
            'questions'  => QuestionResource::collection($questions),
            'pagination' => [
                'current_page' => $questions->currentPage(),
                'last_page'    => $questions->lastPage(),
                'per_page'     => $questions->perPage(),
                'total'        => $questions->total(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'category_id'    => 'required|exists:categories,id',
            'question_text'  => 'required|string|max:1000',
            'question_text_ar' => 'nullable|string|max:1000',
            'answer_a'       => 'required|string|max:300',
            'answer_b'       => 'required|string|max:300',
            'answer_c'       => 'required|string|max:300',
            'answer_d'       => 'required|string|max:300',
            'correct_answer' => 'required|in:a,b,c,d',
            'difficulty'     => 'required|in:easy,medium,hard',
            'image'          => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'status'         => 'nullable|in:active,inactive',
        ]);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('questions', 'public');
        }

        $validated['points'] = Question::DIFFICULTY_POINTS[$validated['difficulty']];
        $validated['status'] ??= 'active';
        $validated['created_by'] = Auth::id();

        $question = Question::create($validated);

        return response()->json([
            'message'  => 'تم إضافة السؤال بنجاح',
            'question' => new QuestionResource($question->load('category')),
        ], 201);
    }

    public function show(Question $question): JsonResponse
    {
        $question->load('category', 'creator');

        return response()->json([
            'question' => new QuestionResource($question),
        ]);
    }

    public function update(Request $request, Question $question): JsonResponse
    {
        $validated = $request->validate([
            'category_id'    => 'required|exists:categories,id',
            'question_text'  => 'required|string|max:1000',
            'question_text_ar' => 'nullable|string|max:1000',
            'answer_a'       => 'required|string|max:300',
            'answer_b'       => 'required|string|max:300',
            'answer_c'       => 'required|string|max:300',
            'answer_d'       => 'required|string|max:300',
            'correct_answer' => 'required|in:a,b,c,d',
            'difficulty'     => 'required|in:easy,medium,hard',
            'image'          => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'status'         => 'nullable|in:active,inactive',
        ]);

        if ($request->hasFile('image')) {
            if ($question->image) {
                Storage::disk('public')->delete($question->image);
            }
            $validated['image'] = $request->file('image')->store('questions', 'public');
        }

        $validated['points'] = Question::DIFFICULTY_POINTS[$validated['difficulty']];
        $question->update($validated);

        return response()->json([
            'message'  => 'تم تحديث السؤال بنجاح',
            'question' => new QuestionResource($question->fresh()->load('category')),
        ]);
    }

    public function destroy(Question $question): JsonResponse
    {
        $question->delete();

        return response()->json([
            'message' => 'تم حذف السؤال',
        ]);
    }

    public function submissions(Request $request): JsonResponse
    {
        $query = SubmittedQuestion::with(['user', 'category']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $submissions = $query->latest()->paginate($request->per_page ?? 20);

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

    public function reviewSubmission(SubmittedQuestion $submission): JsonResponse
    {
        $submission->load(['user', 'category']);

        return response()->json([
            'submission' => new SubmittedQuestionResource($submission),
        ]);
    }

    public function approveSubmission(SubmittedQuestion $submission): JsonResponse
    {
        $question = $this->questionService->approve($submission, Auth::user());

        return response()->json([
            'message'  => 'تم قبول السؤال ونشره',
            'question' => new QuestionResource($question->load('category')),
        ]);
    }

    public function rejectSubmission(Request $request, SubmittedQuestion $submission): JsonResponse
    {
        $request->validate(['reason' => 'required|string|max:500']);

        $this->questionService->reject($submission, Auth::user(), $request->reason);

        return response()->json([
            'message' => 'تم رفض السؤال',
        ]);
    }
}
