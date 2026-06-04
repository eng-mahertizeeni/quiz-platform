@extends('App')
@section('title', 'النتائج - كرويات')
@section('content')
<div class="container">
    <div class="row justify-content-center mt-5">
        <div class="col-md-6">
            <div class="card-custom p-5 text-center">
                <div style="font-size:4rem; line-height:1; margin-bottom:1rem;">🏆</div>
                <h2 class="mb-4">النتائج النهائية</h2>

                <div class="mb-4">
                    <div class="display-6 fw-bold text-warning mb-1">{{ $scores[0]['name'] ?? '—' }}</div>
                    <p class="text-muted">{{ $scores[0]['score'] ?? 0 }} نقطة</p>
                </div>

                <div class="list-group">
                    @foreach($scores as $i => $s)
                    <div class="list-group-item d-flex justify-content-between align-items-center py-3"
                         style="background:var(--bg-card); border-color:var(--border); color:var(--text-primary);">
                        <div>
                            <span class="fw-bold ms-2">{{ $i + 1 }}.</span>
                            {{ $s['name'] }}
                            @if($i === 0) <span class="text-warning ms-1">👑</span> @endif
                        </div>
                        <span class="badge bg-warning text-dark fs-6">{{ $s['score'] }} نقطة</span>
                    </div>
                    @endforeach
                </div>

                <div class="mt-4 d-flex gap-2 justify-content-center">
                    <a href="{{ route('kuraiyat.index') }}" class="btn btn-primary-custom">
                        <i class="fas fa-redo me-1"></i>العب مرة أخرى
                    </a>
                    <a href="{{ route('home') }}" class="btn btn-outline-light">
                        <i class="fas fa-home me-1"></i>الرئيسية
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
