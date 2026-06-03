@extends('App')

@section('title', 'النتائج - Face Off')
@section('content')
<div class="page-header">
    <div class="container text-center">
        <h1 class="mb-0"><i class="fas fa-trophy text-warning me-2"></i>نتائج اللعبة - Face Off</h1>
        <p class="text-muted mb-0">كود اللعبة: <code class="text-warning">{{ $game->code }}</code></p>
    </div>
</div>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-6">
            @if($winner)
            <div class="card-custom p-5 text-center mb-4">
                <div class="mb-3">
                    <i class="fas fa-crown text-warning" style="font-size:4rem;"></i>
                </div>
                <h2 class="fw-bold">{{ $winner['player_name'] }}</h2>
                <p class="text-muted">الفائز باللعبة</p>
                <h1 class="display-4 fw-bold text-warning">{{ $winner['total_score'] }}</h1>
                <p class="text-muted">نقطة</p>
            </div>
            @endif

            <div class="card-custom p-4">
                <h4 class="mb-3"><i class="fas fa-list-ol me-2 text-info"></i>الترتيب النهائي</h4>
                @foreach($results as $i => $r)
                <div class="d-flex align-items-center justify-content-between p-3 rounded-3 mb-2
                    {{ $i === 0 ? 'bg-warning text-dark' : 'bg-dark' }}"
                    style="border:1px solid var(--border);">
                    <div class="d-flex align-items-center gap-3">
                        <span class="fw-bold fs-5">{{ $i + 1 }}</span>
                        <img src="{{ $r['player_avatar'] }}" alt="" class="rounded-circle" width="40" height="40">
                        <span class="fw-bold">{{ $r['player_name'] }}</span>
                    </div>
                    <span class="fw-bold fs-5">{{ $r['total_score'] }}</span>
                </div>
                @endforeach
            </div>

            <div class="d-flex gap-3 mt-4">
                <a href="{{ route('bluff.index') }}" class="btn btn-primary-custom flex-fill">
                    <i class="fas fa-redo me-1"></i>لعبة جديدة
                </a>
                <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary flex-fill">
                    <i class="fas fa-tachometer-alt me-1"></i>الرجوع للوحة
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
