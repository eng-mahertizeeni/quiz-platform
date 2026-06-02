@extends('App')
@section('title', 'تسجيل')
@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card-custom p-4">
                <div class="text-center mb-4">
                    <i class="fas fa-user-plus text-warning" style="font-size:3rem"></i>
                    <h4 class="fw-bold mt-2">إنشاء حساب جديد</h4>
                </div>
                <form method="POST" action="{{ route('register') }}">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">الاسم</label>
                            <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">اسم المستخدم</label>
                            <input type="text" name="username" class="form-control" value="{{ old('username') }}" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">البريد الإلكتروني</label>
                            <input type="email" name="email" class="form-control" value="{{ old('email') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">كلمة المرور</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">تأكيد كلمة المرور</label>
                            <input type="password" name="password_confirmation" class="form-control" required>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary-custom w-100 mt-3">تسجيل</button>
                </form>
                <p class="text-center text-muted mt-3 mb-0">
                    لديك حساب بالفعل؟
                    <a href="{{ route('login') }}" class="text-warning fw-bold">دخول</a>
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
