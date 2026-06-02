@extends('App')
@section('title', 'نتيجة اللعبة')
@section('content')
<div class="container py-5 text-center">
    <h4 class="fw-bold mb-4"><i class="fas fa-flag-checkered text-warning me-2"></i>انتهت اللعبة!</h4>

    @if($winner)
    <div class="card-custom p-5 mb-4 d-inline-block" style="border:2px solid {{ $winner->color }}">
        <i class="fas fa-trophy text-warning" style="font-size:4rem"></i>
        <h2 class="fw-bold mt-3" style="color:{{ $winner->color }}">{{ $winner->name }}</h2>
        <h5 class="text-muted">الفائز</h5>
        <div class="fw-bold text-warning" style="font-size:2rem">{{ $winner->score }} نقطة</div>
    </div>
    @endif

    <div class="row g-4 justify-content-center mb-4">
        @foreach($session->teams->sortByDesc('score') as $team)
        <div class="col-md-5">
            <div class="card-custom p-4" style="border-top:4px solid {{ $team->color }}">
                <h5 class="fw-bold" style="color:{{ $team->color }}">{{ $team->name }}</h5>
                <div class="fw-bold" style="font-size:2rem">{{ $team->score }}</div>
                <small class="text-muted">{{ $team->correct_answers }} صحيح / {{ $team->wrong_answers }} خطأ</small>
            </div>
        </div>
        @endforeach
    </div>

    <div class="d-flex gap-3 justify-content-center">
        <a href="{{ route('game.create') }}" class="btn btn-primary-custom btn-lg">
            <i class="fas fa-play me-2"></i>لعبة جديدة
        </a>
        <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary btn-lg">
            <i class="fas fa-tachometer-alt me-2"></i>العودة للوحة
        </a>
    </div>
</div>
@endsection
