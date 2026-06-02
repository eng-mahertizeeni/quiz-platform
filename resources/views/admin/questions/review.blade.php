@extends('Admin')
@section('title', 'مراجعة سؤال')
@section('content')
<div class="row g-4">
    <div class="col-md-8">
        <div class="card-admin p-4">
            <h5 class="fw-bold mb-3"><i class="fas fa-search text-info me-2"></i>مراجعة السؤال المقترح</h5>
            <div class="mb-3">
                <label class="form-label fw-bold text-muted small">نص السؤال</label>
                <p class="fw-bold">{{ $submission->question_text }}</p>
            </div>
            <div class="row g-3 mb-3">
                @foreach(['a','b','c','d'] as $letter)
                <div class="col-md-6">
                    <div class="p-3 rounded" style="background:var(--bg-dark);border:1px solid var(--border)">
                        <strong>{{ strtoupper($letter) }}:</strong>
                        {{ $submission->{'answer_'.$letter} }}
                        @if($submission->correct_answer === $letter)
                        <span class="badge bg-success ms-2"><i class="fas fa-check"></i> الصحيحة</span>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
            <div class="d-flex gap-2 mb-3">
                <span class="badge badge-points-{{ $submission->difficulty === 'medium' ? 250 : ($submission->difficulty === 'hard' ? 500 : 750) }}">{{ $submission->difficulty_label }}</span>
                <span class="badge" style="background:{{ $submission->category->color ?? '#666' }}22;color:{{ $submission->category->color ?? '#666' }}">{{ $submission->category->name ?? '--' }}</span>
            </div>
            @if($submission->image)
            <div class="mb-3">
                <img src="{{ Storage::url($submission->image) }}" class="img-fluid rounded" style="max-height:200px">
            </div>
            @endif
        </div>
    </div>
    <div class="col-md-4">
        <div class="card-admin p-4 mb-3">
            <h6 class="fw-bold mb-3">معلومات المقدم</h6>
            <div class="d-flex align-items-center gap-2 mb-2">
                <img src="{{ $submission->user->avatar_url ?? '' }}" alt="" class="rounded-circle" width="36" height="36">
                <div>
                    <strong>{{ $submission->user->name ?? '--' }}</strong>
                    <small class="d-block text-muted">{{ $submission->user->email ?? '' }}</small>
                </div>
            </div>
        </div>

        @if($submission->isPending())
        <div class="card-admin p-4">
            <h6 class="fw-bold mb-3">الإجراء</h6>
            <form method="POST" action="{{ route('admin.questions.approve', $submission) }}" class="mb-2">
                @csrf
                <button type="submit" class="btn btn-success w-100"><i class="fas fa-check me-1"></i>قبول السؤال</button>
            </form>
            <form method="POST" action="{{ route('admin.questions.reject', $submission) }}">
                @csrf
                <div class="mb-2">
                    <label class="form-label fw-bold small">سبب الرفض</label>
                    <textarea name="reason" class="form-control" rows="3" required placeholder="ذكر سبب الرفض"></textarea>
                </div>
                <button type="submit" class="btn btn-danger w-100" onclick="return confirm('تأكيد رفض السؤال؟')"><i class="fas fa-times me-1"></i>رفض السؤال</button>
            </form>
        </div>
        @else
        <div class="card-admin p-4">
            @if($submission->isApproved())
            <div class="alert alert-success border-0 rounded-3">تم قبول هذا السؤال ونشره.</div>
            @else
            <div class="alert alert-danger border-0 rounded-3">
                تم رفض هذا السؤال.
                @if($submission->rejection_reason)
                <hr><strong>السبب:</strong> {{ $submission->rejection_reason }}
                @endif
            </div>
            @endif
            <a href="{{ route('admin.questions.submissions') }}" class="btn btn-outline-secondary w-100">العودة</a>
        </div>
        @endif
    </div>
</div>
@endsection
