@extends('Admin')
@section('title', 'تفاصيل المستخدم')
@section('content')
<div class="row g-4">
    <div class="col-md-4">
        <div class="card-admin p-4 text-center">
            <img src="{{ $user->avatar_url }}" alt="" class="rounded-circle mb-3" width="100" height="100" style="border:3px solid var(--primary)">
            <h5 class="fw-bold">{{ $user->name }}</h5>
            <span class="text-muted">@ {{ $user->username }}</span>
            <div class="mt-2">
                {!! $user->isAdmin() ? '<span class="badge bg-warning text-dark">مسؤول</span>' : '<span class="badge bg-info">مستخدم</span>' !!}
                {!! $user->is_active ? '<span class="badge bg-success">نشط</span>' : '<span class="badge bg-danger">معطل</span>' !!}
            </div>
            <hr style="border-color:var(--border)">
            <div class="row text-center">
                <div class="col-4">
                    <h6 class="fw-bold text-warning mb-0">{{ number_format($user->total_score) }}</h6>
                    <small class="text-muted">نقاط</small>
                </div>
                <div class="col-4">
                    <h6 class="fw-bold mb-0">{{ $user->games_played }}</h6>
                    <small class="text-muted">ألعاب</small>
                </div>
                <div class="col-4">
                    <h6 class="fw-bold text-success mb-0">{{ $user->win_rate }}%</h6>
                    <small class="text-muted">فوز</small>
                </div>
            </div>
            <p class="text-muted small mt-3 mb-0">{{ $user->email }}</p>
        </div>
    </div>
    <div class="col-md-8">
        <div class="card-admin p-3 mb-3">
            <h6 class="fw-bold mb-3"><i class="fas fa-gamepad text-info me-2"></i>آخر الألعاب</h6>
            @forelse($user->gameSessions->sortByDesc('created_at')->take(5) as $s)
            <div class="d-flex justify-content-between py-1">
                <span>{{ $s->teams->first()?->name ?? '?' }} vs {{ $s->teams->last()?->name ?? '?' }}</span>
                <span class="badge bg-{{ $s->status === 'finished' ? 'success' : 'warning' }}">{{ $s->status }}</span>
            </div>
            @empty
            <p class="text-muted small">لا توجد ألعاب</p>
            @endforelse
        </div>
        <div class="card-admin p-3">
            <h6 class="fw-bold mb-3"><i class="fas fa-question-circle text-warning me-2"></i>الأسئلة المقترحة</h6>
            @forelse($user->submittedQuestions->sortByDesc('created_at') as $q)
            <div class="d-flex justify-content-between py-1">
                <span class="small">{{ Str::limit($q->question_text, 40) }}</span>
                <span>
                    @if($q->isPending()) <span class="badge bg-warning text-dark">قيد المراجعة</span>
                    @elseif($q->isApproved()) <span class="badge bg-success">مقبول</span>
                    @else <span class="badge bg-danger">مرفوض</span>
                    @endif
                </span>
            </div>
            @empty
            <p class="text-muted small">لا توجد أسئلة مقترحة</p>
            @endforelse
        </div>
    </div>
</div>
@endsection
