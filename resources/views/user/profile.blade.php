@extends('App')
@section('title', 'الملف الشخصي')
@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card-custom p-4 mb-4">
                <div class="text-center mb-4">
                    <img src="{{ $user->avatar_url }}" alt="" class="rounded-circle mb-3" width="100" height="100" style="border:3px solid var(--primary)">
                    <h4 class="fw-bold">{{ $user->name }}</h4>
                    <span class="badge bg-warning text-dark">@ {{ $user->username }}</span>
                </div>
            </div>

            <div class="card-custom p-4 mb-4">
                <h5 class="fw-bold mb-3"><i class="fas fa-user-edit text-warning me-2"></i>تعديل الملف الشخصي</h5>
                <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
                    @csrf @method('PATCH')
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">الاسم</label>
                            <input type="text" name="name" class="form-control" value="{{ old('name', $user->name) }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">اسم المستخدم</label>
                            <input type="text" name="username" class="form-control" value="{{ old('username', $user->username) }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">البريد الإلكتروني</label>
                            <input type="email" name="email" class="form-control" value="{{ old('email', $user->email) }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">الصورة الشخصية</label>
                            <input type="file" name="avatar" class="form-control" accept="image/*">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary-custom mt-3">حفظ التغييرات</button>
                </form>
            </div>

            <div class="card-custom p-4 mb-4">
                <h5 class="fw-bold mb-3"><i class="fas fa-key text-danger me-2"></i>تغيير كلمة المرور</h5>
                <form method="POST" action="{{ route('profile.password') }}">
                    @csrf @method('PUT')
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-bold">كلمة المرور الحالية</label>
                            <input type="password" name="current_password" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">كلمة المرور الجديدة</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">تأكيد كلمة المرور</label>
                            <input type="password" name="password_confirmation" class="form-control" required>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-outline-warning mt-3">تغيير كلمة المرور</button>
                </form>
            </div>

            <div class="card-custom p-4 border-danger">
                <h5 class="fw-bold text-danger mb-3"><i class="fas fa-trash me-2"></i>حذف الحساب</h5>
                <form method="POST" action="{{ route('profile.destroy') }}" onsubmit="return confirm('هل أنت متأكد من حذف حسابك؟')">
                    @csrf @method('DELETE')
                    <div class="row g-3 align-items-end">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">كلمة المرور للتأكيد</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <button type="submit" class="btn btn-danger">حذف الحساب</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
