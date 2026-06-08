<?php

namespace App\Http\Requests\Game;

use App\Models\Category;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class SelectCategoriesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::check();
    }

    public function rules(): array
    {
        return [
            'category_ids' => 'required|array|min:1|max:6',
            'category_ids.*' => 'required|exists:categories,id',
        ];
    }

    public function withValidator($validator): void
    {
        // removed hasEnoughQuestions check - all categories are selectable
    }

    public function messages(): array
    {
        return [
            'category_ids.required' => 'يجب اختيار الفئات',
            'category_ids.min' => 'يجب اختيار فئة واحدة على الأقل',
            'category_ids.max' => 'يمكن اختيار 6 فئات كحد أقصى',
            'category_ids.*.exists' => 'إحدى الفئات المختارة غير موجودة',
        ];
    }
}