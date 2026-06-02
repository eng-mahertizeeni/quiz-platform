<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\QuestionRequest;
use App\Models\Category;
use App\Models\Question;
use App\Models\SubmittedQuestion;
use App\Models\User;
use App\Services\QuestionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class QuestionController extends Controller
{
    public function __construct(private QuestionService $questionService) {}

    public function index(Request $request)
    {
        $query = Question::with(['category', 'creator'])
            ->withTrashed();

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

        $questions = $query->latest()->paginate(20)->withQueryString();
        $categories = Category::active()->orderBy('name')->get();

        return view('admin.questions.index', compact('questions', 'categories'));
    }

    public function create()
    {
        $categories = Category::active()->orderBy('name')->get();
        return view('admin.questions.create', compact('categories'));
    }

    public function store(QuestionRequest $request)
    {
        $data = $request->validated();

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('questions', 'public');
        }

        $data['points'] = Question::DIFFICULTY_POINTS[$data['difficulty']];
        $data['status'] = 'active';
        $data['created_by'] = Auth::id();

        Question::create($data);

        return redirect()->route('admin.questions.index')
            ->with('success', 'تم إضافة السؤال بنجاح');
    }

    public function edit(Question $question)
    {
        $categories = Category::active()->orderBy('name')->get();
        return view('admin.questions.edit', compact('question', 'categories'));
    }

    public function update(QuestionRequest $request, Question $question)
    {
        $data = $request->validated();

        if ($request->hasFile('image')) {
            if ($question->image) {
                Storage::disk('public')->delete($question->image);
            }
            $data['image'] = $request->file('image')->store('questions', 'public');
        }

        $data['points'] = Question::DIFFICULTY_POINTS[$data['difficulty']];
        $question->update($data);

        return redirect()->route('admin.questions.index')
            ->with('success', 'تم تحديث السؤال بنجاح');
    }

    public function destroy(Question $question)
    {
        $question->delete();
        return back()->with('success', 'تم حذف السؤال');
    }

    public function submissions(Request $request)
    {
        $query = SubmittedQuestion::with(['user', 'category']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $submissions = $query->latest()->paginate(20)->withQueryString();
        return view('admin.questions.submissions', compact('submissions'));
    }

    public function reviewSubmission(SubmittedQuestion $submission)
    {
        $submission->load(['user', 'category']);
        $categories = Category::active()->orderBy('name')->get();
        return view('admin.questions.review', compact('submission', 'categories'));
    }

    public function approveSubmission(SubmittedQuestion $submission)
    {
        $user = User::find(Auth::id());
        $question = $this->questionService->approve($submission, $user);

        return redirect()->route('admin.questions.submissions')
            ->with('success', 'تم قبول السؤال ونشره');
    }

    public function rejectSubmission(Request $request, SubmittedQuestion $submission)
    {        
        $user = User::find(Auth::id());
        $request->validate(['reason' => 'required|string|max:500']);
        $this->questionService->reject($submission, $user, $request->reason);

        return redirect()->route('admin.questions.submissions')
            ->with('success', 'تم رفض السؤال');
    }
}