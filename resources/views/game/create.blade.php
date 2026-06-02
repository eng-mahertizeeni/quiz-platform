@extends('App')
@section('title', 'إنشاء لعبة جديدة')
@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card-custom p-4">
                <h4 class="fw-bold mb-3"><i class="fas fa-play-circle text-success me-2"></i>إنشاء لعبة جديدة</h4>
                <form method="POST" action="{{ route('game.store') }}">
                    @csrf
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="card-custom p-3 text-center" style="border-color:var(--info)">
                                <label class="form-label fw-bold text-info">الفريق الأول</label>
                                <input type="text" name="team_one_name" class="form-control text-center" placeholder="اسم الفريق الأول" required>
                                <div class="mt-2">
                                    <label class="small text-muted">اللون</label>
                                    <input type="color" name="team_one_color" class="form-control form-control-color mx-auto d-block" value="#3B82F6" style="width:60px;height:40px">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card-custom p-3 text-center" style="border-color:var(--danger)">
                                <label class="form-label fw-bold text-danger">الفريق الثاني</label>
                                <input type="text" name="team_two_name" class="form-control text-center" placeholder="اسم الفريق الثاني" required>
                                <div class="mt-2">
                                    <label class="small text-muted">اللون</label>
                                    <input type="color" name="team_two_color" class="form-control form-control-color mx-auto d-block" value="#EF4444" style="width:60px;height:40px">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row g-3 mt-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">مدة الإجابة (ثانية)</label>
                            <select name="timer_seconds" class="form-select">
                                <option value="15">15 ثانية</option>
                                <option value="30" selected>30 ثانية</option>
                                <option value="45">45 ثانية</option>
                                <option value="60">60 ثانية</option>
                                <option value="90">90 ثانية</option>
                                <option value="120">120 ثانية</option>
                            </select>
                        </div>
                        <div class="col-md-6 d-flex align-items-end">
                            <div class="form-check form-switch">
                                <input type="hidden" name="sound_enabled" value="0">
                                <input type="checkbox" name="sound_enabled" class="form-check-input" value="1" checked id="soundToggle">
                                <label class="form-check-label fw-bold" for="soundToggle"><i class="fas fa-volume-up me-1"></i>مؤثرات صوتية</label>
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary-custom w-100 mt-4 btn-lg">إنشاء اللعبة</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
