@extends('Admin')
@section('title', 'إضافة سؤال')
@section('content')
<div class="card-admin p-4">
    <h5 class="fw-bold mb-3"><i class="fas fa-plus-circle text-success me-2"></i>إضافة سؤال جديد</h5>
    <form method="POST" action="{{ route('admin.questions.store') }}" enctype="multipart/form-data">
        @csrf
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label fw-bold">الفئة</label>
                <select name="category_id" class="form-select" required>
                    <option value="">اختر الفئة</option>
                    @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-bold">مستوى الصعوبة</label>
                <select name="difficulty" class="form-select" required>
                    <option value="medium" {{ old('difficulty')=='medium' ? 'selected' : '' }}>متوسط (250)</option>
                    <option value="hard" {{ old('difficulty')=='hard' ? 'selected' : '' }}>صعب (500)</option>
                    <option value="very_hard" {{ old('difficulty')=='very_hard' ? 'selected' : '' }}>صعب جداً (750)</option>
                </select>
            </div>
            <div class="col-12">
                <label class="form-label fw-bold">نص السؤال</label>
                <textarea name="question_text" class="form-control" rows="3" required>{{ old('question_text') }}</textarea>
            </div>
            <div class="col-12">
                <label class="form-label fw-bold">نص السؤال (عربي - اختياري)</label>
                <textarea name="question_text_ar" class="form-control" rows="2">{{ old('question_text_ar') }}</textarea>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-bold">الإجابة أ</label>
                <input type="text" name="answer_a" class="form-control" value="{{ old('answer_a') }}" required>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-bold">الإجابة ب</label>
                <input type="text" name="answer_b" class="form-control" value="{{ old('answer_b') }}" required>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-bold">الإجابة ج</label>
                <input type="text" name="answer_c" class="form-control" value="{{ old('answer_c') }}" required>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-bold">الإجابة د</label>
                <input type="text" name="answer_d" class="form-control" value="{{ old('answer_d') }}" required>
            </div>
            <div class="col-md-4">
                <label class="form-label fw-bold">الإجابة الصحيحة</label>
                <select name="correct_answer" class="form-select" required>
                    <option value="a">أ</option>
                    <option value="b">ب</option>
                    <option value="c">ج</option>
                    <option value="d">د</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label fw-bold">الصورة</label>
                <input type="file" name="image" class="form-control" accept="image/*">
            </div>
            <div class="col-md-4">
                <label class="form-label fw-bold">الحالة</label>
                <select name="status" class="form-select">
                    <option value="active">نشط</option>
                    <option value="inactive">غير نشط</option>
                </select>
            </div>
        </div>
        <button type="submit" class="btn btn-primary-custom mt-3">حفظ</button>
    </form>
</div>
@endsection
