@extends('App')
@section('title', 'اختيار الفئات')
@section('content')
<div class="container py-4">
    <h4 class="fw-bold mb-3"><i class="fas fa-tags text-warning me-2"></i>اختر الفئات للعبة</h4>
    <p class="text-muted">اختر {{ \App\Models\GameSession::CATEGORIES_COUNT }} فئات. كل فئة فيها 6 أسئلة: سؤالان بـ 200 (سهل)، سؤالان بـ 400 (متوسط)، سؤالان بـ 600 (صعب).</p>

    @error('category_ids')
    <div class="alert alert-danger">{{ $message }}</div>
    @enderror

    <form method="POST" action="{{ route('game.categories.attach', $session->code) }}" id="categoriesForm">
        @csrf
        <div class="row g-3">
            @foreach($categories as $cat)
            <div class="col-md-4">
                <div class="card-custom p-3 category-card" data-id="{{ $cat->id }}" onclick="toggleCategory(this)" style="cursor:pointer;{{ !$cat->has_enough ? 'opacity:0.5' : '' }}">
                    <div class="form-check">
                        <input type="checkbox" name="category_ids[]" value="{{ $cat->id }}" class="form-check-input category-checkbox" id="cat{{ $cat->id }}" {{ !$cat->has_enough ? 'disabled' : '' }}>
                        <label class="form-check-label fw-bold" for="cat{{ $cat->id }}">
                            <i class="fas fa-{{ $cat->icon ?: 'tag' }} me-1" style="color:{{ $cat->color }}"></i>
                            {{ $cat->name }}
                        </label>
                    </div>
                    <small class="text-muted d-block mt-1">{{ $cat->questions_count }} سؤال</small>
                    @if(!$cat->has_enough)
                    <small class="text-danger">لا يوجد أسئلة كافية</small>
                    @endif
                </div>
            </div>
            @endforeach
        </div>

        <div class="mt-4 d-flex justify-content-between align-items-center">
            <span class="text-muted" id="selectedCount">0 من {{ \App\Models\GameSession::CATEGORIES_COUNT }} فئات مختارة</span>
            <button type="submit" class="btn btn-primary-custom btn-lg" id="submitBtn" disabled>تأكيد الاختيار</button>
        </div>
    </form>
</div>

@push('scripts')
<script>
    const REQUIRED = @json(\App\Models\GameSession::CATEGORIES_COUNT);
    let selected = new Set();
    function toggleCategory(el) {
        const cb = el.querySelector('.category-checkbox');
        if (cb.disabled) return;
        cb.checked = !cb.checked;
        cb.dispatchEvent(new Event('change'));
    }
    document.querySelectorAll('.category-checkbox').forEach(cb => {
        cb.addEventListener('change', function() {
            const id = this.value;
            if (this.checked) {
                if (selected.size >= REQUIRED) { this.checked = false; return; }
                selected.add(id);
                this.closest('.category-card').style.borderColor = 'var(--success)';
            } else {
                selected.delete(id);
                this.closest('.category-card').style.borderColor = '';
            }
            updateUI();
        });
    });
    function updateUI() {
        const count = selected.size;
        document.getElementById('selectedCount').innerText = count + ' من ' + REQUIRED + ' فئات مختارة';
        document.getElementById('submitBtn').disabled = count !== REQUIRED;
    }
</script>
@endpush
@endsection
