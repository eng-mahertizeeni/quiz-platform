@extends('App')
@section('title', 'دخول')
@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card-custom p-4">
                <div class="text-center mb-4">
                    <i class="fas fa-user-circle text-warning" style="font-size:3rem"></i>
                    <h4 class="fw-bold mt-2">تسجيل الدخول</h4>
                </div>
                <form method="POST" action="{{ route('login') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label fw-bold">البريد الإلكتروني</label>
                        <input type="email" name="email" class="form-control" value="{{ old('email') }}" required autofocus>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">كلمة المرور</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <div class="mb-3 form-check">
                        <input type="checkbox" name="remember" class="form-check-input" id="remember">
                        <label class="form-check-label" for="remember">تذكرني</label>
                    </div>
                    <button type="submit" class="btn btn-primary-custom w-100">دخول</button>
                </form>
                <p class="text-center text-muted mt-3 mb-0">
                    ليس لديك حساب؟
                    <a href="{{ route('register') }}" class="text-warning fw-bold">سجل الآن</a>
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
