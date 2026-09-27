@extends('App')
@section('title', 'الرئيسية')
@section('content')
<section class="hero-section text-center py-5" style="background:linear-gradient(135deg, var(--bg-card) 0%, rgba(245,158,11,0.1) 100%)">
    <div class="container">
        <h1 class="display-4 fw-900 mb-3">
            <i class="fas fa-brain text-warning me-2"></i>Quiz Battle
        </h1>
        <p class="lead text-muted mb-4">منصة المسابقات الذكية - اختبر معرفتك وتحدى الأصدقاء</p>
        @auth
            <a href="{{ route('game.create') }}" class="btn btn-primary-custom btn-lg px-5">
                <i class="fas fa-play me-2"></i>ابدأ لعبة جديدة
            </a>
        @else
            <a href="{{ route('register') }}" class="btn btn-primary-custom btn-lg px-5">
                <i class="fas fa-user-plus me-2"></i>سجل الآن وابدأ التحدي
            </a>
        @endauth
    </div>
</section>

<div class="container py-4">
    @if($featuredCategories->isNotEmpty())
    <section class="mb-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="fw-bold"><i class="fas fa-star text-warning me-2"></i>الفئات المميزة</h4>
            <a href="{{ route('leaderboard') }}" class="btn btn-sm btn-outline-warning">المتصدرين <i class="fas fa-arrow-left me-1"></i></a>
        </div>
        <div class="row g-3">
            @foreach($featuredCategories as $cat)
            <div class="col-md-3 col-6">
                <div class="card-custom p-4 text-center h-100">
                    <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width:60px;height:60px;background:{{ $cat->color }}22;color:{{ $cat->color }}">
                        <i class="fas fa-{{ $cat->icon ?: 'tag' }} fa-2x"></i>
                    </div>
                    <h6 class="fw-bold mb-1">{{ $cat->name }}</h6>
                    <small class="text-muted">{{ $cat->questions_count }} سؤال</small>
                </div>
            </div>
            @endforeach
        </div>
    </section>
    @endif

    <div class="row g-4">
        <div class="col-md-6">
            <section>
                <h4 class="fw-bold mb-3"><i class="fas fa-trophy text-warning me-2"></i>المتصدرين</h4>
                @forelse($leaderboard as $i => $u)
                <div class="card-custom p-3 mb-2 d-flex align-items-center gap-3">
                    <span class="badge bg-warning text-dark rounded-circle fw-bold" style="width:32px;height:32px;display:flex;align-items:center;justify-content:center">{{ $i + 1 }}</span>
                    <img src="{{ $u->avatar_url }}" alt="" class="rounded-circle" width="40" height="40">
                    <div class="flex-grow-1">
                        <strong>{{ $u->name }}</strong>
                        <small class="text-muted d-block">{{ $u->total_score }} نقطة</small>
                    </div>
                </div>
                @empty
                <p class="text-muted">لا يوجد متصدرون بعد</p>
                @endforelse
            </section>
        </div>
        <div class="col-md-6">
            <section>
                <h4 class="fw-bold mb-3"><i class="fas fa-fire text-danger me-2"></i>الفئات الأكثر لعباً</h4>
                @forelse($mostPlayedCategories as $cat)
                <div class="card-custom p-3 mb-2 d-flex align-items-center gap-3">
                    <div style="width:40px;height:40px;border-radius:10px;background:{{ $cat->color }}22;color:{{ $cat->color }};display:flex;align-items:center;justify-content:center">
                        <i class="fas fa-{{ $cat->icon ?: 'tag' }}"></i>
                    </div>
                    <div class="flex-grow-1">
                        <strong>{{ $cat->name }}</strong>
                        <small class="text-muted d-block">{{ $cat->times_played }} لعبة</small>
                    </div>
                </div>
                @empty
                <p class="text-muted">لا توجد إحصاءات بعد</p>
                @endforelse
            </section>
        </div>
    </div>
</div>
@endsection
