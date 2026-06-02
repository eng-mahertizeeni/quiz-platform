<?php

namespace App\Http\Requests\Game;

use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Http\FormRequest;

class CreateGameRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::check();
    }

    public function rules(): array
    {
        return [
            'team_one_name' => 'required|string|max:50',
            'team_two_name' => 'required|string|max:50|different:team_one_name',
            'team_one_color' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'team_two_color' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'timer_seconds' => 'nullable|integer|min:10|max:120',
            'sound_enabled' => 'nullable|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'team_one_name.required' => 'اسم الفريق الأول مطلوب',
            'team_two_name.required' => 'اسم الفريق الثاني مطلوب',
            'team_two_name.different' => 'يجب أن يكون اسم الفريقين مختلفاً',
        ];
    }
}