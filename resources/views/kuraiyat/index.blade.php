@extends('App')
@section('title', 'أنا مين - كرويات')
@section('content')
<div class="page-header">
    <div class="container">
        <div class="d-flex align-items-center gap-3">
            <span style="font-size:2.5rem;">🕵️</span>
            <div>
                <h1 class="mb-0">أنا مين؟</h1>
                <p class="text-muted mb-0">فقرة من صباحو تحدي — خمّن اللاعب أو النادي من 5 أدلة تدريجية</p>
            </div>
        </div>
    </div>
</div>

<div class="container">
    <div class="row g-4 mb-4">
        <div class="col-md-8 mx-auto">
            <div class="card-custom p-5 text-center">
                <div style="font-size:5rem; line-height:1; margin-bottom:1rem;">🕵️</div>
                <h2 class="mb-3">أنا مين؟</h2>
                <p class="text-muted mb-4">
                    راح تظهرلك 5 أدلة عن لاعب أو نادي كرة قدم.<br>
                    الدليل الأول صعب جداً، والخامس راح يكون واضح.<br>
                    كل ما عرفت بدري، كل ما جبت نقاط أكثر.
                </p>
                <div class="d-flex justify-content-center gap-3 mb-4 flex-wrap">
                    <span class="badge fs-6 px-3 py-2" style="background:#7C3AED;color:#fff;">1️⃣ صعب جداً — 750 نقطة</span>
                    <span class="badge fs-6 px-3 py-2" style="background:#EF4444;color:#fff;">2️⃣ صعب — 750 نقطة</span>
                    <span class="badge fs-6 px-3 py-2" style="background:#F59E0B;color:#000;">3️⃣ متوسط — 500 نقطة</span>
                    <span class="badge fs-6 px-3 py-2" style="background:#F59E0B;color:#000;">4️⃣ سهل — 500 نقطة</span>
                    <span class="badge fs-6 px-3 py-2" style="background:#10B981;color:#fff;">5️⃣ واضح — 250 نقطة</span>
                </div>

                <form action="{{ route('kuraiyat.store') }}" method="POST">
                    @csrf
                    <button class="btn btn-primary-custom btn-lg">
                        <i class="fas fa-play me-2"></i>ابدأ اللعبة
                    </button>
                </form>
            </div>
        </div>
    </div>

    @if($pastGames->isNotEmpty())
    <div class="card-custom p-4 mt-4">
        <h4 class="mb-3"><i class="fas fa-history text-muted me-2"></i>الألعاب السابقة</h4>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>الجلسة</th>
                        <th>نقاطي</th>
                        <th>التاريخ</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($pastGames as $g)
                    <tr>
                        <td><code class="text-warning">{{ $g->code }}</code></td>
                        <td>{{ $g->players->firstWhere('user_id', auth()->id())?->score ?? 0 }}</td>
                        <td class="text-muted">{{ $g->finished_at?->diffForHumans() }}</td>
                        <td><a href="{{ route('kuraiyat.results', $g->code) }}" class="btn btn-sm btn-outline-light">عرض</a></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
</div>
@endsection
