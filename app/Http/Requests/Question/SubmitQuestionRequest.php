<?php

namespace App\Http\Requests\Question;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class SubmitQuestionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::check();
    }

    public function rules(): array
    {
        return [
            'category_id' => 'required|exists:categories,id',
            'question_text' => 'required|string|min:10|max:1000',
            'answer_a' => 'required|string|max:300',
            'answer_b' => 'required|string|max:300',
            'answer_c' => 'required|string|max:300',
            'answer_d' => 'required|string|max:300',
            'correct_answer' => 'required|in:a,b,c,d',
            'difficulty' => 'required|in:easy,medium,hard',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ];
    }

    public function messages(): array
    {
        return [
            'category_id.required' => 'يجب اختيار الفئة',
            'question_text.required' => 'نص السؤال مطلوب',
            'question_text.min' => 'يجب أن يكون السؤال 10 أحرف على الأقل',
            'answer_a.required' => 'الإجابة أ مطلوبة',
            'answer_b.required' => 'الإجابة ب مطلوبة',
            'answer_c.required' => 'الإجابة ج مطلوبة',
            'answer_d.required' => 'الإجابة د مطلوبة',
            'correct_answer.required' => 'يجب تحديد الإجابة الصحيحة',
            'difficulty.required' => 'يجب تحديد مستوى الصعوبة',
        ];
    }
}