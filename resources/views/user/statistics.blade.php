@extends('App')
@section('title', 'الإحصاءات')
@section('content')
<div class="container py-4">
    <h4 class="fw-bold mb-4"><i class="fas fa-chart-bar text-warning me-2"></i>الإحصاءات</h4>

    <div class="row g-4">
        <div class="col-md-6">
            <div class="card-custom p-3">
                <h5 class="fw-bold mb-3"><i class="fas fa-fire text-danger me-2"></i>الفئات الأكثر لعباً</h5>
                @forelse($mostPlayedCategories as $cat)
                <div class="d-flex justify-content-between align-items-center py-2">
                    <span><i class="fas fa-{{ $cat->icon ?: 'tag' }} me-2" style="color:{{ $cat->color }}"></i>{{ $cat->name }}</span>
                    <span class="text-muted">{{ $cat->times_played }} لعبة</span>
                </div>
                @empty
                <p class="text-muted">لا توجد إحصاءات بعد</p>
                @endforelse
            </div>
        </div>
        <div class="col-md-6">
            <div class="card-custom p-3">
                <h5 class="fw-bold mb-3"><i class="fas fa-trophy text-warning me-2"></i>أفضل اللاعبين</h5>
                @forelse($leaderboard as $i => $u)
                <div class="d-flex align-items-center gap-2 py-1">
                    <span class="badge bg-warning text-dark">{{ $i + 1 }}</span>
                    <img src="{{ $u->avatar_url }}" alt="" class="rounded-circle" width="28" height="28">
                    <strong class="small">{{ $u->name }}</strong>
                    <span class="ms-auto text-muted small">{{ number_format($u->total_score) }} نقطة</span>
                </div>
                @empty
                <p class="text-muted">لا يوجد متصدرون بعد</p>
                @endforelse
            </div>
        </div>
        <div class="col-12">
            <div class="card-custom p-3">
                <h5 class="fw-bold mb-3"><i class="fas fa-question-circle text-info me-2"></i>أصعب الأسئلة</h5>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>السؤال</th>
                                <th>الفئة</th>
                                <th>الصعوبة</th>
                                <th>نسبة النجاح</th>
                                <th>المحاولات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($hardestQuestions as $q)
                            <tr>
                                <td class="small">{{ Str::limit($q->question_text, 50) }}</td>
                                <td>{{ $q->category->name ?? '--' }}</td>
                                <td><span class="badge badge-points-{{ $q->points }}">{{ $q->difficulty_label }}</span></td>
                                <td>{{ $q->success_rate }}%</td>
                                <td>{{ $q->times_used }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="5" class="text-center text-muted">لا توجد إحصاءات كافية</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
