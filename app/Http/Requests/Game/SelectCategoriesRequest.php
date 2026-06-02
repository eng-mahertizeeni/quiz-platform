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
        $validator->after(function ($validator) {
            $categoryIds = $this->input('category_ids', []);
            foreach ($categoryIds as $id) {
                $category = Category::find($id);
                if ($category && !$category->hasEnoughQuestions()) {
                    $validator->errors()->add(
                        'category_ids',
                        "الفئة \"{$category->name}\" لا تحتوي على أسئلة كافية"
                    );
                }
            }
        });
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