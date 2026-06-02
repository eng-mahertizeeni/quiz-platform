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
            'category_ids' => 'required|array|size:6',
            'category_ids.*' => 'required|exists:categories,id',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $categoryIds = $this->input('category_ids', []);
            foreach ($categoryIds as $id) {
                $category = Category::find($id);
                if ($category && !$category->hasEnoughQuestions()) {
                    $validator->errors()->add(
                        'category_ids',
                        "الفئة \"{$category->name}\" لا تحتوي على أسئلة كافية (يجب 2 لكل مستوى صعوبة)"
                    );
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'category_ids.required' => 'يجب اختيار الفئات',
            'category_ids.size' => 'يجب اختيار 6 فئات بالضبط',
            'category_ids.*.exists' => 'إحدى الفئات المختارة غير موجودة',
        ];
    }
}