@extends('App')
@section('title', 'لوحتي')
@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold"><i class="fas fa-tachometer-alt text-warning me-2"></i>لوحتي</h4>
        <a href="{{ route('game.create') }}" class="btn btn-primary-custom"><i class="fas fa-play me-1"></i>لعبة جديدة</a>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3 col-6">
            <div class="stat-card text-center">
                <i class="fas fa-gamepad text-info fa-2x mb-2"></i>
                <h5 class="fw-bold mb-0">{{ $stats['games_played'] }}</h5>
                <small class="text-muted">الألعاب</small>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="stat-card text-center">
                <i class="fas fa-trophy text-warning fa-2x mb-2"></i>
                <h5 class="fw-bold mb-0">{{ $stats['games_won'] }}</h5>
                <small class="text-muted">الفوز</small>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="stat-card text-center">
                <i class="fas fa-chart-line text-success fa-2x mb-2"></i>
                <h5 class="fw-bold mb-0">{{ $stats['win_rate'] }}%</h5>
                <small class="text-muted">نسبة الفوز</small>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="stat-card text-center">
                <i class="fas fa-star text-danger fa-2x mb-2"></i>
                <h5 class="fw-bold mb-0">{{ $stats['total_score'] }}</h5>
                <small class="text-muted">النقاط</small>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-md-6">
            <div class="card-custom p-3">
                <h5 class="fw-bold mb-3"><i class="fas fa-check-circle text-success me-2"></i>الأسئلة المقترحة</h5>
                <div class="d-flex justify-content-around text-center">
                    <div>
                        <h4 class="fw-bold text-primary mb-0">{{ $stats['submitted_questions'] }}</h4>
                        <small class="text-muted">مقترحة</small>
                    </div>
                    <div>
                        <h4 class="fw-bold text-success mb-0">{{ $stats['approved_questions'] }}</h4>
                        <small class="text-muted">مقبولة</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card-custom p-3">
                <h5 class="fw-bold mb-3"><i class="fas fa-clock text-info me-2"></i>آخر الألعاب</h5>
                @forelse($stats['recent_games'] as $g)
                <div class="d-flex justify-content-between align-items-center py-1">
                    <span>{{ $g->teams->first()?->name ?? '--' }} vs {{ $g->teams->last()?->name ?? '--' }}</span>
                    <span class="{{ $g->scores->where('user_id', auth()->id())->first()?->is_winner ? 'text-success' : 'text-muted' }}">{{ $g->winner?->score ?? 0 }}</span>
                </div>
                @empty
                <p class="text-muted mb-0 small">لا توجد ألعاب سابقة</p>
                @endforelse
            </div>
        </div>
        @if($activeGames->isNotEmpty())
        <div class="col-12">
            <div class="card-custom p-3">
                <h5 class="fw-bold mb-3"><i class="fas fa-play-circle text-success me-2"></i>الألعاب النشطة</h5>
                @foreach($activeGames as $g)
                <div class="d-flex justify-content-between align-items-center py-2">
                    <div>
                        <strong>{{ $g->teams->first()?->name ?? '?' }}</strong> vs <strong>{{ $g->teams->last()?->name ?? '?' }}</strong>
                        <span class="badge bg-warning text-dark me-2">{{ $g->code }}</span>
                    </div>
                    <a href="{{ $g->status === 'waiting' ? route('game.lobby', $g->code) : route('game.board', $g->code) }}" class="btn btn-sm btn-outline-warning">دخول</a>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>

</div>
@endsection
