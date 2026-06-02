<?php

namespace App\Http\Controllers;

use App\Http\Requests\Question\SubmitQuestionRequest;
use App\Models\Category;
use App\Models\SubmittedQuestion;
use App\Models\User;
use App\Services\QuestionService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class QuestionSubmissionController extends Controller
{
    public function __construct(private QuestionService $questionService) {}

    public function create()
    {
        $categories = Category::active()->orderBy('name')->get();
        return view('user.submit-question', compact('categories'));
    }

    public function store(SubmitQuestionRequest $request)
    {
        $imagePath = null;

        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('submitted-questions', 'public');
        }
        $user = User::find(Auth::id());

        $this->questionService->submitQuestion($user, $request->validated(), $imagePath);

        return redirect()->route('questions.my-submissions')
            ->with('success', 'تم إرسال سؤالك بنجاح! سيتم مراجعته من قبل الإدارة.');
    }

    public function mySubmissions()
    {
        $user = User::find(Auth::id());
        $submissions = SubmittedQuestion::where('user_id',$user)
            ->with('category')
            ->latest()
            ->paginate(15);

        return view('user.my-submissions', compact('submissions'));
    }
}