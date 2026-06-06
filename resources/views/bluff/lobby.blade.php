@extends('App')

@section('title', 'انتظار اللاعبين - Face Off')
@section('content')
<div class="page-header">
    <div class="container text-center">
        <h1 class="mb-0"><i class="fas fa-gamepad me-2 text-warning"></i>Face Off</h1>
    </div>
</div>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="card-custom p-5 text-center">
                <p class="text-muted mb-1">كود اللعبة</p>
                <h1 class="display-3 fw-bold text-warning" style="letter-spacing: 8px; font-family: monospace;">
                    {{ $game->code }}
                </h1>
                <p class="text-muted mb-4">أرسل هذا الكود لأصدقائك للانضمام</p>

                @if($categories->isNotEmpty())
                <div class="mb-4">
                    <h5 class="mb-3"><i class="fas fa-tags me-2 text-warning"></i>الفقرات</h5>
                    <div class="d-flex flex-wrap justify-content-center gap-2">
                        @foreach($categories as $cat)
                            <span class="badge bg-warning text-dark fs-6 px-3 py-2">{{ $cat->name }}</span>
                        @endforeach
                    </div>
                </div>
                @endif

                <div class="mb-4">
                    <h5 class="mb-3"><i class="fas fa-users me-2 text-info"></i>اللاعبون (<span id="playerCount">{{ $game->players->count() }}</span>)</h5>
                    <div id="playersList" class="d-flex flex-wrap justify-content-center gap-3">
                        @foreach($game->players as $player)
                        <div class="text-center" style="min-width:80px;" data-player-id="{{ $player->id }}">
                            <img src="{{ $player->user->avatar_url }}" alt="" class="rounded-circle mb-1" width="48" height="48">
                            <div class="small fw-bold player-name">{{ $player->display_name }}</div>
                            @if($player->user_id === auth()->id())
                            <button type="button" class="btn btn-sm btn-outline-secondary mt-1 py-0 px-2 edit-name-btn" style="font-size:0.7rem;" onclick="toggleNameEdit(this)">
                                <i class="fas fa-pencil-alt"></i>
                            </button>
                            <div class="name-edit-wrap" style="display:none;">
                                <input type="text" class="form-control form-control-sm text-center mt-1 name-input" value="{{ $player->display_name }}" maxlength="50">
                                <div class="d-flex gap-1 mt-1 justify-content-center">
                                    <button type="button" class="btn btn-sm btn-success py-0 px-2" onclick="saveName(this, '{{ $game->code }}')"><i class="fas fa-check"></i></button>
                                    <button type="button" class="btn btn-sm btn-secondary py-0 px-2" onclick="cancelNameEdit(this)"><i class="fas fa-times"></i></button>
                                </div>
                            </div>
                            @endif
                        </div>
                        @endforeach
                    </div>
                </div>

                @if($isCreator)
                    <form action="{{ route('bluff.start', $game->code) }}" method="POST" id="startForm">
                        @csrf
                        <button type="submit" class="btn btn-primary-custom btn-lg w-100" id="startBtn">
                            <i class="fas fa-play me-2"></i>بدء اللعبة
                        </button>
                    </form>
                    <div class="text-muted small mt-2" id="minPlayersMsg">
                        <i class="fas fa-info-circle me-1"></i>يجب أن يكون على الأقل لاعبين للبدء
                    </div>
                @else
                    <div class="alert alert-info border-0 rounded-3">
                        <i class="fas fa-clock me-2"></i>بانتظار أن يبدأ منشئ اللعبة اللعبة...
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const code = '{{ $game->code }}';
    const isCreator = {{ $isCreator ? 'true' : 'false' }};
    const startBtn = document.getElementById('startBtn');
    const minMsg = document.getElementById('minPlayersMsg');

    function checkLobby() {
        fetch('{{ route("bluff.lobby.data", $game->code) }}')
            .then(r => r.json())
            .then(data => {
                const list = document.getElementById('playersList');
                const currentUserId = {{ auth()->id() }};
                list.querySelectorAll('.player-name-wrap').forEach(el => {
                    const pid = parseInt(el.closest('[data-player-id]')?.dataset.playerId);
                    if (pid) {
                        // Keep existing display names for current user
                    }
                });

                list.innerHTML = data.players.map(p =>
                    `<div class="text-center" style="min-width:80px;" data-player-id="${p.id}">
                        <img src="${p.avatar}" alt="" class="rounded-circle mb-1" width="48" height="48">
                        <div class="small fw-bold player-name">${p.name}</div>
                        ${p.id === {{ auth()->id() }} ? `
                        <button type="button" class="btn btn-sm btn-outline-secondary mt-1 py-0 px-2 edit-name-btn" style="font-size:0.7rem;" onclick="toggleNameEdit(this)">
                            <i class="fas fa-pencil-alt"></i>
                        </button>
                        <div class="name-edit-wrap" style="display:none;">
                            <input type="text" class="form-control form-control-sm text-center mt-1 name-input" value="${p.name}" maxlength="50">
                            <div class="d-flex gap-1 mt-1 justify-content-center">
                                <button type="button" class="btn btn-sm btn-success py-0 px-2" onclick="saveName(this, '${code}')"><i class="fas fa-check"></i></button>
                                <button type="button" class="btn btn-sm btn-secondary py-0 px-2" onclick="cancelNameEdit(this)"><i class="fas fa-times"></i></button>
                            </div>
                        </div>
                        ` : ''}
                    </div>`
                ).join('');

                document.getElementById('playerCount').textContent = data.players.length;

                if (isCreator) {
                    const count = data.players.length;
                    if (count >= 2) {
                        startBtn.disabled = false;
                        if (minMsg) minMsg.style.display = 'none';
                    } else {
                        startBtn.disabled = true;
                        if (minMsg) minMsg.style.display = 'block';
                    }
                }

                if (data.status === 'playing') {
                    window.location.href = '{{ route("bluff.play", $game->code) }}';
                }
            })
            .catch(() => {});
    }

    function toggleNameEdit(btn) {
        const wrap = btn.parentElement.querySelector('.name-edit-wrap');
        if (wrap) {
            const isHidden = wrap.style.display === 'none' || !wrap.style.display;
            wrap.style.display = isHidden ? 'block' : 'none';
            btn.style.display = isHidden ? 'none' : 'inline-block';
            if (isHidden) wrap.querySelector('.name-input')?.focus();
        }
    }

    function cancelNameEdit(btn) {
        const wrap = btn.closest('.name-edit-wrap');
        if (wrap) {
            wrap.style.display = 'none';
            wrap.parentElement.querySelector('.edit-name-btn').style.display = 'inline-block';
        }
    }

    function saveName(btn, code) {
        const wrap = btn.closest('.name-edit-wrap');
        const input = wrap.querySelector('.name-input');
        const name = input.value.trim();
        if (!name) return;

        fetch('/bluff/' + code + '/display-name', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ display_name: name })
        })
        .then(r => r.json())
        .then(data => {
            if (data.status === 'ok') {
                const playerNameEl = wrap.parentElement.querySelector('.player-name');
                if (playerNameEl) playerNameEl.textContent = data.name;
                wrap.style.display = 'none';
                wrap.parentElement.querySelector('.edit-name-btn').style.display = 'inline-block';
            }
        })
        .catch(() => {});
    }

    setInterval(checkLobby, 3000);
</script>
@endpush
