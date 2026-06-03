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

/**
 * @OA\Tag(name="Admin Questions", description="Admin question management endpoints")
 */
class QuestionController extends Controller
{
    public function __construct(private QuestionService $questionService) {}

    /**
     * @OA\Get(
     *      path="/api/admin/questions",
     *      description="List questions with filtering.",
     *      tags={"Admin Questions"},
     *      security={{"bearer_token":{}}},
     *      @OA\Parameter(name="category", in="query", @OA\Schema(type="integer")),
     *      @OA\Parameter(name="difficulty", in="query", @OA\Schema(type="string")),
     *      @OA\Parameter(name="status", in="query", @OA\Schema(type="string")),
     *      @OA\Parameter(name="search", in="query", @OA\Schema(type="string")),
     *      @OA\Response(response=200, description="List of questions"),
     *      @OA\Response(response=403, description="Forbidden - Admin only")
     * )
     */
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

    /**
     * @OA\Post(
     *      path="/api/admin/questions",
     *      description="Create a new question.",
     *      tags={"Admin Questions"},
     *      security={{"bearer_token":{}}},
     *      @OA\RequestBody(
     *           required=true,
     *           @OA\MediaType(
     *              mediaType="multipart/form-data",
     *              @OA\Schema(
     *                  @OA\Property(property="category_id", type="integer", description="Category ID"),
     *                  @OA\Property(property="question_text", type="string", description="Question text"),
     *                  @OA\Property(property="question_text_ar", type="string", description="Question text (Arabic)"),
     *                  @OA\Property(property="answer_a", type="string", description="Answer A"),
     *                  @OA\Property(property="answer_b", type="string", description="Answer B"),
     *                  @OA\Property(property="answer_c", type="string", description="Answer C"),
     *                  @OA\Property(property="answer_d", type="string", description="Answer D"),
     *                  @OA\Property(property="correct_answer", type="string", enum={"a","b","c","d"}, description="Correct answer letter"),
     *                  @OA\Property(property="difficulty", type="string", enum={"medium","hard","very_hard"}, description="Difficulty level"),
     *                  @OA\Property(property="status", type="string", enum={"active","inactive"}, description="Question status"),
     *                  @OA\Property(property="image", type="string", format="binary", description="Image (max 2MB)"),
     *              )
     *          )
     *      ),
     *      @OA\Response(response=201, description="Question created"),
     *      @OA\Response(response=403, description="Forbidden"),
     *      @OA\Response(response=422, description="Validation error")
     * )
     */
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
            'difficulty'     => 'required|in:medium,hard,very_hard',
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

    /**
     * @OA\Get(
     *      path="/api/admin/questions/{question}",
     *      description="Get a single question details.",
     *      tags={"Admin Questions"},
     *      security={{"bearer_token":{}}},
     *      @OA\Parameter(name="question", in="path", required=true, @OA\Schema(type="integer")),
     *      @OA\Response(response=200, description="Question details"),
     *      @OA\Response(response=403, description="Forbidden"),
     *      @OA\Response(response=404, description="Not found")
     * )
     */
    public function show(Question $question): JsonResponse
    {
        $question->load('category', 'creator');

        return response()->json([
            'question' => new QuestionResource($question),
        ]);
    }

    /**
     * @OA\Post(
     *      path="/api/admin/questions/{question}",
     *      description="Update a question.",
     *      tags={"Admin Questions"},
     *      security={{"bearer_token":{}}},
     *      @OA\Parameter(name="question", in="path", required=true, @OA\Schema(type="integer")),
     *      @OA\RequestBody(
     *           required=true,
     *           @OA\MediaType(
     *              mediaType="multipart/form-data",
     *              @OA\Schema(
     *                  @OA\Property(property="category_id", type="integer"),
     *                  @OA\Property(property="question_text", type="string"),
     *                  @OA\Property(property="question_text_ar", type="string"),
     *                  @OA\Property(property="answer_a", type="string"),
     *                  @OA\Property(property="answer_b", type="string"),
     *                  @OA\Property(property="answer_c", type="string"),
     *                  @OA\Property(property="answer_d", type="string"),
     *                  @OA\Property(property="correct_answer", type="string", enum={"a","b","c","d"}),
     *                  @OA\Property(property="difficulty", type="string", enum={"medium","hard","very_hard"}),
     *                  @OA\Property(property="status", type="string", enum={"active","inactive"}),
     *                  @OA\Property(property="image", type="string", format="binary"),
     *              )
     *          )
     *      ),
     *      @OA\Response(response=200, description="Question updated"),
     *      @OA\Response(response=403, description="Forbidden"),
     *      @OA\Response(response=422, description="Validation error")
     * )
     */
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
            'difficulty'     => 'required|in:medium,hard,very_hard',
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

    /**
     * @OA\Delete(
     *      path="/api/admin/questions/{question}",
     *      description="Delete a question.",
     *      tags={"Admin Questions"},
     *      security={{"bearer_token":{}}},
     *      @OA\Parameter(name="question", in="path", required=true, @OA\Schema(type="integer")),
     *      @OA\Response(response=200, description="Question deleted"),
     *      @OA\Response(response=403, description="Forbidden"),
     *      @OA\Response(response=404, description="Not found")
     * )
     */
    public function destroy(Question $question): JsonResponse
    {
        $question->delete();

        return response()->json([
            'message' => 'تم حذف السؤال',
        ]);
    }

    /**
     * @OA\Get(
     *      path="/api/admin/questions-submissions",
     *      description="List question submissions.",
     *      tags={"Admin Questions"},
     *      security={{"bearer_token":{}}},
     *      @OA\Parameter(name="status", in="query", @OA\Schema(type="string"), description="Filter by status (pending, approved, rejected)"),
     *      @OA\Response(response=200, description="List of submissions"),
     *      @OA\Response(response=403, description="Forbidden - Admin only")
     * )
     */
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

    /**
     * @OA\Get(
     *      path="/api/admin/questions-submissions/{submission}",
     *      description="Get a single submission for review.",
     *      tags={"Admin Questions"},
     *      security={{"bearer_token":{}}},
     *      @OA\Parameter(name="submission", in="path", required=true, @OA\Schema(type="integer")),
     *      @OA\Response(response=200, description="Submission details"),
     *      @OA\Response(response=403, description="Forbidden"),
     *      @OA\Response(response=404, description="Not found")
     * )
     */
    public function reviewSubmission(SubmittedQuestion $submission): JsonResponse
    {
        $submission->load(['user', 'category']);

        return response()->json([
            'submission' => new SubmittedQuestionResource($submission),
        ]);
    }

    /**
     * @OA\Post(
     *      path="/api/admin/questions-submissions/{submission}/approve",
     *      description="Approve a submitted question.",
     *      tags={"Admin Questions"},
     *      security={{"bearer_token":{}}},
     *      @OA\Parameter(name="submission", in="path", required=true, @OA\Schema(type="integer")),
     *      @OA\Response(response=200, description="Submission approved"),
     *      @OA\Response(response=403, description="Forbidden"),
     *      @OA\Response(response=404, description="Not found")
     * )
     */
    public function approveSubmission(SubmittedQuestion $submission): JsonResponse
    {
        $question = $this->questionService->approve($submission, Auth::user());

        return response()->json([
            'message'  => 'تم قبول السؤال ونشره',
            'question' => new QuestionResource($question->load('category')),
        ]);
    }

    /**
     * @OA\Post(
     *      path="/api/admin/questions-submissions/{submission}/reject",
     *      description="Reject a submitted question with a reason.",
     *      tags={"Admin Questions"},
     *      security={{"bearer_token":{}}},
     *      @OA\Parameter(name="submission", in="path", required=true, @OA\Schema(type="integer")),
     *      @OA\RequestBody(
     *           required=true,
     *           @OA\MediaType(
     *              mediaType="multipart/form-data",
     *              @OA\Schema(
     *                  @OA\Property(property="reason", type="string", description="Rejection reason (max 500 chars)"),
     *              )
     *          )
     *      ),
     *      @OA\Response(response=200, description="Submission rejected"),
     *      @OA\Response(response=403, description="Forbidden"),
     *      @OA\Response(response=422, description="Validation error")
     * )
     */
    public function rejectSubmission(Request $request, SubmittedQuestion $submission): JsonResponse
    {
        $request->validate(['reason' => 'required|string|max:500']);

        $this->questionService->reject($submission, Auth::user(), $request->reason);

        return response()->json([
            'message' => 'تم رفض السؤال',
        ]);
    }
}
