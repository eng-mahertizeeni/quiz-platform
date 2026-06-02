@extends('App')
@section('title', 'اختيار الفئات')
@section('content')
<div class="container py-4">
    <h4 class="fw-bold mb-3"><i class="fas fa-tags text-warning me-2"></i>اختر الفئات للعبة</h4>
    <p class="text-muted">اختر من 1 إلى 6 فئات. يمكنك اختيار عدد أقل من الفئات للعبة أسرع.</p>

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
                    <div class="d-flex justify-content-between mt-1">
                        <small class="text-muted">{{ $cat->questions_count }} سؤال</small>
                        @auth
                        <small style="color:{{ $cat->color }}">{{ $cat->progress ?? 0 }}%</small>
                        @endauth
                    </div>
                    @auth
                    <div class="progress mt-1" style="height:4px;background:var(--border)">
                        <div class="progress-bar" role="progressbar"
                             style="width:{{ $cat->progress ?? 0 }}%;background:{{ $cat->color }}"
                             aria-valuenow="{{ $cat->progress ?? 0 }}" aria-valuemin="0" aria-valuemax="100">
                        </div>
                    </div>
                    @endauth
                    @if(!$cat->has_enough)
                    <small class="text-danger">لا يوجد أسئلة كافية</small>
                    @endif
                </div>
            </div>
            @endforeach
        </div>

        <div class="mt-4 d-flex justify-content-between align-items-center">
            <span class="text-muted" id="selectedCount">0 من 6 فئات مختارة</span>
            <button type="submit" class="btn btn-primary-custom btn-lg" id="submitBtn" disabled>تأكيد الاختيار</button>
        </div>
    </form>
</div>

@push('scripts')
<script>
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
                if (selected.size >= 6) { this.checked = false; return; }
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
        document.getElementById('selectedCount').innerText = count + ' من 6 فئات مختارة';
        document.getElementById('submitBtn').disabled = count < 1 || count > 6;
    }
</script>
@endpush
@endsection
