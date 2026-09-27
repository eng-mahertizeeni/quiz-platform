@extends('Admin')
@section('title', 'تعديل سؤال')
@section('content')
<div class="card-admin p-4">
    <h5 class="fw-bold mb-3"><i class="fas fa-edit text-warning me-2"></i>تعديل السؤال</h5>
    <form method="POST" action="{{ route('admin.questions.update', $question) }}" enctype="multipart/form-data">
        @csrf @method('PUT')
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label fw-bold">الفئة</label>
                <select name="category_id" class="form-select" required>
                    @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" {{ old('category_id', $question->category_id) == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-bold">مستوى الصعوبة</label>
                <select name="difficulty" class="form-select" required>
                    <option value="easy" {{ old('difficulty', $question->difficulty)=='easy' ? 'selected' : '' }}>سهل (200)</option>
                    <option value="medium" {{ old('difficulty', $question->difficulty)=='medium' ? 'selected' : '' }}>متوسط (400)</option>
                    <option value="hard" {{ old('difficulty', $question->difficulty)=='hard' ? 'selected' : '' }}>صعب (600)</option>
                </select>
            </div>
            <div class="col-12">
                <label class="form-label fw-bold">نص السؤال</label>
                <textarea name="question_text" class="form-control" rows="3" required>{{ old('question_text', $question->question_text) }}</textarea>
            </div>
            <div class="col-12">
                <label class="form-label fw-bold">نص السؤال (عربي - اختياري)</label>
                <textarea name="question_text_ar" class="form-control" rows="2">{{ old('question_text_ar', $question->question_text_ar) }}</textarea>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-bold">الإجابة أ</label>
                <input type="text" name="answer_a" class="form-control" value="{{ old('answer_a', $question->answer_a) }}" required>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-bold">الإجابة ب</label>
                <input type="text" name="answer_b" class="form-control" value="{{ old('answer_b', $question->answer_b) }}" required>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-bold">الإجابة ج</label>
                <input type="text" name="answer_c" class="form-control" value="{{ old('answer_c', $question->answer_c) }}" required>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-bold">الإجابة د</label>
                <input type="text" name="answer_d" class="form-control" value="{{ old('answer_d', $question->answer_d) }}" required>
            </div>
            <div class="col-md-4">
                <label class="form-label fw-bold">الإجابة الصحيحة</label>
                <select name="correct_answer" class="form-select" required>
                    <option value="a" {{ old('correct_answer', $question->correct_answer)=='a' ? 'selected' : '' }}>أ</option>
                    <option value="b" {{ old('correct_answer', $question->correct_answer)=='b' ? 'selected' : '' }}>ب</option>
                    <option value="c" {{ old('correct_answer', $question->correct_answer)=='c' ? 'selected' : '' }}>ج</option>
                    <option value="d" {{ old('correct_answer', $question->correct_answer)=='d' ? 'selected' : '' }}>د</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label fw-bold">الصورة</label>
                <input type="file" name="image" class="form-control" accept="image/*">
                @if($question->image)<small class="text-muted d-block">موجودة حالياً</small>@endif
            </div>
            <div class="col-md-4">
                <label class="form-label fw-bold">الحالة</label>
                <select name="status" class="form-select">
                    <option value="active" {{ old('status', $question->status)=='active' ? 'selected' : '' }}>نشط</option>
                    <option value="inactive" {{ old('status', $question->status)=='inactive' ? 'selected' : '' }}>غير نشط</option>
                </select>
            </div>
        </div>
        <button type="submit" class="btn btn-primary-custom mt-3">تحديث</button>
    </form>
</div>
@endsection
