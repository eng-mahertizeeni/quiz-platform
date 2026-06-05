@extends('App')

@section('title', 'Face Off')
@section('content')
<div class="page-header">
    <div class="container">
        <h1 class="mb-0"><i class="fas fa-gamepad me-2 text-warning"></i>Face Off</h1>
        <p class="text-muted mb-0">لعبة المواجهة والتحدي — اكتب إجابات مقنعة وخدع اللاعبين الآخرين</p>
    </div>
</div>

<div class="container">
    <div class="row g-4">
        <div class="col-md-6">
            <div class="card-custom p-4 h-100">
                <h4 class="mb-3"><i class="fas fa-plus-circle text-success me-2"></i>إنشاء لعبة جديدة</h4>
                <p class="text-muted mb-3">أنشئ غرفة وادعُ أصدقاءك للانضمام عبر كود اللعبة</p>
                <form action="{{ route('bluff.store') }}" method="POST" id="createGameForm">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label fw-bold">عدد الجولات</label>
                        <select name="total_rounds" class="form-select">
                            @foreach([3, 5, 8, 10, 15] as $n)
                                <option value="{{ $n }}" {{ $n === 8 ? 'selected' : '' }}>{{ $n }} جولات</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">مدة الإجابة والتصويت (ثانية)</label>
                        <select name="question_duration" class="form-select">
                            @foreach([15, 20, 25, 30, 35, 40] as $d)
                                <option value="{{ $d }}" {{ $d === 30 ? 'selected' : '' }}>{{ $d }} ثانية</option>
                            @endforeach
                        </select>
                        <div class="text-muted small mt-1"><i class="fas fa-info-circle me-1"></i>المدة تنطبق على مرحلتي الإجابة والتصويت معاً</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">اختر الفقرات <span class="text-danger">*</span></label>
                        <div class="row g-2" id="categoriesContainer">
                            @foreach($categories as $cat)
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input category-checkbox" type="checkbox"
                                           name="categories[]" value="{{ $cat->id }}" id="cat{{ $cat->id }}">
                                    <label class="form-check-label" for="cat{{ $cat->id }}">
                                        {{ $cat->name }}
                                    </label>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        <div class="text-danger small mt-1 d-none" id="categoryError">اختر فقرة واحدة على الأقل</div>
                    </div>
                    <button type="submit" class="btn btn-primary-custom w-100" id="createBtn">
                        <i class="fas fa-play me-1"></i>إنشاء اللعبة
                    </button>
                </form>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card-custom p-4 h-100">
                <h4 class="mb-3"><i class="fas fa-sign-in-alt text-info me-2"></i>انضمام إلى لعبة</h4>
                <p class="text-muted mb-3">أدخل كود اللعبة المكون من 6 أحرف للانضمام إلى أصدقائك</p>
                <form action="{{ route('bluff.join') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label fw-bold">كود اللعبة</label>
                        <input type="text" name="code" class="form-control text-center fw-bold"
                               placeholder="مثال: ABC123" maxlength="6" style="text-transform:uppercase; letter-spacing:4px; font-size:1.5rem;" required>
                    </div>
                    <button type="submit" class="btn btn-primary-custom w-100">
                        <i class="fas fa-door-open me-1"></i>انضمام
                    </button>
                </form>
            </div>
        </div>
    </div>

    @if($activeGames->isNotEmpty())
    <div class="card-custom p-4 mt-4">
        <h4 class="mb-3"><i class="fas fa-spinner fa-spin text-warning me-2"></i>الألعاب النشطة</h4>
        <div class="row g-3">
            @foreach($activeGames as $g)
            <div class="col-md-4">
                <a href="{{ route('bluff.play', $g->code) }}" class="text-decoration-none">
                    <div class="card-custom p-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="badge bg-warning text-dark">جولة {{ $g->current_round }}/{{ $g->total_rounds }}</span>
                            <span class="text-muted small">{{ $g->players->count() }} لاعب</span>
                        </div>
                        <div class="d-flex align-items-center gap-2 mb-0">
                            <code class="text-warning fw-bold">{{ $g->code }}</code>
                            <span class="text-muted small">— {{ $g->creator?->name ?? 'غير معروف' }}</span>
                        </div>
                    </div>
                </a>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    @if($pastGames->isNotEmpty())
    <div class="card-custom p-4 mt-4">
        <h4 class="mb-3"><i class="fas fa-history text-muted me-2"></i>الألعاب السابقة</h4>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>الكود</th>
                        <th>اللاعبين</th>
                        <th>نقاطي</th>
                        <th>التاريخ</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($pastGames as $g)
                    <tr>
                        <td><code class="text-warning">{{ $g->code }}</code></td>
                        <td>{{ $g->players->count() }}</td>
                        <td>{{ $g->players->firstWhere('user_id', auth()->id())?->total_score ?? 0 }}</td>
                        <td class="text-muted">{{ $g->finished_at?->diffForHumans() }}</td>
                        <td>
                            <a href="{{ route('bluff.results', $g->code) }}" class="btn btn-sm btn-outline-warning">
                                <i class="fas fa-chart-bar"></i>
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    <div class="card-custom p-4 mt-4">
        <h4 class="mb-3"><i class="fas fa-info-circle text-info me-2"></i>شرح لعبة Face Off</h4>
        <div class="row g-3">
            <div class="col-md-6">
                <ul class="list-unstyled">
                    <li class="mb-2"><span class="badge bg-warning text-dark me-2">1</span>يُعرض سؤال، والجميع يكتب إجابة</li>
                    <li class="mb-2"><span class="badge bg-warning text-dark me-2">2</span>إذا كتبت الإجابة الصحيحة، اكتب إجابة أخرى لخداع الباقين</li>
                    <li class="mb-2"><span class="badge bg-warning text-dark me-2">3</span>تُخلط الإجابات مع الإجابة الصحيحة وتُعرض على الجميع</li>
                </ul>
            </div>
            <div class="col-md-6">
                <ul class="list-unstyled">
                    <li class="mb-2"><span class="badge bg-warning text-dark me-2">4</span>يصوت اللاعبون على الإجابة التي يعتقدون أنها صحيحة</li>
                    <li class="mb-2"><span class="badge bg-warning text-dark me-2">5</span>من يصوت صح يحصل <strong>+2 نقطة</strong></li>
                    <li class="mb-2"><span class="badge bg-warning text-dark me-2">6</span>كل من خُدع بإجابتك يمنحك <strong>+1 نقطة</strong></li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.getElementById('createGameForm')?.addEventListener('submit', function(e) {
        const checked = document.querySelectorAll('.category-checkbox:checked');
        const error = document.getElementById('categoryError');
        if (checked.length === 0) {
            e.preventDefault();
            error.classList.remove('d-none');
        } else {
            error.classList.add('d-none');
        }
    });
</script>
@endpush
