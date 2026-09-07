@extends('App')
@section('title', 'أنا مين - كرويات')
@section('content')
<div class="container">
    <div class="row justify-content-center mt-4">
        <div class="col-md-8">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <span class="badge bg-warning text-dark fs-6">جولة {{ $game->current_round }}/{{ $game->total_rounds }}</span>
                @if($authPlayer)
                <div class="text-warning fw-bold fs-5">{{ $authPlayer->score }} نقطة</div>
                @endif
            </div>

            <div class="card-custom p-5 text-center">
                <div style="font-size:3rem; line-height:1; margin-bottom:0.5rem;">🕵️</div>
                <h3 class="mb-1">أنا مين؟</h3>
                <p class="text-muted mb-4 small">كل ما ظهرت أدلة أكثر، تنقص النقاط. خمّن بدري عشان تجيب أعلى نقاط!</p>

                <div id="clueArea" class="mb-4">
                    <div class="d-flex justify-content-center gap-2 mb-3" id="clueDots">
                        <span class="clue-dot" data-idx="0">1</span>
                        <span class="clue-dot" data-idx="1">2</span>
                        <span class="clue-dot" data-idx="2">3</span>
                        <span class="clue-dot" data-idx="3">4</span>
                        <span class="clue-dot" data-idx="4">5</span>
                    </div>
                    <div id="clueText" class="fs-5 fw-bold text-warning mb-2 p-4 rounded-3" style="background:rgba(245,158,11,0.08); min-height:80px; display:flex; align-items:center; justify-content:center;">
                        ⏳ جاري تحضير الدليل...
                    </div>
                    <div id="clueNumber" class="text-muted small"></div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">تخمينك</label>
                    <div class="input-group">
                        <input type="text" id="guessInput" class="form-control text-center"
                               placeholder="اكتب اسم اللاعب أو النادي..." disabled>
                        <button id="guessBtn" class="btn btn-primary-custom" disabled>
                            <i class="fas fa-check me-1"></i>تخمين
                        </button>
                        <button id="skipBtn" class="btn btn-secondary" disabled>
                            <i class="fas fa-forward me-1"></i>ما عرفته
                        </button>
                    </div>
                    <div id="guessFeedback" class="mt-2"></div>
                </div>

                <div id="roundResult" class="d-none">
                    <div id="roundResultMessage" class="fs-4 fw-bold mb-2"></div>
                    <div id="roundAnswer" class="text-muted"></div>
                    <button id="nextClueBtn" class="btn btn-primary-custom mt-3">
                        <i class="fas fa-arrow-left me-2"></i>التالي
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<style>
.clue-dot {
    display: inline-flex; align-items: center; justify-content: center;
    width: 40px; height: 40px; border-radius: 50%;
    background: var(--bg-card); border: 2px solid var(--border);
    color: var(--text-muted); font-weight: bold; font-size: 0.9rem;
    transition: all 0.3s;
}
.clue-dot.active {
    background: 

}
.clue-dot.revealed {
    background: 

}
</style>
<script>
let currentRoundId = {{ $state['current_round_data']['id'] ?? 'null' }};
let gameOver = false;

function showClue(data) {
    document.getElementById('clueText').innerHTML = '🔍 ' + data.clue;
    document.getElementById('clueNumber').textContent = 'دليل ' + data.clue_number + ' من ' + data.total_clues;

    document.querySelectorAll('.clue-dot').forEach((dot, i) => {
        if (i < data.clue_number) dot.classList.add('active');
    });

    document.getElementById('guessInput').disabled = false;
    document.getElementById('guessBtn').disabled = false;
    document.getElementById('skipBtn').disabled = false;
    document.getElementById('guessInput').focus();
    document.getElementById('guessFeedback').innerHTML = '';
}

function loadClue() {
    fetch('{{ route("kuraiyat.clue", $game->code) }}')
        .then(r => r.json())
        .then(data => {
            if (data.status === 'ok') {
                showClue(data);
            } else if (data.status === 'finished') {
                document.getElementById('clueText').innerHTML = '😵 ' + data.answer;
                document.getElementById('guessBtn').disabled = true;
                document.getElementById('skipBtn').disabled = true;
                document.getElementById('guessInput').disabled = true;
                showNextRound();
            }
        });
}

function updateScore(pts) {
    const scoreEl = document.getElementById('scoreDisplay');
    if (scoreEl) {
        let current = parseInt(scoreEl.textContent) || 0;
        scoreEl.textContent = (current + pts) + ' نقطة';
    }
}

document.getElementById('guessBtn')?.addEventListener('click', submitGuess);
document.getElementById('guessInput')?.addEventListener('keypress', function(e) {
    if (e.key === 'Enter') submitGuess();
});

function submitGuess() {
    const guess = document.getElementById('guessInput').value.trim();
    if (!guess) return;

    const formData = new FormData();
    formData.append('game_code', '{{ $game->code }}');
    formData.append('round_id', currentRoundId);
    formData.append('answer', guess);

    fetch('{{ route("kuraiyat.answer") }}', {
        method: 'POST',
        headers: {'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value},
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        const feedback = document.getElementById('guessFeedback');
        if (data.status === 'correct') {
            feedback.innerHTML = '<span class="text-success fw-bold fs-5">✅ صحيح! +' + data.points + ' نقطة</span>';
            updateScore(data.points);
            document.getElementById('guessBtn').disabled = true;
            document.getElementById('skipBtn').disabled = true;
            document.getElementById('guessInput').disabled = true;
            if (data.game_over) {
                setTimeout(() => window.location.href = '{{ route("kuraiyat.results", $game->code) }}', 2000);
            } else {
                showNextRound();
            }
        } else {
            feedback.innerHTML = '<span class="text-danger">❌ غلط، حاول مرة أخرى</span>';
            document.getElementById('guessInput').value = '';
            document.getElementById('guessInput').focus();
        }
    });
}

document.getElementById('skipBtn')?.addEventListener('click', function() {
    fetch('{{ route("kuraiyat.skip", $game->code) }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
            'Content-Type': 'application/json',
        }
    })
    .then(r => r.json())
    .then(data => {
        if (data.status === 'ok') {
            showClue(data);
        } else if (data.status === 'finished') {
            document.getElementById('clueText').innerHTML = '😵 ' + data.answer;
            document.getElementById('guessBtn').disabled = true;
            document.getElementById('skipBtn').disabled = true;
            document.getElementById('guessInput').disabled = true;
            showNextRound();
        } else if (data.status === 'round_skipped') {
            document.getElementById('guessFeedback').innerHTML = '<span class="text-muted">الإجابة: ' + data.answer + '</span>';
            document.getElementById('guessBtn').disabled = true;
            document.getElementById('skipBtn').disabled = true;
            document.getElementById('guessInput').disabled = true;
            if (data.game_over) {
                setTimeout(() => window.location.href = '{{ route("kuraiyat.results", $game->code) }}', 2000);
            } else {
                showNextRound();
            }
        }
    });
});

function showNextRound() {
    document.getElementById('roundResultMessage').innerHTML = '👇';
    document.getElementById('nextClueBtn').classList.remove('d-none');
    document.getElementById('nextClueBtn').onclick = () => window.location.reload();
}

if (currentRoundId) {
    loadClue();
}
</script>
@endpush
@endsection
