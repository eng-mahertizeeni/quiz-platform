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

    /**
     * @OA\Post(
     *      path="/api/questions/submit",
     *      description="Submit a new question for review.",
     *      tags={"Questions"},
     *      security={{"bearer_token":{}}},
     *      @OA\RequestBody(
     *           required=true,
     *           @OA\MediaType(
     *              mediaType="multipart/form-data",
     *              @OA\Schema(
     *                  @OA\Property(property="category_id", type="integer", description="Category ID"),
     *                  @OA\Property(property="question_text", type="string", description="Question text (min 10 chars)"),
     *                  @OA\Property(property="answer_a", type="string", description="Answer A"),
     *                  @OA\Property(property="answer_b", type="string", description="Answer B"),
     *                  @OA\Property(property="answer_c", type="string", description="Answer C"),
     *                  @OA\Property(property="answer_d", type="string", description="Answer D"),
     *                  @OA\Property(property="correct_answer", type="string", enum={"a","b","c","d"}, description="Correct answer letter"),
     *                  @OA\Property(property="difficulty", type="string", enum={"medium","hard","very_hard"}, description="Difficulty level"),
     *                  @OA\Property(property="image", type="string", format="binary", description="Optional image (max 2MB)"),
     *              )
     *          )
     *      ),
     *      @OA\Response(response=201, description="Question submitted"),
     *      @OA\Response(response=401, description="Unauthenticated"),
     *      @OA\Response(response=422, description="Validation error")
     * )
     */
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

    /**
     * @OA\Get(
     *      path="/api/questions/my-submissions",
     *      description="Get authenticated user's submitted questions.",
     *      tags={"Questions"},
     *      security={{"bearer_token":{}}},
     *      @OA\Response(response=200, description="List of submissions"),
     *      @OA\Response(response=401, description="Unauthenticated")
     * )
     */
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
