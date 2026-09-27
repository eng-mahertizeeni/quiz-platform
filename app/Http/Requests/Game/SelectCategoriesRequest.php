<?php

namespace App\Http\Requests\Game;

use App\Models\Category;
use App\Models\GameSession;
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
            'category_ids' => 'required|array|size:' . GameSession::CATEGORIES_COUNT,
            'category_ids.*' => 'required|distinct|exists:categories,id',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $ids = (array) $this->input('category_ids', []);
            $notReady = Category::whereIn('id', $ids)->get()->reject->hasEnoughQuestions();

            if ($notReady->isNotEmpty()) {
                $validator->errors()->add(
                    'category_ids',
                    'لا توجد أسئلة كافية في: ' . $notReady->pluck('name')->implode('، ')
                );
            }
        });
    }

    public function messages(): array
    {
        return [
            'category_ids.required' => 'يجب اختيار الفئات',
            'category_ids.size' => 'يجب اختيار ' . GameSession::CATEGORIES_COUNT . ' فئات بالضبط',
            'category_ids.*.distinct' => 'لا يمكن اختيار نفس الفئة مرتين',
            'category_ids.*.exists' => 'إحدى الفئات المختارة غير موجودة',
        ];
    }
}