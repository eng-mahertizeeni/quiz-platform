@extends('App')
@section('title', 'المتصدرين')
@section('content')
<div class="container py-4">
    <h4 class="fw-bold mb-4"><i class="fas fa-trophy text-warning me-2"></i>المتصدرين</h4>

    <div class="row g-4">
        <div class="col-md-8">
            <div class="card-custom p-3">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>

                                <th>المستخدم</th>
                                <th>النقاط</th>
                                <th>الألعاب</th>
                                <th>الفوز</th>
                                <th>نسبة الفوز</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($users as $i => $u)
                            <tr>
                                <td><span class="badge bg-warning text-dark">{{ $i + 1 }}</span></td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <img src="{{ $u->avatar_url }}" alt="" class="rounded-circle" width="32" height="32">
                                        <strong>{{ $u->name }}</strong>
                                    </div>
                                </td>
                                <td class="fw-bold text-warning">{{ number_format($u->total_score) }}</td>
                                <td>{{ $u->games_played }}</td>
                                <td>{{ $u->games_won }}</td>
                                <td>{{ $u->win_rate }}%</td>
                            </tr>
                            @empty
                            <tr><td colspan="6" class="text-center text-muted">لا يوجد متصدرون بعد</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card-custom p-3">
                <h5 class="fw-bold mb-3"><i class="fas fa-tags text-info me-2"></i>إحصاءات الفئات</h5>
                @forelse($categories as $cat)
                <div class="d-flex justify-content-between align-items-center py-2 border-bottom" style="border-color:var(--border)">
                    <span><i class="fas fa-{{ $cat->icon ?: 'tag' }} me-2" style="color:{{ $cat->color }}"></i>{{ $cat->name }}</span>
                    <span class="badge" style="background:{{ $cat->color }}22;color:{{ $cat->color }}">{{ $cat->questions_count }} سؤال</span>
                </div>
                @empty
                <p class="text-muted small">لا توجد فئات</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
