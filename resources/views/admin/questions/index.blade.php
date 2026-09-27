@extends('Admin')
@section('title', 'الأسئلة')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="fw-bold mb-0">الأسئلة</h5>
    <a href="{{ route('admin.questions.create') }}" class="btn btn-primary-custom"><i class="fas fa-plus me-1"></i>إضافة سؤال</a>
</div>

<div class="card-admin p-3">
    <form method="GET" class="row g-2 mb-3">
        <div class="col-md-4">
            <input type="text" name="search" class="form-control" placeholder="بحث..." value="{{ request('search') }}">
        </div>
        <div class="col-md-3">
            <select name="category" class="form-select">
                <option value="">كل الفئات</option>
                @foreach($categories as $cat)
                <option value="{{ $cat->id }}" {{ request('category') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <select name="difficulty" class="form-select">
                <option value="">كل الصعوبات</option>
                <option value="easy" {{ request('difficulty') =='easy' ? 'selected' : '' }}>سهل</option>
                <option value="medium" {{ request('difficulty') =='medium' ? 'selected' : '' }}>متوسط</option>
                <option value="hard" {{ request('difficulty') =='hard' ? 'selected' : '' }}>صعب</option>
            </select>
        </div>
        <div class="col-md-2">
            <select name="status" class="form-select">
                <option value="">كل الحالات</option>
                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>نشط</option>
                <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>غير نشط</option>
            </select>
        </div>
        <div class="col-md-1">
            <button class="btn btn-outline-secondary w-100" type="submit"><i class="fas fa-search"></i></button>
        </div>
    </form>
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead>
                <tr>
                    <th>السؤال</th>
                    <th>الفئة</th>
                    <th>الصعوبة</th>
                    <th>النقاط</th>
                    <th>المنشئ</th>
                    <th>الحالة</th>
                    <th>الإجراءات</th>
                </tr>
            </thead>
            <tbody>
                @forelse($questions as $q)
                <tr>
                    <td class="small">{{ Str::limit($q->question_text, 60) }}</td>
                    <td>{{ $q->category->name ?? '--' }}</td>
                    <td><span class="badge badge-points-{{ $q->points }}">{{ $q->difficulty_label }}</span></td>
                    <td>{{ $q->points }}</td>
                    <td class="small">{{ $q->creator->name ?? '--' }}</td>
                    <td>{!! $q->status === 'active' ? '<span class="badge bg-success">نشط</span>' : '<span class="badge bg-secondary">غير نشط</span>' !!}</td>
                    <td>
                        <a href="{{ route('admin.questions.edit', $q) }}" class="btn btn-sm btn-outline-warning"><i class="fas fa-edit"></i></a>
                        <form method="POST" action="{{ route('admin.questions.destroy', $q) }}" class="d-inline" onsubmit="return confirm('حذف السؤال؟')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center text-muted">لا توجد أسئلة</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $questions->links() }}
</div>
@endsection
