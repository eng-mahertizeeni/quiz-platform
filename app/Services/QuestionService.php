<?php

namespace App\Services;

use App\Models\Question;
use App\Models\SubmittedQuestion;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class QuestionService
{
    public function submitQuestion(User $user, array $data, ?string $imagePath = null): SubmittedQuestion
    {
        return SubmittedQuestion::create([
            'user_id' => $user->id,
            'category_id' => $data['category_id'],
            'question_text' => $data['question_text'],
            'answer_a' => $data['answer_a'],
            'answer_b' => $data['answer_b'],
            'answer_c' => $data['answer_c'],
            'answer_d' => $data['answer_d'],
            'correct_answer' => $data['correct_answer'],
            'difficulty' => $data['difficulty'],
            'image' => $imagePath,
            'status' => 'pending',
        ]);
    }

    public function approve(SubmittedQuestion $submission, User $admin): Question
    {
        return DB::transaction(function () use ($submission, $admin) {
            $pointsMap = [
                'medium' => 250,
                'hard' => 500,
                'very_hard' => 750,
            ];

            $question = Question::create([
                'category_id' => $submission->category_id,
                'created_by' => $submission->user_id,
                'question_text' => $submission->question_text,
                'answer_a' => $submission->answer_a,
                'answer_b' => $submission->answer_b,
                'answer_c' => $submission->answer_c,
                'answer_d' => $submission->answer_d,
                'correct_answer' => $submission->correct_answer,
                'difficulty' => $submission->difficulty,
                'points' => $pointsMap[$submission->difficulty],
                'image' => $submission->image,
                'status' => 'active',
            ]);

            $submission->update([
                'status' => 'approved',
                'reviewed_by' => $admin->id,
                'reviewed_at' => now(),
                'converted_question_id' => $question->id,
            ]);

            return $question;
        });
    }

    public function reject(SubmittedQuestion $submission, User $admin, string $reason): void
    {
        $submission->update([
            'status' => 'rejected',
            'reviewed_by' => $admin->id,
            'reviewed_at' => now(),
            'rejection_reason' => $reason,
        ]);
    }
}