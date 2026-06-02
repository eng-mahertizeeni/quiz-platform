@extends('App')
@section('title', 'الممارسة')
@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold"><i class="fas fa-book-open text-warning me-2"></i>الممارسة والتدريب</h4>
        <a href="{{ route('game.create') }}" class="btn btn-primary-custom"><i class="fas fa-play me-1"></i>لعبة جديدة</a>
    </div>

    <div class="alert alert-info border-0 rounded-3 bg-opacity-10 d-flex align-items-center gap-3" style="background:rgba(59,130,246,0.1)">
        <i class="fas fa-lightbulb text-info fa-2x"></i>
        <div>
            <strong class="text-info">تدرب على الأسئلة</strong>
            <p class="mb-0 text-muted small">اختر فئة للتدرب عليها. كل سؤال تجيب عليه لن يظهر لك مرة أخرى. ستظهر نسبة تقدمك لكل فئة.</p>
        </div>
    </div>

    <div class="row g-3">
        @forelse($categories as $cat)
        <div class="col-md-4 col-6">
            <a href="{{ route('practice.quiz', $cat->slug) }}" class="text-decoration-none">
                <div class="card-custom p-4 text-center h-100">
                    <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width:60px;height:60px;background:{{ $cat->color }}22;color:{{ $cat->color }}">
                        <i class="fas fa-{{ $cat->icon ?: 'tag' }} fa-2x"></i>
                    </div>
                    <h6 class="fw-bold mb-2 text-light">{{ $cat->name }}</h6>
                    <div class="d-flex justify-content-center gap-3 mb-2">
                        <small class="text-muted">{{ $cat->questions_count }} سؤال</small>
                        @if($cat->answered_count > 0)
                        <small class="text-success">{{ $cat->answered_count }} تم</small>
                        @endif
                    </div>
                    <div class="progress" style="height:8px;background:var(--border)">
                        <div class="progress-bar" role="progressbar"
                             style="width:{{ $cat->progress }}%;background:{{ $cat->color }}"
                             aria-valuenow="{{ $cat->progress }}" aria-valuemin="0" aria-valuemax="100">
                        </div>
                    </div>
                    <small class="d-block mt-1" style="color:{{ $cat->color }}">{{ $cat->progress }}%</small>
                </div>
            </a>
        </div>
        @empty
        <div class="col-12 text-center py-5">
            <i class="fas fa-folder-open text-muted fa-3x mb-3"></i>
            <p class="text-muted">لا توجد فئات متاحة حالياً</p>
        </div>
        @endforelse
    </div>
</div>
@endsection
