@extends('App')
@section('title', 'لوحة اللعبة')
@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold"><i class="fas fa-gamepad text-warning me-2"></i>لوحة اللعبة</h4>
        <div class="d-flex align-items-center gap-3">
            @foreach($session->teams as $team)
            <div class="text-center" style="color:{{ $team->color }}">
                <strong>{{ $team->name }}</strong>
                <div class="fw-bold" style="font-size:1.5rem">{{ $team->score }}</div>
            </div>
            @if(!$loop->last) <span class="text-muted">vs</span> @endif
            @endforeach
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered text-center align-middle" style="border-color:var(--border)">
            <thead>
                <tr>
                    <th style="background:var(--bg-card);min-width:150px">الفئة</th>
                    <th style="background:var(--bg-card);min-width:120px"><span class="badge badge-points-250">250</span></th>
                    <th style="background:var(--bg-card);min-width:120px"><span class="badge badge-points-500">500</span></th>
                    <th style="background:var(--bg-card);min-width:120px"><span class="badge badge-points-750">750</span></th>
                </tr>
            </thead>
            <tbody>
                @foreach($board as $row)
                <tr>
                    <td class="fw-bold" style="background:var(--bg-card)">{{ $row['category']->name }}</td>
                    @foreach([250,500,750] as $pts)
                    <td class="p-2">
                        @php $rounds = $row['rounds'][$pts] @endphp
                        @foreach($rounds as $r)
                            @if($r->status === 'answered')
                                <div class="py-1 {{ $r->answer?->is_correct ? 'text-success' : 'text-danger' }}">
                                    <i class="fas {{ $r->answer?->is_correct ? 'fa-check-circle' : 'fa-times-circle' }}"></i>
                                </div>
                            @elseif($r->status === 'stolen')
                                <div class="py-1 text-info"><i class="fas fa-skull"></i></div>
                            @else
                                <div class="py-1">
                                    <span class="badge bg-secondary fw-bold" style="cursor:pointer" onclick="openQuestion({{ $r->id }})">?</span>
                                </div>
                            @endif
                        @endforeach
                    </td>
                    @endforeach
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="card-custom p-3 mt-3">
        <div class="d-flex justify-content-around">
            <span class="text-muted">الدور: <strong style="color:{{ $session->currentTeam?->color }}">{{ $session->currentTeam?->name }}</strong></span>
            <span class="text-muted">{{ $session->answered_questions }} / {{ $session->total_questions }}</span>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function openQuestion(roundId) {
        const code = '{{ $session->code }}';
        fetch(`/game/${code}/round/${roundId}/question`)
            .then(r => r.json())
            .then(data => {
                if (data.error) { alert(data.error); return; }
                showQuestionModal(data);
            });
    }

    function showQuestionModal(data) {
        const existing = document.getElementById('questionModal');
        if (existing) existing.remove();

        const modal = document.createElement('div');
        modal.id = 'questionModal';
        modal.className = 'modal fade';
        modal.setAttribute('tabindex', '-1');
        modal.innerHTML = `
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content" style="background:var(--bg-card);border:1px solid var(--border)">
                    <div class="modal-header border-0">
                        <span class="badge badge-points-${data.round.points_value}">${data.round.points_value} نقطة</span>
                        <span class="text-muted">${data.category}</span>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body text-center p-4">
                        ${data.question.image ? `<img src="${data.question.image}" class="img-fluid mb-3 rounded" style="max-height:200px">` : ''}
                        <h4 class="fw-bold mb-4">${data.question.text}</h4>
                        <div class="row g-3">
                            ${Object.entries(data.question.answers).map(([k,v]) => `
                                <div class="col-md-6">
                                    <button class="btn btn-outline-light w-100 p-3 answer-btn" data-answer="${k}" onclick="submitAnswer(${data.round.id}, '${k}')">
                                        <strong>${k.toUpperCase()}:</strong> ${v}
                                    </button>
                                </div>
                            `).join('')}
                        </div>
                    </div>
                </div>
            </div>
        `;
        document.body.appendChild(modal);
        new bootstrap.Modal(modal).show();
    }

    function submitAnswer(roundId, answer) {
        const code = '{{ $session->code }}';
        const teamId = @json($session->current_team_id);
        if (teamId === null) { alert('لا يوجد فريق محدد'); return; }

        document.querySelectorAll('.answer-btn').forEach(b => b.disabled = true);

        fetch(`/game/${code}/round/${roundId}/answer`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify({ answer, team_id: teamId, time_taken: 0 })
        })
        .then(r => r.json())
        .then(data => {
            if (data.error) { alert(data.error); return; }
            const isCorrect = data.is_correct;
            const correctAns = data.correct_answer;
            document.querySelectorAll('.answer-btn').forEach(b => {
                if (b.dataset.answer === correctAns) b.className = 'btn btn-success w-100 p-3';
                else if (b.dataset.answer === answer && !isCorrect) b.className = 'btn btn-danger w-100 p-3';
                else b.className = 'btn btn-outline-secondary w-100 p-3';
            });
            setTimeout(() => {
                bootstrap.Modal.getInstance(document.getElementById('questionModal'))?.hide();
                location.reload();
            }, 2000);
        });
    }
</script>
@endpush
@endsection
