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
        <div class="col-lg-7">
            <div class="card-custom p-4">
                <h4 class="mb-3"><i class="fas fa-plus-circle text-success me-2"></i>إنشاء لعبة جديدة</h4>
                <p class="text-muted mb-4">اضبط إعدادات اللعبة ثم ادعُ أصدقاءك عبر كود اللعبة</p>

                <form action="{{ route('bluff.store') }}" method="POST" id="createGameForm">
                    @csrf

                    <div class="row g-4 mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">
                                <i class="fas fa-list-ol me-1 text-warning"></i>عدد الجولات
                            </label>
                            <div class="d-flex align-items-center gap-2">
                                <button type="button" class="btn btn-outline-secondary rounded-circle" style="width:40px;height:40px;" onclick="adjustRounds(-1)" id="roundDecBtn">−</button>
                                <input type="number" name="total_rounds" id="roundsInput" value="8" min="3" max="15"
                                       class="form-control text-center fw-bold" style="font-size:1.3rem;width:80px;">
                                <button type="button" class="btn btn-outline-secondary rounded-circle" style="width:40px;height:40px;" onclick="adjustRounds(1)" id="roundIncBtn">+</button>
                            </div>
                            <div class="text-muted small mt-1"><span id="roundsLabel">8</span> جولات · مدة اللعبة التقريبية <span id="approxDuration">4</span> دقائق</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">
                                <i class="fas fa-hourglass-half me-1 text-warning"></i>الوقت لكل سؤال
                            </label>
                            <div class="d-flex align-items-center gap-2">
                                <input type="range" name="question_duration" id="durationSlider" value="30" min="15" max="40" step="5"
                                       class="form-range flex-grow-1" style="height:6px;">
                                <span id="durationLabel" class="badge bg-warning text-dark fs-6" style="min-width:60px;">30ث</span>
                            </div>
                            <div class="text-muted small mt-1">يشمل وقت الإجابة والتصويت معاً</div>
                        </div>
                    </div>

                    <div class="mb-1">
                        <label class="form-label fw-bold">
                            <i class="fas fa-layer-group me-1 text-warning"></i>اختر الفقرات <span class="text-danger">*</span>
                        </label>
                        <p class="text-muted small mb-3">اختر فقرة واحدة أو أكثر لتظهر أسئلتها في اللعبة</p>
                    </div>

                    <div id="categoriesContainer" class="row g-2 mb-3"></div>
                    <div class="text-danger small mb-3 d-none" id="categoryError"><i class="fas fa-exclamation-circle me-1"></i>اختر فقرة واحدة على الأقل</div>

                    <div class="d-flex gap-2 mb-3">
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="toggleAllCats(true)">اختر الكل</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="toggleAllCats(false)">إلغاء الكل</button>
                        <span class="text-muted small align-self-center" id="selectedCount">0 فقرات محددة</span>
                    </div>

                    <button type="submit" class="btn btn-primary-custom w-100 py-3" id="createBtn">
                        <i class="fas fa-play me-2"></i>إنشاء اللعبة
                    </button>
                </form>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card-custom p-4 h-100">
                <h4 class="mb-3"><i class="fas fa-sign-in-alt text-info me-2"></i>انضمام إلى لعبة</h4>
                <p class="text-muted mb-3">أدخل كود اللعبة المكون من 6 أحرف</p>
                <form action="{{ route('bluff.join') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label fw-bold">كود اللعبة</label>
                        <input type="text" name="code" class="form-control text-center fw-bold"
                               placeholder="مثال: ABC123" maxlength="6" style="text-transform:uppercase; letter-spacing:4px; font-size:1.5rem;" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">
                            <i class="fas fa-tag me-1 text-info"></i>الاسم المستعار
                            <span class="text-muted small fw-normal">(اختياري — سيظهر اسمك النظامي إن لم تحدده)</span>
                        </label>
                        <input type="text" name="display_name" class="form-control text-center"
                               placeholder="أدخل اسماً مستعاراً" maxlength="50">
                    </div>
                    <button type="submit" class="btn btn-primary-custom w-100">
                        <i class="fas fa-door-open me-1"></i>انضمام
                    </button>
                </form>

                <hr class="my-4">

                <div class="bg-dark p-3 rounded-3">
                    <h5 class="mb-2"><i class="fas fa-info-circle text-info me-2"></i>شرح سريع</h5>
                    <ul class="list-unstyled small mb-0">
                        <li class="mb-2"><span class="badge bg-warning text-dark me-2">1</span>يكتب الجميع إجابة للسؤال</li>
                        <li class="mb-2"><span class="badge bg-warning text-dark me-2">2</span>تصوت على الإجابة الصحيحة</li>
                        <li class="mb-2"><span class="badge bg-warning text-dark me-2">3</span>الصحيح <strong>+2</strong> · كل من خُدع <strong>+1</strong></li>
                    </ul>
                </div>
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
                    <tr><th>الكود</th><th>اللاعبين</th><th>نقاطي</th><th>التاريخ</th><th></th></tr>
                </thead>
                <tbody>
                    @foreach($pastGames as $g)
                    <tr>
                        <td><code class="text-warning">{{ $g->code }}</code></td>
                        <td>{{ $g->players->count() }}</td>
                        <td>{{ $g->players->firstWhere('user_id', auth()->id())?->total_score ?? 0 }}</td>
                        <td class="text-muted">{{ $g->finished_at?->diffForHumans() }}</td>
                        <td><a href="{{ route('bluff.results', $g->code) }}" class="btn btn-sm btn-outline-warning"><i class="fas fa-chart-bar"></i></a></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
    const categoriesData = {!! json_encode($categories->map(fn($c) => [
        'id' => $c->id,
        'name' => $c->name,
        'icon' => $c->icon ?: 'tag',
        'color' => $c->color ?: '#6B7280',
        'question_count' => $c->questions_count ?? 0,
    ])->values()) !!};

    function renderCategories() {
        const container = document.getElementById('categoriesContainer');
        container.innerHTML = categoriesData.map(c => `
            <div class="col-md-6 col-lg-4">
                <label class="cat-card" for="cat${c.id}" data-selected="false"
                       style="--cat-color:${c.color};">
                    <input type="checkbox" class="cat-checkbox" name="categories[]" value="${c.id}" id="cat${c.id}"
                           onchange="updateCatCard(this)">
                    <div class="cat-card-inner">
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <i class="fas fa-${c.icon}" style="color:${c.color};font-size:1.1rem;"></i>
                            <span class="fw-bold small">${c.name}</span>
                        </div>
                        <div class="text-muted" style="font-size:0.75rem;">${c.question_count} سؤال</div>
                    </div>
                    <div class="cat-check-mark"><i class="fas fa-check"></i></div>
                </label>
            </div>
        `).join('');
    }

    function updateCatCard(checkbox) {
        const label = checkbox.closest('.cat-card');
        label.dataset.selected = checkbox.checked ? 'true' : 'false';
        updateSelectedCount();
    }

    function toggleAllCats(select) {
        document.querySelectorAll('.cat-checkbox').forEach(cb => {
            cb.checked = select;
            updateCatCard(cb);
        });
    }

    function updateSelectedCount() {
        const count = document.querySelectorAll('.cat-checkbox:checked').length;
        let label;
        if (count === 0) label = 'لم تختر أي فقرة';
        else if (count === 1) label = 'فقرة واحدة محددة';
        else if (count === 2) label = 'فقرتين محددتين';
        else label = count + ' فقرات محددة';
        document.getElementById('selectedCount').textContent = label;
        document.getElementById('selectedCount').className = 'small align-self-center ' + (count === 0 ? 'text-danger' : 'text-success');
    }

    function adjustRounds(delta) {
        const input = document.getElementById('roundsInput');
        let v = parseInt(input.value) + delta;
        v = Math.max(3, Math.min(15, v));
        input.value = v;
        updateRoundLabels();
    }

    function updateRoundLabels() {
        const v = parseInt(document.getElementById('roundsInput').value);
        document.getElementById('roundsLabel').textContent = v;
        document.getElementById('roundDecBtn').disabled = v <= 3;
        document.getElementById('roundIncBtn').disabled = v >= 15;

        const dur = parseInt(document.getElementById('durationSlider').value);
        const mins = Math.round(v * dur * 2 / 60);
        document.getElementById('approxDuration').textContent = Math.max(1, mins);
    }

    document.getElementById('durationSlider').addEventListener('input', function() {
        document.getElementById('durationLabel').textContent = this.value + 'ث';
        updateRoundLabels();
    });

    document.getElementById('roundsInput').addEventListener('change', updateRoundLabels);

    document.getElementById('createGameForm')?.addEventListener('submit', function(e) {
        const checked = document.querySelectorAll('.cat-checkbox:checked');
        if (checked.length === 0) {
            e.preventDefault();
            document.getElementById('categoryError').classList.remove('d-none');
        }
    });

    renderCategories();
    updateRoundLabels();
    updateSelectedCount();
</script>

<style>
    .cat-card {
        position: relative;
        display: block;
        cursor: pointer;
        border-radius: 12px;
        border: 2px solid var(--border);
        background: var(--bg-card);
        transition: all 0.2s ease;
        overflow: hidden;
        user-select: none;
    }
    .cat-card:hover {
        border-color: var(--cat-color, #6B7280);
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }
    .cat-card[data-selected="true"] {
        border-color: var(--cat-color, #6B7280);
        background: color-mix(in srgb, var(--cat-color, #6B7280) 12%, var(--bg-card));
    }
    .cat-card .cat-checkbox {
        position: absolute;
        opacity: 0;
        pointer-events: none;
    }
    .cat-card-inner {
        padding: 10px 12px;
    }
    .cat-check-mark {
        position: absolute;
        top: 6px;
        right: 6px;
        width: 20px;
        height: 20px;
        border-radius: 50%;
        background: var(--cat-color, #6B7280);
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        transform: scale(0.5);
        transition: all 0.2s ease;
    }
    .cat-check-mark i {
        color: #fff;
        font-size: 0.7rem;
    }
    .cat-card[data-selected="true"] .cat-check-mark {
        opacity: 1;
        transform: scale(1);
    }
</style>
@endpush
