@extends('Admin')
@section('title', 'الفئات')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="fw-bold mb-0">الفئات</h5>
    <a href="{{ route('admin.categories.create') }}" class="btn btn-primary-custom"><i class="fas fa-plus me-1"></i>إضافة فئة</a>
</div>

<div class="card-admin p-3">
    <form method="GET" class="mb-3">
        <div class="input-group">
            <input type="text" name="search" class="form-control" placeholder="بحث..." value="{{ request('search') }}">
            <button class="btn btn-outline-secondary" type="submit"><i class="fas fa-search"></i></button>
        </div>
    </form>
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead>
                <tr>
                    <th>الاسم</th>
                    <th>الاسم بالعربية</th>
                    <th>الرابط</th>
                    <th>الأسئلة</th>
                    <th>مرات اللعب</th>
                    <th>مميز</th>
                    <th>نشط</th>
                    <th>الإجراءات</th>
                </tr>
            </thead>
            <tbody>
                @forelse($categories as $cat)
                <tr>
                    <td><i class="fas fa-{{ $cat->icon ?: 'tag' }} me-2" style="color:{{ $cat->color }}"></i>{{ $cat->name }}</td>
                    <td>{{ $cat->name_ar ?? '--' }}</td>
                    <td><code>{{ $cat->slug }}</code></td>
                    <td>{{ $cat->questions_count }}</td>
                    <td>{{ $cat->times_played }}</td>
                    <td>{!! $cat->is_featured ? '<span class="badge bg-warning text-dark">نعم</span>' : '<span class="badge bg-secondary">لا</span>' !!}</td>
                    <td>{!! $cat->is_active ? '<span class="badge bg-success">نشط</span>' : '<span class="badge bg-danger">معطل</span>' !!}</td>
                    <td>
                        <a href="{{ route('admin.categories.edit', $cat) }}" class="btn btn-sm btn-outline-warning"><i class="fas fa-edit"></i></a>
                        <form method="POST" action="{{ route('admin.categories.featured', $cat) }}" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-outline-info" title="تبديل التمييز"><i class="fas fa-star"></i></button>
                        </form>
                        <form method="POST" action="{{ route('admin.categories.destroy', $cat) }}" class="d-inline" onsubmit="return confirm('حذف الفئة؟')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="text-center text-muted">لا توجد فئات</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $categories->links() }}
</div>
@endsection
