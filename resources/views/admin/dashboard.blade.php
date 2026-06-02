@extends('Admin')
@section('title', 'لوحة التحكم')
@section('content')
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="stat-card d-flex align-items-center gap-3">
            <div class="stat-icon" style="background:rgba(59,130,246,0.15);color:#3B82F6"><i class="fas fa-users"></i></div>
            <div><h5 class="fw-bold mb-0">{{ $stats['total_users'] }}</h5><small class="text-muted">المستخدمون</small></div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card d-flex align-items-center gap-3">
            <div class="stat-icon" style="background:rgba(16,185,129,0.15);color:#10B981"><i class="fas fa-question-circle"></i></div>
            <div><h5 class="fw-bold mb-0">{{ $stats['total_questions'] }}</h5><small class="text-muted">الأسئلة</small></div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card d-flex align-items-center gap-3">
            <div class="stat-icon" style="background:rgba(245,158,11,0.15);color:#F59E0B"><i class="fas fa-tags"></i></div>
            <div><h5 class="fw-bold mb-0">{{ $stats['total_categories'] }}</h5><small class="text-muted">الفئات</small></div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card d-flex align-items-center gap-3">
            <div class="stat-icon" style="background:rgba(239,68,68,0.15);color:#EF4444"><i class="fas fa-gamepad"></i></div>
            <div><h5 class="fw-bold mb-0">{{ $stats['total_games'] }}</h5><small class="text-muted">الألعاب</small></div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card d-flex align-items-center gap-3">
            <div class="stat-icon" style="background:rgba(168,85,247,0.15);color:#A855F7"><i class="fas fa-clock"></i></div>
            <div><h5 class="fw-bold mb-0">{{ $stats['games_today'] }}</h5><small class="text-muted">ألعاب اليوم</small></div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card d-flex align-items-center gap-3">
            <div class="stat-icon" style="background:rgba(239,68,68,0.15);color:#EF4444"><i class="fas fa-inbox"></i></div>
            <div><h5 class="fw-bold mb-0">{{ $stats['pending_submissions'] }}</h5><small class="text-muted">بانتظار المراجعة</small></div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card d-flex align-items-center gap-3">
            <div class="stat-icon" style="background:rgba(16,185,129,0.15);color:#10B981"><i class="fas fa-play-circle"></i></div>
            <div><h5 class="fw-bold mb-0">{{ $stats['active_games'] }}</h5><small class="text-muted">ألعاب نشطة</small></div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card d-flex align-items-center gap-3">
            <div class="stat-icon" style="background:rgba(59,130,246,0.15);color:#3B82F6"><i class="fas fa-user-plus"></i></div>
            <div><h5 class="fw-bold mb-0">{{ $stats['new_users_week'] }}</h5><small class="text-muted">مستخدمون جدد هذا الأسبوع</small></div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-md-6">
        <div class="card-admin p-3">
            <h5 class="fw-bold mb-3"><i class="fas fa-trophy text-warning me-2"></i>المتصدرين</h5>
            @forelse($leaderboard as $i => $u)
            <div class="d-flex align-items-center gap-2 py-1">
                <span class="badge bg-warning text-dark">{{ $i + 1 }}</span>
                <img src="{{ $u->avatar_url }}" alt="" class="rounded-circle" width="28" height="28">
                <strong class="small">{{ $u->name }}</strong>
                <span class="ms-auto text-muted small">{{ number_format($u->total_score) }} نقطة</span>
            </div>
            @empty
            <p class="text-muted small">لا يوجد متصدرون</p>
            @endforelse
        </div>
    </div>
    <div class="col-md-6">
        <div class="card-admin p-3">
            <h5 class="fw-bold mb-3"><i class="fas fa-question-circle text-info me-2"></i>أصعب الأسئلة</h5>
            @forelse($hardestQuestions as $q)
            <div class="d-flex justify-content-between py-1">
                <span class="small">{{ Str::limit($q->question_text, 40) }}</span>
                <span class="badge badge-points-{{ $q->points }}">{{ $q->success_rate }}%</span>
            </div>
            @empty
            <p class="text-muted small">لا توجد بيانات كافية</p>
            @endforelse
        </div>
    </div>
    <div class="col-12">
        <div class="card-admin p-3">
            <h5 class="fw-bold mb-3"><i class="fas fa-tags text-success me-2"></i>إحصاءات الفئات</h5>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>الفئة</th>
                            <th>الأيقونة</th>
                            <th>الأسئلة</th>
                            <th>مرات اللعب</th>
                            <th>الحالة</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($categories as $cat)
                        <tr>
                            <td><i class="fas fa-{{ $cat->icon ?: 'tag' }} me-2" style="color:{{ $cat->color }}"></i>{{ $cat->name }}</td>
                            <td>{{ $cat->icon }}</td>
                            <td>{{ $cat->questions_count }}</td>
                            <td>{{ $cat->times_played }}</td>
                            <td>{!! $cat->is_active ? '<span class="badge bg-success">نشط</span>' : '<span class="badge bg-secondary">غير نشط</span>' !!}</td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="text-center text-muted">لا توجد فئات</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
