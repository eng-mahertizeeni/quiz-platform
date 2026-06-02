@extends('App')
@section('title', 'إضافة سؤال')
@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card-custom p-4">
                <h4 class="fw-bold mb-3"><i class="fas fa-plus-circle text-success me-2"></i>إضافة سؤال جديد</h4>
                <p class="text-muted">شارك بأسئلتك مع المجتمع. سيتم مراجعة السؤال من قبل الإدارة قبل نشره.</p>
                <form method="POST" action="{{ route('questions.store') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label fw-bold">الفئة</label>
                        <select name="category_id" class="form-select" required>
                            <option value="">اختر الفئة</option>
                            @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">نص السؤال</label>
                        <textarea name="question_text" class="form-control" rows="3" required>{{ old('question_text') }}</textarea>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">الإجابة أ</label>
                            <input type="text" name="answer_a" class="form-control" value="{{ old('answer_a') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">الإجابة ب</label>
                            <input type="text" name="answer_b" class="form-control" value="{{ old('answer_b') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">الإجابة ج</label>
                            <input type="text" name="answer_c" class="form-control" value="{{ old('answer_c') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">الإجابة د</label>
                            <input type="text" name="answer_d" class="form-control" value="{{ old('answer_d') }}" required>
                        </div>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label fw-bold">الإجابة الصحيحة</label>
                            <select name="correct_answer" class="form-select" required>
                                <option value="a" {{ old('correct_answer')=='a' ? 'selected' : '' }}>أ</option>
                                <option value="b" {{ old('correct_answer')=='b' ? 'selected' : '' }}>ب</option>
                                <option value="c" {{ old('correct_answer')=='c' ? 'selected' : '' }}>ج</option>
                                <option value="d" {{ old('correct_answer')=='d' ? 'selected' : '' }}>د</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">مستوى الصعوبة</label>
                            <select name="difficulty" class="form-select" required>
                                <option value="medium" {{ old('difficulty')=='medium' ? 'selected' : '' }}>متوسط (250 نقطة)</option>
                                <option value="hard" {{ old('difficulty')=='hard' ? 'selected' : '' }}>صعب (500 نقطة)</option>
                                <option value="very_hard" {{ old('difficulty')=='very_hard' ? 'selected' : '' }}>صعب جداً (750 نقطة)</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">صورة (اختياري)</label>
                            <input type="file" name="image" class="form-control" accept="image/*">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary-custom">إرسال السؤال</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
