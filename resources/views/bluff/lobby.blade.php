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
                    <h5 class="mb-3"><i class="fas fa-users me-2 text-info"></i>اللاعبون ({{ $game->players->count() }})</h5>
                    <div id="playersList" class="d-flex flex-wrap justify-content-center gap-3">
                        @foreach($game->players as $player)
                        <div class="text-center" style="min-width:80px;">
                            <img src="{{ $player->user->avatar_url }}" alt="" class="rounded-circle mb-1" width="48" height="48">
                            <div class="small fw-bold">{{ $player->user->name }}</div>
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
                list.innerHTML = data.players.map(p =>
                    `<div class="text-center" style="min-width:80px;">
                        <img src="${p.avatar}" alt="" class="rounded-circle mb-1" width="48" height="48">
                        <div class="small fw-bold">${p.name}</div>
                    </div>`
                ).join('');

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

    setInterval(checkLobby, 3000);
</script>
@endpush
