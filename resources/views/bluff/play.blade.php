@extends('App')

@section('title', 'Face Off - جاري اللعب')
@section('content')
<div class="page-header">
    <div class="container text-center">
        <h1 class="mb-0">
            <i class="fas fa-gamepad me-2 text-warning"></i>Face Off
            <small class="text-muted fs-5 me-2" id="roundLabel">—</small>
        </h1>
    </div>
</div>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div id="scoresBar" class="mb-4"></div>

            <div id="loadingIndicator" class="card-custom p-5 text-center" style="display:none;">
                <div class="spinner-border text-warning mb-3" role="status" style="width:3rem;height:3rem;"></div>
                <h5 class="text-muted">جاري تحميل حالة اللعبة...</h5>
            </div>

            <div id="spectatorBanner" class="alert alert-info border-0 rounded-3 text-center" style="display:none;">
                <i class="fas fa-eye me-2"></i>أنت في وضع المشاهدة
            </div>

            <div id="selectingPhase" class="card-custom p-4 text-center" style="display:none;">
                <div class="mb-2">
                    <span class="badge bg-primary fs-6">اختيار الفقرة</span>
                </div>
                <h5 class="mb-1" id="selectingMsg"></h5>
                <p class="text-muted mb-3" id="selectingSubMsg"></p>
                <div id="categoriesList" class="row g-3 mb-4"></div>
                <div id="selectFeedback" class="mt-3"></div>
            </div>

            <div id="questionPhase" class="card-custom p-4 text-center" style="display:none;">
                <div class="mb-2 d-flex justify-content-between align-items-center">
                    <span class="badge bg-warning text-dark fs-6" id="roundBadge"></span>
                    <span id="answerTimer" class="badge bg-danger fs-6" style="display:none;">
                        <i class="fas fa-hourglass-half me-1"></i><span id="answerTimerValue">30</span>ث
                    </span>
                </div>
                <h3 class="mb-4" id="questionText"></h3>

                <div class="mb-3" id="answerSection">
                    <label class="form-label fw-bold">اكتب إجابتك <span class="text-danger">*</span></label>
                    <div id="wordCountHint" class="small text-muted mb-2" style="display:none;"></div>
                    <div class="input-group input-group-lg">
                        <input type="text" id="answerInput" class="form-control text-center"
                               placeholder="اكتب إجابتك هنا..." maxlength="255" autocomplete="off">
                        <button class="btn btn-primary-custom" id="answerSubmitBtn">
                            <i class="fas fa-paper-plane me-1"></i>إرسال
                        </button>
                    </div>
                </div>

                <div id="answerFeedback" class="mt-3"></div>

                <div class="text-muted small mt-3" id="waitingAnswerMsg">
                    <i class="fas fa-spinner fa-spin me-1"></i>بانتظار اللاعبين الآخرين...
                </div>
            </div>

            <div id="votingPhase" class="card-custom p-4 text-center" style="display:none;">
                <div class="mb-2 d-flex justify-content-between align-items-center">
                    <span class="badge bg-info fs-6">التصويت</span>
                    <span id="voteTimer" class="badge bg-danger fs-6" style="display:none;">
                        <i class="fas fa-hourglass-half me-1"></i><span id="voteTimerValue">30</span>ث
                    </span>
                </div>
                <h5 class="mb-3">اختر الإجابة التي تعتقد أنها الصحيحة:</h5>
                <div id="answersList" class="row g-3 mb-4"></div>
                <div id="voteFeedback" class="mt-3"></div>
            </div>

            <div id="resultsPhase" class="card-custom p-4 text-center" style="display:none;">
                <h4 class="mb-2"><i class="fas fa-trophy text-warning me-2"></i>نتائج الجولة</h4>
                <p class="text-muted mb-3">
                    الإجابة الصحيحة: <strong class="text-success" id="resultsCorrectAnswer"></strong>
                </p>
                <div id="resultsList" class="row g-3 mb-4"></div>
                <button class="btn btn-primary-custom btn-lg" id="nextRoundBtn" style="display:none;">
                    <i class="fas fa-arrow-left me-2"></i>التالي
                </button>
                <div id="finalRoundMsg" class="alert alert-success border-0 rounded-3" style="display:none;">
                    <i class="fas fa-check-circle me-2"></i>انتهت جميع الجولات! جاري الانتقال إلى النتائج...
                </div>
            </div>

            <div class="text-center mt-3">
                <button class="btn btn-sm btn-outline-secondary" onclick="showHistory()" id="historyBtn" style="display:none;">
                    <i class="fas fa-history me-1"></i>الجولات السابقة
                </button>
            </div>

            <div id="errorBanner" class="alert alert-danger border-0 rounded-3" style="display:none;">
                <i class="fas fa-exclamation-triangle me-2"></i><span id="errorText"></span>
            </div>
        </div>
    </div>
</div>

{{-- History Modal --}}
<div class="modal fade" id="historyModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content" style="background:var(--bg-card);">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-history me-2 text-warning"></i>الجولات السابقة</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="historyBody">
                <div class="text-center py-4"><div class="spinner-border text-warning"></div></div>
            </div>
        </div>
    </div>
</div>
@endsection

<style>
    .badge.pulse {
        animation: pulse-anim 0.6s ease-in-out infinite alternate;
    }
    @keyframes pulse-anim {
        from { opacity: 1; transform: scale(1); }
        to { opacity: 0.6; transform: scale(1.05); }
    }
</style>

@push('scripts')
<script>
    const code = '{{ $game->code }}';
    const isSpectator = {{ ($isSpectator ?? false) ? 'true' : 'false' }};
    let currentRoundId = null;
    let pollInterval = null;
    let advanceTimer = null;
    let prevStatus = null;
    let prevRoundNumber = null;
    let resultsPhaseStart = null;
    let soundEnabled = localStorage.getItem('bluff_sound') !== 'off';
    let answerCountdown = null;
    let voteCountdown = null;
    let questionDuration = 30;
    let shuffledAnswerIds = null;

    // XSS escape
    function esc(str) {
        const d = document.createElement('div');
        d.textContent = str;
        return d.innerHTML;
    }

    // Sound
    function playSound(name) {
        if (!soundEnabled) return;
        const sounds = {
            tick: 'https://www.soundjay.com/buttons/sounds/button-09.mp3',
            correct: 'https://www.soundjay.com/misc/sounds/bell-ringing-05.mp3',
            vote: 'https://www.soundjay.com/buttons/sounds/button-10.mp3',
            finish: 'https://www.soundjay.com/misc/sounds/magic-chime-02.mp3',
        };
        if (sounds[name]) {
            try { new Audio(sounds[name]).play(); } catch(e) {}
        }
    }

    function startAnswerTimer(duration) {
        clearInterval(answerCountdown);
        const el = document.getElementById('answerTimerValue');
        const container = document.getElementById('answerTimer');
        let remaining = duration;
        el.textContent = remaining;
        container.style.display = 'inline-block';
        container.className = 'badge bg-danger fs-6';
        answerCountdown = setInterval(() => {
            remaining--;
            el.textContent = remaining;
            if (remaining <= 5) container.className = 'badge bg-danger fs-6 pulse';
            if (remaining <= 0) {
                clearInterval(answerCountdown);
                container.style.display = 'none';
                const btn = document.getElementById('answerSubmitBtn');
                const input = document.getElementById('answerInput');
                if (!btn.disabled && input.value.trim()) {
                    submitAnswer();
                } else if (!btn.disabled) {
                    btn.disabled = true;
                    document.getElementById('answerFeedback').innerHTML =
                        '<div class="alert alert-danger border-0 rounded-3 py-2"><i class="fas fa-clock me-2"></i>انتهى الوقت!</div>';
                }
            }
        }, 1000);
    }

    function startVoteTimer(duration) {
        clearInterval(voteCountdown);
        const el = document.getElementById('voteTimerValue');
        const container = document.getElementById('voteTimer');
        let remaining = duration;
        el.textContent = remaining;
        container.style.display = 'inline-block';
        container.className = 'badge bg-danger fs-6';
        voteCountdown = setInterval(() => {
            remaining--;
            el.textContent = remaining;
            if (remaining <= 5) container.className = 'badge bg-danger fs-6 pulse';
            if (remaining <= 0) {
                clearInterval(voteCountdown);
                container.style.display = 'none';
                const btns = document.querySelectorAll('.vote-btn');
                if (btns.length > 0 && !btns[0].disabled) {
                    btns.forEach(b => b.disabled = true);
                    document.getElementById('voteFeedback').innerHTML =
                        '<div class="alert alert-danger border-0 rounded-3"><i class="fas fa-clock me-2"></i>انتهى الوقت! لم تتمكن من التصويت.</div>';
                }
            }
        }, 1000);
    }

    function stopTimers() {
        clearInterval(answerCountdown);
        answerCountdown = null;
        clearInterval(voteCountdown);
        voteCountdown = null;
        document.getElementById('answerTimer').style.display = 'none';
        document.getElementById('voteTimer').style.display = 'none';
    }

    function getState() {
        return fetch('/bluff/' + code + '/state?_=' + Date.now())
            .then(r => { if (!r.ok) throw new Error('HTTP ' + r.status); return r.json(); });
    }

    function startPolling() {
        document.getElementById('loadingIndicator').style.display = 'block';
        poll();
        pollInterval = setInterval(poll, 1500);
    }

    let polling = false;
    function poll() {
        if (polling) return;
        polling = true;
        getState().then(data => {
            document.getElementById('loadingIndicator').style.display = 'none';
            renderState(data);
        }).catch(() => {
            document.getElementById('loadingIndicator').style.display = 'none';
        }).finally(() => { polling = false; });
    }

    function renderState(data) {
        if (data.status === 'finished') {
            window.location.href = '/bluff/' + code + '/results';
            return;
        }

        const round = data.current_round_data;
        if (!round) {
            document.getElementById('loadingIndicator').style.display = 'block';
            return;
        }

        document.getElementById('loadingIndicator').style.display = 'none';
        if (round.round_number !== prevRoundNumber) {
            prevRoundNumber = round.round_number;
            resultsPhaseStart = null;
            shuffledAnswerIds = null;
            stopTimers();
            // Reset answer input for a new round's answering phase
            if (round.status === 'answering') {
                const inp = document.getElementById('answerInput');
                const sbtn = document.getElementById('answerSubmitBtn');
                inp.style.display = ''; inp.disabled = false; inp.value = '';
                sbtn.style.display = ''; sbtn.disabled = false;
                document.getElementById('answerFeedback').innerHTML = '';
                document.getElementById('answerSection').style.display = 'block';
            }
        } else if (prevStatus !== round.status && round.status === 'answering') {
            // Transition from selecting/voting to answering within same round (edge case)
            const inp = document.getElementById('answerInput');
            const sbtn = document.getElementById('answerSubmitBtn');
            inp.style.display = ''; inp.disabled = false; inp.value = '';
            sbtn.style.display = ''; sbtn.disabled = false;
            document.getElementById('answerFeedback').innerHTML = '';
            document.getElementById('answerSection').style.display = 'block';
        }
        if (data.question_duration) {
            questionDuration = data.question_duration;
        }
        currentRoundId = round.id;
        document.getElementById('roundLabel').textContent = '\u062C\u0648\u0644\u0629 ' + round.round_number + '/' + data.total_rounds;
        renderScores(data);

        document.getElementById('historyBtn').style.display = data.current_round > 1 ? 'inline-block' : 'none';

        if (isSpectator) {
            document.getElementById('spectatorBanner').style.display = 'block';
        }

        if (prevStatus !== round.status) {
            if (round.status === 'voting') playSound('tick');
            if (round.status === 'finished') playSound('correct');
            prevStatus = round.status;
        }

        if (round.status === 'selecting') {
            showSelectingPhase(round, data);
        } else if (round.status === 'answering') {
            showQuestionPhase(round, data);
        } else if (round.status === 'voting') {
            showVotingPhase(round, data);
        } else if (round.status === 'finished') {
            showResultsPhase(round, data);
        }
    }

    function renderScores(data) {
        const bar = document.getElementById('scoresBar');
        if (!data.scores || data.scores.length === 0) return;
        bar.innerHTML = '<div class="d-flex justify-content-center gap-4 flex-wrap">' +
            data.scores.map(s =>
                `<div class="text-center px-3 py-2 rounded-3" style="background:var(--bg-card);border:1px solid var(--border);">
                    <div class="small fw-bold">${esc(s.player_name)}</div>
                    <div class="fw-bold text-warning fs-5">${esc('' + s.total_score)}</div>
                </div>`
            ).join('') + '</div>';
    }

    function showSelectingPhase(round, data) {
        document.getElementById('selectingPhase').style.display = 'block';
        document.getElementById('questionPhase').style.display = 'none';
        document.getElementById('votingPhase').style.display = 'none';
        document.getElementById('resultsPhase').style.display = 'none';

        const msg = document.getElementById('selectingMsg');
        const subMsg = document.getElementById('selectingSubMsg');
        const list = document.getElementById('categoriesList');
        const fb = document.getElementById('selectFeedback');

        if (round.is_selector && !isSpectator) {
            msg.textContent = 'حان دورك! اختر فقرة للسؤال';
            subMsg.textContent = '';
            fb.innerHTML = '';

            const cats = round.available_categories || [];
            if (cats.length === 0) {
                list.innerHTML = '<div class="col-12"><div class="alert alert-warning border-0 rounded-3">لا توجد فقرات متاحة</div></div>';
                return;
            }

            list.innerHTML = cats.map(c =>
                `<div class="col-md-6">
                    <button class="btn btn-outline-primary w-100 p-3 fw-bold cat-btn"
                            style="border-radius:12px; font-size:1.1rem; white-space:normal; height:100%;"
                            onclick="selectCategory(${c.id})">
                        ${esc(c.name)}
                        <br><small class="text-muted">${c.remaining_questions} أسئلة</small>
                    </button>
                </div>`
            ).join('');
        } else {
            msg.textContent = 'بانتظار اختيار الفقرة...';
            subMsg.textContent = round.selector_name + ' يختار الفقرة الآن';
            list.innerHTML = '';
            fb.innerHTML = '';
        }
    }

    function selectCategory(categoryId) {
        const btns = document.querySelectorAll('.cat-btn');
        btns.forEach(b => b.disabled = true);
        document.getElementById('selectFeedback').innerHTML =
            '<div class="alert alert-info border-0 rounded-3"><i class="fas fa-spinner fa-spin me-2"></i>جاري اختيار الفقرة...</div>';

        fetch('/bluff/' + code + '/round/' + currentRoundId + '/select-category', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ category_id: categoryId })
        })
        .then(r => r.json())
        .then(result => {
            if (result.status === 'ok') {
                document.getElementById('selectFeedback').innerHTML =
                    '<div class="alert alert-success border-0 rounded-3"><i class="fas fa-check me-2"></i>تم اختيار الفقرة! جاري تحميل السؤال...</div>';
                setTimeout(() => { polling = false; poll(); }, 500);
            } else {
                document.getElementById('selectFeedback').innerHTML =
                    '<div class="alert alert-danger border-0 rounded-3"><i class="fas fa-exclamation me-2"></i>' + esc(result.message) + '</div>';
                btns.forEach(b => b.disabled = false);
            }
        })
        .catch(() => {
            document.getElementById('selectFeedback').innerHTML =
                '<div class="alert alert-danger border-0 rounded-3">حدث خطأ، حاول مرة أخرى</div>';
            btns.forEach(b => b.disabled = false);
        });
    }

    function showQuestionPhase(round, data) {
        document.getElementById('selectingPhase').style.display = 'none';
        document.getElementById('questionPhase').style.display = 'block';
        document.getElementById('votingPhase').style.display = 'none';
        document.getElementById('resultsPhase').style.display = 'none';

        document.getElementById('roundBadge').textContent = '\u062C\u0648\u0644\u0629 ' + round.round_number;
        document.getElementById('questionText').textContent = round.question_text || '\u0627\u0644\u0633\u0624\u0627\u0644 \u063A\u064A\u0631 \u0645\u062A\u0648\u0641\u0631';

        // Show answer type hint
        const wcHint = document.getElementById('wordCountHint');
        if (round.correct_answer_is_numeric) {
            wcHint.textContent = '\u0627\u0644\u0625\u062C\u0627\u0628\u0629 \u0631\u0642\u0645';
            wcHint.style.display = 'block';
        } else {
            const wc = round.correct_answer_word_count || 0;
            if (wc >= 1) {
                let label;
                if (wc === 1) label = '\u0643\u0644\u0645\u0629 \u0648\u0627\u062D\u062F\u0629';
                else if (wc === 2) label = '\u0643\u0644\u0645\u062A\u064A\u0646';
                else label = wc + ' \u0643\u0644\u0645\u0627\u062A';
                wcHint.textContent = '\u0627\u0644\u0625\u062C\u0627\u0628\u0629 \u062A\u062A\u0643\u0648\u0646 \u0645\u0646 ' + label;
                wcHint.style.display = 'block';
            } else {
                wcHint.style.display = 'none';
            }
        }

        const hasAnswered = !!round.has_answered;
        const input = document.getElementById('answerInput');
        const submitBtn = document.getElementById('answerSubmitBtn');
        const feedback = document.getElementById('answerFeedback');
        const waitingMsg = document.getElementById('waitingAnswerMsg');
        const ansSection = document.getElementById('answerSection');

        if (isSpectator || hasAnswered) {
            ansSection.style.display = 'none';
            document.getElementById('answerTimer').style.display = 'none';
            clearInterval(answerCountdown);
            answerCountdown = null;
            if (hasAnswered) {
                feedback.innerHTML = '<div class="alert alert-success border-0 rounded-3"><i class="fas fa-check me-2"></i>\u062A\u0645 \u0625\u0631\u0633\u0627\u0644 \u0625\u062C\u0627\u0628\u062A\u0643</div>';
            }
            waitingMsg.style.display = isSpectator ? 'none' : 'block';
        } else if (round.answer_time_expired) {
            ansSection.style.display = 'block';
            input.disabled = true;
            submitBtn.disabled = true;
            document.getElementById('answerTimer').style.display = 'none';
            waitingMsg.style.display = 'none';
        } else {
            ansSection.style.display = 'block';
            feedback.innerHTML = '';
            waitingMsg.style.display = 'none';
            if (answerCountdown === null) {
                startAnswerTimer(questionDuration);
            }
            setTimeout(() => input.focus(), 100);
        }

        if (hasAnswered && !isSpectator) {
            const waitingCount = round.waiting_for_answers || 0;
            waitingMsg.innerHTML = waitingCount > 0
                ? '<i class="fas fa-spinner fa-spin me-1"></i>\u0628\u0627\u0646\u062A\u0638\u0627\u0631 ' + waitingCount + ' \u0644\u0627\u0639\u0628\u064A\u0646 \u0622\u062E\u0631\u064A\u0646...'
                : '<i class="fas fa-spinner fa-spin me-1"></i>\u062C\u0627\u0631\u064A \u062A\u062C\u0647\u064A\u0632 \u0627\u0644\u062A\u0635\u0648\u064A\u062A...';
        }
    }

    function shuffleArray(arr) {
        for (let i = arr.length - 1; i > 0; i--) {
            const j = Math.floor(Math.random() * (i + 1));
            [arr[i], arr[j]] = [arr[j], arr[i]];
        }
        return arr;
    }

    function showVotingPhase(round, data) {
        document.getElementById('selectingPhase').style.display = 'none';
        document.getElementById('questionPhase').style.display = 'none';
        document.getElementById('votingPhase').style.display = 'block';
        document.getElementById('resultsPhase').style.display = 'none';

        const list = document.getElementById('answersList');
        let answers = round.display_answers || [];

        if (shuffledAnswerIds === null) {
            shuffledAnswerIds = shuffleArray(answers.map(a => a.id));
        }
        const idOrder = new Map(shuffledAnswerIds.map((id, i) => [id, i]));
        answers = [...answers].sort((a, b) => (idOrder.get(a.id) ?? 0) - (idOrder.get(b.id) ?? 0));

        if (round.has_voted || isSpectator) {
            document.getElementById('voteTimer').style.display = 'none';
            clearInterval(voteCountdown);
            voteCountdown = null;
            list.innerHTML = '<div class="col-12"><div class="alert alert-success border-0 rounded-3"><i class="fas fa-check me-2"></i>' +
                (isSpectator ? '\u0627\u0646\u062A\u0638\u0631 \u0646\u062A\u0627\u0626\u062C \u0627\u0644\u062A\u0635\u0648\u064A\u062A...' : '\u0644\u0642\u062F \u0635\u0648\u062A \u0628\u0627\u0644\u0641\u0639\u0644\u060C \u0627\u0646\u062A\u0638\u0631 \u0628\u0627\u0642\u064A \u0627\u0644\u0644\u0627\u0639\u0628\u064A\u0646...') +
                '</div></div>';
            return;
        }

        if (answers.length === 0) {
            document.getElementById('voteTimer').style.display = 'none';
            clearInterval(voteCountdown);
            voteCountdown = null;
            list.innerHTML = '<div class="col-12"><div class="alert alert-warning border-0 rounded-3">\u0644\u0627 \u062A\u0648\u062C\u062F \u0625\u062C\u0627\u0628\u0627\u062A \u0644\u0644\u0639\u0631\u0636</div></div>';
            return;
        }

        if (round.vote_time_expired) {
            document.getElementById('voteTimer').style.display = 'none';
            list.innerHTML = '<div class="col-12"><div class="alert alert-danger border-0 rounded-3"><i class="fas fa-clock me-2"></i>\u0627\u0646\u062A\u0647\u0649 \u0627\u0644\u0648\u0642\u062A! \u0644\u0645 \u062A\u062A\u0645\u0643\u0646 \u0645\u0646 \u0627\u0644\u062A\u0635\u0648\u064A\u062A.</div></div>';
            return;
        }

        if (voteCountdown === null) {
            startVoteTimer(questionDuration);
        }

        list.innerHTML = answers.map(a => {
            const countLabel = a.playercount > 1
                ? ' <span class="badge bg-warning text-dark">' + a.playercount + ' لاعبين</span>'
                : '';
            return `<div class="col-md-6">
                <button class="btn btn-outline-warning w-100 p-3 fw-bold vote-btn" data-id="${a.id}"
                        style="border-radius:12px; font-size:1.1rem; white-space:normal; height:100%;"
                        onclick="castVote(${a.id})">
                    ${esc(a.answer_text)}${countLabel}
                </button>
            </div>`;
        }).join('');
    }

    function castVote(answerId) {
        const btns = document.querySelectorAll('.vote-btn');
        btns.forEach(b => b.disabled = true);
        document.getElementById('voteFeedback').innerHTML =
            '<div class="alert alert-info border-0 rounded-3"><i class="fas fa-spinner fa-spin me-2"></i>\u062C\u0627\u0631\u064A \u062A\u0633\u062C\u064A\u0644 \u0635\u0648\u062A\u0643...</div>';

        fetch('/bluff/' + code + '/round/' + currentRoundId + '/vote', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ answer_id: answerId })
        })
        .then(r => r.json())
        .then(result => {
            if (result.status === 'ok') {
                playSound('vote');
                document.getElementById('voteFeedback').innerHTML =
                    '<div class="alert alert-success border-0 rounded-3"><i class="fas fa-check me-2"></i>\u062A\u0645 \u062A\u0633\u062C\u064A\u0644 \u0635\u0648\u062A\u0643!</div>';
                btns.forEach(b => b.style.display = 'none');
            } else {
                document.getElementById('voteFeedback').innerHTML =
                    '<div class="alert alert-danger border-0 rounded-3"><i class="fas fa-exclamation me-2"></i>' + esc(result.message) + '</div>';
                btns.forEach(b => b.disabled = false);
            }
        })
        .catch(() => {
            document.getElementById('voteFeedback').innerHTML =
                '<div class="alert alert-danger border-0 rounded-3">\u062D\u062F\u062B \u062E\u0637\u0623\u060C \u062D\u0627\u0648\u0644 \u0645\u0631\u0629 \u0623\u062E\u0631\u0649</div>';
            btns.forEach(b => b.disabled = false);
        });
    }

    function showResultsPhase(round, data) {
        document.getElementById('selectingPhase').style.display = 'none';
        document.getElementById('questionPhase').style.display = 'none';
        document.getElementById('votingPhase').style.display = 'none';
        document.getElementById('resultsPhase').style.display = 'block';
        stopTimers();

        document.getElementById('resultsCorrectAnswer').textContent = round.correct_answer_text || '\u2014';

        const list = document.getElementById('resultsList');
        const results = round.results || [];

        if (results.length === 0) {
            list.innerHTML = '<div class="col-12"><p class="text-muted">\u0644\u0627 \u062A\u0648\u062C\u062F \u0646\u062A\u0627\u0626\u062C</p></div>';
        } else {
            // Calculate total points per player for this round
            const pointsMap = {};
            results.forEach(r => {
                if (r.is_real_fake) {
                    const pts = (r.vote_count || 0) * 2;
                    (r.voters || []).forEach(v => {
                        pointsMap[v] = (pointsMap[v] || 0) + 2;
                    });
                } else {
                    const pts = (r.vote_count || 0);
                    if (pts > 0 && r.authors) {
                        r.authors.forEach(a => {
                            pointsMap[a] = (pointsMap[a] || 0) + (r.vote_count || 0);
                        });
                    }
                }
            });

            list.innerHTML = results.map(r => {
                const isCorrect = r.is_real_fake;

                if (isCorrect) {
                    const voteCount = r.vote_count || 0;
                    const totalPts = voteCount * 2;

                    return `<div class="col-12 mb-3">
                        <div class="card-custom p-3 text-center" style="border:2px solid var(--success); background:rgba(40,167,69,0.08);">
                            <div class="d-flex align-items-center justify-content-center gap-2 mb-2">
                                <span class="badge bg-success fs-6">
                                    <i class="fas fa-check-circle me-1"></i>\u0627\u0644\u0625\u062C\u0627\u0628\u0629 \u0627\u0644\u0635\u062D\u064A\u062D\u0629
                                </span>
                            </div>
                            <div class="fw-bold mb-2" style="font-size:1.4rem; color:var(--success);">${esc(r.answer_text)}</div>
                            <div class="small text-muted mb-2">\u0643\u0627\u062A\u0628: ${esc(r.author_name || '\u0627\u0644\u0646\u0638\u0627\u0645')}</div>
                            ${voteCount > 0 ? `
                                <div class="d-flex align-items-center justify-content-center gap-2 flex-wrap mb-2">
                                    <span class="badge bg-success fs-6"><i class="fas fa-users me-1"></i>${voteCount} \u0635\u0648\u062A</span>
                                    <span class="badge bg-warning text-dark fs-6"><i class="fas fa-star me-1"></i>${voteCount} \u00D7 2 = ${totalPts} \u0646\u0642\u0637\u0629</span>
                                </div>
                                <div class="small">\u0627\u0644\u0645\u0635\u0648\u062A\u0648\u0646: ${r.voters.map(v => '<span class="badge bg-dark me-1">' + esc(v) + '</span>').join('')}</div>
                            ` : '<div class="text-muted small"><i class="fas fa-times me-1"></i>\u0644\u0645 \u064A\u062E\u062A\u0631\u0647\u0627 \u0623\u062D\u062F</div>'}
                        </div>
                    </div>`;
                }

                const voteCount = r.vote_count || 0;
                const authors = r.authors && r.authors.length > 0
                    ? r.authors.map(v => esc(v)).join('\u060C ')
                    : esc(r.author_name || '\u2014');
                const authorCount = r.author_count || 1;
                const totalPts = voteCount * authorCount;

                return `<div class="col-md-6 mb-3">
                    <div class="card-custom p-3 text-center h-100" style="border:1px solid var(--border);">
                        <div class="d-flex align-items-center justify-content-center gap-2 mb-2">
                            <span class="badge bg-secondary"><i class="fas fa-times-circle me-1"></i>\u0625\u062C\u0627\u0628\u0629 \u0645\u0632\u064A\u0641\u0629</span>
                            ${authorCount > 1 ? '<span class="badge bg-warning text-dark">' + authorCount + ' \u0643\u062A\u0627\u0628</span>' : ''}
                        </div>
                        <div class="fw-bold mb-1" style="font-size:1.2rem;">${esc(r.answer_text)}</div>
                        <div class="small text-muted mb-2">\u0643\u062A\u0628: ${authors}</div>
                        ${voteCount > 0 ? `
                            <div class="d-flex align-items-center justify-content-center gap-2 flex-wrap mb-2">
                                <span class="badge bg-danger fs-6"><i class="fas fa-thumbs-up me-1"></i>${voteCount} \u0635\u0648\u062A</span>
                                <span class="badge bg-warning text-dark fs-6"><i class="fas fa-gem me-1"></i>+${totalPts} \u0646\u0642\u0637\u0629</span>
                            </div>
                            <div class="small">\u062E\u064F\u062F\u0639\u0648\u0627 \u0628\u0647\u0627: ${r.voters.map(v => '<span class="badge bg-dark me-1">' + esc(v) + '</span>').join('')}</div>
                        ` : '<div class="text-muted small"><i class="fas fa-ban me-1"></i>\u0644\u0645 \u064A\u062E\u062A\u0631\u0647\u0627 \u0623\u062D\u062F</div>'}
                    </div>
                </div>`;
            }).join('');
        }

        const nextBtn = document.getElementById('nextRoundBtn');
        const finalMsg = document.getElementById('finalRoundMsg');

        if (data.current_round >= data.total_rounds) {
            finalMsg.style.display = 'block';
            nextBtn.style.display = 'none';
            playSound('finish');
            setTimeout(() => {
                window.location.href = '/bluff/' + code + '/results';
            }, 3000);
        } else if (data.is_creator && !isSpectator) {
            if (resultsPhaseStart === null) {
                resultsPhaseStart = Date.now();
            }
            const elapsed = (Date.now() - resultsPhaseStart) / 1000;
            const remaining = Math.max(0, 8 - Math.floor(elapsed));

            nextBtn.style.display = 'inline-block';
            nextBtn.innerHTML = '<i class="fas fa-arrow-left me-2"></i>\u0627\u0644\u062A\u0627\u0644\u064A (' + remaining + ')';
            nextBtn.disabled = false;

            if (remaining <= 0 && !nextBtn.dataset.advancing) {
                nextBtn.dataset.advancing = '1';
                nextBtn.disabled = true;
                nextBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>\u062C\u0627\u0631\u064A...';
                fetch('/bluff/' + code + '/advance', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
                })
                .then(r => r.json())
                .then(d => {
                    if (d.redirect) { window.location.href = d.redirect; return; }
                    polling = false;
                    poll();
                })
                .catch(() => {
                    nextBtn.disabled = false;
                    nextBtn.dataset.advancing = '';
                    nextBtn.innerHTML = '<i class="fas fa-arrow-left me-2"></i>\u0627\u0644\u062A\u0627\u0644\u064A';
                });
                return;
            }

            nextBtn.onclick = function() {
                this.dataset.advancing = '1';
                this.disabled = true;
                this.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>\u062C\u0627\u0631\u064A...';
                fetch('/bluff/' + code + '/advance', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
                })
                .then(r => r.json())
                .then(d => {
                    if (d.redirect) { window.location.href = d.redirect; return; }
                    polling = false;
                    poll();
                })
                .catch(() => {
                    this.disabled = false;
                    this.dataset.advancing = '';
                    this.innerHTML = '<i class="fas fa-arrow-left me-2"></i>\u0627\u0644\u062A\u0627\u0644\u064A';
                });
            };
        } else {
            if (resultsPhaseStart === null) {
                resultsPhaseStart = Date.now();
            }
            const elapsed = (Date.now() - resultsPhaseStart) / 1000;

            if (elapsed > 20 && !nextBtn.dataset.advancing) {
                nextBtn.dataset.advancing = '1';
                fetch('/bluff/' + code + '/advance', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
                })
                .then(r => r.json())
                .then(d => {
                    if (d.redirect) { window.location.href = d.redirect; return; }
                    polling = false;
                    poll();
                })
                .catch(() => { nextBtn.dataset.advancing = ''; });
                return;
            }

            nextBtn.style.display = 'none';
            finalMsg.innerHTML = '<i class="fas fa-clock me-2"></i>\u0628\u0627\u0646\u062A\u0638\u0627\u0631 \u0627\u0644\u062A\u0642\u062F\u0645 \u0644\u0644\u062C\u0648\u0644\u0629 \u0627\u0644\u062A\u0627\u0644\u064A\u0629...';
            finalMsg.className = 'alert alert-info border-0 rounded-3';
            finalMsg.style.display = 'block';
        }
    }

    function showHistory() {
        const modal = new bootstrap.Modal(document.getElementById('historyModal'));
        const body = document.getElementById('historyBody');
        body.innerHTML = '<div class="text-center py-4"><div class="spinner-border text-warning"></div></div>';
        modal.show();

        fetch('/bluff/' + code + '/history')
            .then(r => r.json())
            .then(data => {
                if (!data.rounds || data.rounds.length === 0) {
                    body.innerHTML = '<p class="text-muted text-center py-4">\u0644\u0627 \u062A\u0648\u062C\u062F \u062C\u0648\u0644\u0627\u062A \u0633\u0627\u0628\u0642\u0629</p>';
                    return;
                }
                body.innerHTML = data.rounds.map(r => {
                    const answersHtml = r.answers.map(a => {
                        const authorLabel = a.is_real_fake
                            ? '<span class="badge bg-success">\u0635\u062D</span>'
                            : (a.author_count > 1
                                ? '<span class="badge bg-warning text-dark ms-1">' + a.author_count + ' \u0643\u062A\u0627\u0628</span>'
                                : '');
                        return `<div class="d-flex justify-content-between align-items-center p-2 rounded-3 mb-1 ${a.is_real_fake ? 'border border-success' : ''}" style="background:var(--bg-card);">
                            <div>
                                <span class="fw-bold">${esc(a.answer_text)}</span>
                                ${authorLabel}
                                <div class="small text-muted">${esc(a.author_name)}</div>
                            </div>
                            <div class="text-end small">
                                ${a.vote_count > 0 ? a.vote_count + ' \u0635\u0648\u062A' : '\u0644\u0645 \u064A\u062E\u062A\u0631\u0647\u0627 \u0623\u062D\u062F'}
                                ${a.voters.length > 0 ? '<br><span class="text-muted">' + a.voters.map(v => esc(v)).join(', ') + '</span>' : ''}
                            </div>
                        </div>`;
                    }).join('');

                    return `<div class="mb-4">
                        <h6 class="fw-bold text-warning mb-2">\u062C\u0648\u0644\u0629 ${r.round_number}: ${esc(r.question_text)}</h6>
                        <div class="small text-muted mb-2">\u0627\u0644\u0625\u062C\u0627\u0628\u0629 \u0627\u0644\u0635\u062D\u064A\u062D\u0629: <strong class="text-success">${esc(r.correct_answer)}</strong></div>
                        ${answersHtml}
                    </div>`;
                }).join('');
            })
            .catch(() => {
                body.innerHTML = '<div class="alert alert-danger border-0 rounded-3">\u062D\u062F\u062B \u062E\u0637\u0623 \u0641\u064A \u062A\u062D\u0645\u064A\u0644 \u0627\u0644\u062A\u0627\u0631\u064A\u062E</div>';
            });
    }

    document.getElementById('answerSubmitBtn')?.addEventListener('click', submitAnswer);
    document.getElementById('answerInput')?.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') submitAnswer();
    });

    function submitAnswer() {
        const input = document.getElementById('answerInput');
        const btn = document.getElementById('answerSubmitBtn');
        const feedback = document.getElementById('answerFeedback');
        const answer = input.value.trim();

        if (!answer || answer.length < 2) {
            feedback.innerHTML = '<div class="alert alert-danger border-0 rounded-3 py-2">\u0627\u0644\u0625\u062C\u0627\u0628\u0629 \u064A\u062C\u0628 \u0623\u0646 \u062A\u0643\u0648\u0646 \u0639\u0644\u0649 \u0627\u0644\u0623\u0642\u0644 \u062D\u0631\u0641\u064A\u0646</div>';
            return;
        }

        if (!currentRoundId) {
            feedback.innerHTML = '<div class="alert alert-danger border-0 rounded-3 py-2">\u062E\u0637\u0623: \u0644\u0645 \u064A\u062A\u0645 \u062A\u062D\u0645\u064A\u0644 \u0627\u0644\u062C\u0648\u0644\u0629 \u0628\u0639\u062F</div>';
            return;
        }

        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>\u062C\u0627\u0631\u064A...';
        feedback.innerHTML = '';

        fetch('/bluff/' + code + '/round/' + currentRoundId + '/answer', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ answer: answer })
        })
        .then(r => r.json())
        .then(result => {
            if (result.status === 'ok') {
                feedback.innerHTML = '<div class="alert alert-success border-0 rounded-3"><i class="fas fa-check me-2"></i>\u062A\u0645 \u062D\u0641\u0638 \u0625\u062C\u0627\u0628\u062A\u0643! \u0627\u0646\u062A\u0638\u0631 \u0627\u0644\u0644\u0627\u0639\u0628\u064A\u0646 \u0627\u0644\u0622\u062E\u0631\u064A\u0646...</div>';
                input.style.display = 'none';
                btn.style.display = 'none';
                document.getElementById('waitingAnswerMsg').style.display = 'block';
                document.getElementById('waitingAnswerMsg').innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>\u0628\u0627\u0646\u062A\u0638\u0627\u0631 \u0627\u0644\u0644\u0627\u0639\u0628\u064A\u0646 \u0627\u0644\u0622\u062E\u0631\u064A\u0646...';
            } else if (result.status === 'correct_answer_rejected') {
                feedback.innerHTML = '<div class="alert alert-warning border-0 rounded-3"><i class="fas fa-star me-2"></i>' + esc(result.message) + '</div>';
                input.value = '';
                input.focus();
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-paper-plane me-1"></i>\u0625\u0631\u0633\u0627\u0644';
            } else {
                feedback.innerHTML = '<div class="alert alert-danger border-0 rounded-3">' + esc(result.message) + '</div>';
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-paper-plane me-1"></i>\u0625\u0631\u0633\u0627\u0644';
            }
        })
        .catch(() => {
            feedback.innerHTML = '<div class="alert alert-danger border-0 rounded-3">\u062D\u062F\u062B \u062E\u0637\u0623 \u0641\u064A \u0627\u0644\u0627\u062A\u0635\u0627\u0644\u060C \u062D\u0627\u0648\u0644 \u0645\u0631\u0629 \u0623\u062E\u0631\u0649</div>';
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-paper-plane me-1"></i>\u0625\u0631\u0633\u0627\u0644';
        });
    }

    startPolling();
</script>
@endpush
