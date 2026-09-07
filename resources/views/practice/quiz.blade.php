@extends('App')
@section('title', $category->name . ' - ممارسة')
@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <a href="{{ route('practice.index') }}" class="btn btn-sm btn-outline-secondary me-2">
                <i class="fas fa-arrow-right me-1"></i>العودة
            </a>
            <h4 class="fw-bold d-inline">
                <i class="fas fa-{{ $category->icon ?: 'tag' }}" style="color:{{ $category->color }} me-2"></i>
                {{ $category->name }}
            </h4>
        </div>
        <div class="text-end">
            <small class="text-muted d-block" id="progressText">{{ $answered }} / {{ $total }} أسئلة</small>
            <small class="d-block fw-bold" id="progressPercent" style="color:{{ $category->color }}">{{ $progress }}%</small>
        </div>
    </div>

    <div class="progress mb-4" style="height:10px;background:var(--border)">
        <div class="progress-bar" id="progressBar" role="progressbar"
             style="width:{{ $progress }}%;background:{{ $category->color }}"
             aria-valuenow="{{ $progress }}" aria-valuemin="0" aria-valuemax="100">
        </div>
    </div>

    <div id="questionContainer">
        <div class="card-custom p-4 mb-3">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <span class="badge rounded-pill px-3 py-2 fw-bold" id="difficultyBadge"
                      style="background:{{ $question->difficulty_color }}22;color:{{ $question->difficulty_color }}">
                    <i class="fas fa-signal me-1"></i>{{ $question->difficulty_label }}
                </span>
                <span class="badge rounded-pill px-3 py-2 fw-bold" id="pointsBadge"
                      style="background:{{ $question->difficulty_color }};color:#fff">
                    {{ $question->points }} نقطة
                </span>
            </div>

            <h5 class="fw-bold mb-0 text-center" id="questionText">{{ $question->question_text }}</h5>
        </div>

        <div class="row g-3" id="answersContainer">
            @foreach(['a' => $question->answer_a, 'b' => $question->answer_b, 'c' => $question->answer_c, 'd' => $question->answer_d] as $key => $text)
            <div class="col-md-6">
                <button class="btn w-100 p-3 text-start answer-btn border rounded-3 fw-bold"
                        style="border-color:var(--border) !important;color:var(--text-primary)"
                        data-answer="{{ $key }}"
                        onclick="submitAnswer('{{ $key }}')">
                    <span class="badge bg-secondary rounded-circle me-2" style="width:28px;height:28px;display:inline-flex;align-items:center;justify-content:center">
                        {{ strtoupper($key) }}
                    </span>
                    {{ $text }}
                </button>
            </div>
            @endforeach
        </div>
    </div>

    <div id="feedbackContainer" class="d-none">
        <div class="card-custom p-4 mb-3 text-center" id="feedbackCard">
            <div id="feedbackIcon" class="mb-3"></div>
            <h4 id="feedbackTitle" class="fw-bold mb-2"></h4>
            <p id="feedbackText" class="text-muted mb-0"></p>
        </div>
        <div class="text-center">
            <button class="btn btn-primary-custom btn-lg px-5" id="nextBtn" onclick="loadNextQuestion()">
                <i class="fas fa-arrow-left me-2"></i>السؤال التالي
            </button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const categorySlug = '{{ $category->slug }}';
let answeredIds = @json($answered);
let totalQuestions = @json($total);
const correctColor = '#10B981';
const wrongColor = '#EF4444';

function submitAnswer(answer) {
    document.querySelectorAll('.answer-btn').forEach(b => b.disabled = true);

    fetch('{{ route("practice.answer") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({
            question_id: {{ $question->id }},
            answer: answer
        })
    })
    .then(r => {
        if (!r.ok) throw new Error('تعذر إرسال الإجابة');
        return r.json();
    })
    .then(data => {
        showFeedback(data);
    })
    .catch(() => {
        alert('حدث خطأ في الاتصال، يرجى المحاولة مرة أخرى');
        document.querySelectorAll('.answer-btn').forEach(b => b.disabled = false);
    });
}

function showFeedback(data) {
    document.getElementById('questionContainer').classList.add('d-none');
    const feedback = document.getElementById('feedbackContainer');
    feedback.classList.remove('d-none');

    const icon = document.getElementById('feedbackIcon');
    const title = document.getElementById('feedbackTitle');
    const text = document.getElementById('feedbackText');

    const isCorrect = data.is_correct;
    const correctLetter = data.correct_answer.toUpperCase();
    const correctText = data.correct_text;

    if (isCorrect) {
        icon.innerHTML = '<i class="fas fa-check-circle" style="font-size:4rem;color:' + correctColor + '"></i>';
        title.textContent = '✅ إجابة صحيحة!';
        title.style.color = correctColor;
        text.textContent = 'أحسنت! الجواب الصحيح هو "' + correctText + '"';
    } else {
        icon.innerHTML = '<i class="fas fa-times-circle" style="font-size:4rem;color:' + wrongColor + '"></i>';
        title.textContent = '❌ إجابة خاطئة';
        title.style.color = wrongColor;
        text.innerHTML = 'الجواب الصحيح هو: <strong class="text-success">' + correctLetter + ' - ' + correctText + '</strong>';
    }
}

function loadNextQuestion() {
    document.getElementById('nextBtn').disabled = true;
    document.getElementById('nextBtn').innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>جاري التحميل...';

    fetch('{{ route("practice.next", $category->slug) }}')
    .then(r => {
        if (!r.ok) throw new Error('تعذر تحميل السؤال التالي');
        return r.json();
    })
    .then(data => {
        if (data.done) {
            document.getElementById('questionContainer').classList.add('d-none');
            const feedback = document.getElementById('feedbackContainer');
            document.getElementById('feedbackCard').innerHTML = `
                <div class="py-4">
                    <i class="fas fa-trophy" style="font-size:4rem;color:var(--warning)"></i>
                    <h4 class="fw-bold mt-3 text-warning">🎉 تهانينا!</h4>
                    <p class="text-muted">لقد أجبت على جميع الأسئلة في هذه الفئة!</p>
                    <div class="mt-3">
                        <a href="{{ route('practice.index') }}" class="btn btn-primary-custom btn-lg px-5">
                            <i class="fas fa-arrow-right me-2"></i>العودة للفئات
                        </a>
                    </div>
                </div>
            `;
            document.getElementById('nextBtn').classList.add('d-none');
            updateProgress(data.progress, data.answered, data.total);
            return;
        }

        document.getElementById('questionContainer').classList.remove('d-none');
        document.getElementById('feedbackContainer').classList.add('d-none');

        document.getElementById('questionText').textContent = data.question.question_text;
        document.getElementById('difficultyBadge').textContent = data.question.difficulty_label;
        document.getElementById('difficultyBadge').style.background = data.question.difficulty_color + '22';
        document.getElementById('difficultyBadge').style.color = data.question.difficulty_color;
        document.getElementById('pointsBadge').textContent = data.question.points + ' نقطة';
        document.getElementById('pointsBadge').style.background = data.question.difficulty_color;

        const answers = [
            { key: 'a', text: data.question.answer_a },
            { key: 'b', text: data.question.answer_b },
            { key: 'c', text: data.question.answer_c },
            { key: 'd', text: data.question.answer_d },
        ];

        const container = document.getElementById('answersContainer');
        container.innerHTML = '';
        answers.forEach(a => {
            const col = document.createElement('div');
            col.className = 'col-md-6';
            col.innerHTML = `
                <button class="btn w-100 p-3 text-start answer-btn border rounded-3 fw-bold"
                        style="border-color:var(--border) !important;color:var(--text-primary)"
                        data-answer="${a.key}"
                        onclick="submitAnswer('${a.key}')">
                    <span class="badge bg-secondary rounded-circle me-2" style="width:28px;height:28px;display:inline-flex;align-items:center;justify-content:center">
                        ${a.key.toUpperCase()}
                    </span>
                    ${a.text}
                </button>
            `;
            container.appendChild(col);
        });

        updateProgress(data.progress, data.answered, data.total);
        document.getElementById('nextBtn').disabled = false;
        document.getElementById('nextBtn').innerHTML = '<i class="fas fa-arrow-left me-2"></i>السؤال التالي';
        window.scrollTo({ top: 0, behavior: 'smooth' });
    })
    .catch(() => {
        alert('حدث خطأ في الاتصال، يرجى المحاولة مرة أخرى');
        document.getElementById('nextBtn').disabled = false;
        document.getElementById('nextBtn').innerHTML = '<i class="fas fa-arrow-left me-2"></i>السؤال التالي';
    });
}

function updateProgress(percent, answered, total) {
    document.getElementById('progressBar').style.width = percent + '%';
    document.getElementById('progressBar').setAttribute('aria-valuenow', percent);
    document.getElementById('progressText').textContent = answered + ' / ' + total + ' أسئلة';
    document.getElementById('progressPercent').textContent = percent + '%';
}
</script>
@endpush
