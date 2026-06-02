@extends('App')
@section('title', 'غرفة اللعبة')
@section('content')
<div class="container py-4 text-center">
    <h4 class="fw-bold mb-3"><i class="fas fa-users text-warning me-2"></i>غرفة اللعبة</h4>

    <div class="card-custom p-4 mb-4 d-inline-block">
        <small class="text-muted">رمز اللعبة</small>
        <h2 class="fw-900 text-warning" style="font-size:3rem;letter-spacing:8px" dir="ltr">{{ $session->code }}</h2>
    </div>

    <div class="row g-4 justify-content-center mb-4">
        @foreach($session->teams as $team)
        <div class="col-md-5">
            <div class="card-custom p-4" style="border-top:4px solid {{ $team->color }}">
                <h5 class="fw-bold">{{ $team->name }}</h5>
                <div style="width:40px;height:40px;border-radius:50%;background:{{ $team->color }};margin:0 auto"></div>
            </div>
        </div>
        @endforeach
    </div>

    <div class="mb-4">
        <h6 class="fw-bold mb-2">الفئات المختارة:</h6>
        <div class="d-flex flex-wrap gap-2 justify-content-center">
            @foreach($session->categories as $cat)
            <span class="badge" style="background:{{ $cat->color }}22;color:{{ $cat->color }};padding:8px 16px">
                <i class="fas fa-{{ $cat->icon ?: 'tag' }} me-1"></i>{{ $cat->name }}
            </span>
            @endforeach
        </div>
    </div>

    <form method="POST" action="{{ route('game.start', $session->code) }}">
        @csrf
        <button type="submit" class="btn btn-success btn-lg px-5">
            <i class="fas fa-play me-2"></i>بدء اللعبة
        </button>
    </form>
</div>
@endsection
