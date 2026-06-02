@extends('Admin')
@section('title', 'المستخدمون')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="fw-bold mb-0">المستخدمون</h5>
</div>

<div class="card-admin p-3">
    <form method="GET" class="row g-2 mb-3">
        <div class="col-md-5">
            <input type="text" name="search" class="form-control" placeholder="بحث بالاسم أو البريد أو اسم المستخدم..." value="{{ request('search') }}">
        </div>
        <div class="col-md-3">
            <select name="role" class="form-select">
                <option value="">كل الأدوار</option>
                <option value="admin" {{ request('role') == 'admin' ? 'selected' : '' }}>مسؤول</option>
                <option value="user" {{ request('role') == 'user' ? 'selected' : '' }}>مستخدم</option>
            </select>
        </div>
        <div class="col-md-2">
            <button class="btn btn-outline-secondary w-100" type="submit"><i class="fas fa-search"></i></button>
        </div>
    </form>
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead>
                <tr>
                    <th>المستخدم</th>
                    <th>البريد</th>
                    <th>الدور</th>
                    <th>النقاط</th>
                    <th>الألعاب</th>
                    <th>نسبة الفوز</th>
                    <th>نشط</th>
                    <th>الإجراءات</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $u)
                <tr>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <img src="{{ $u->avatar_url }}" alt="" class="rounded-circle" width="32" height="32">
                            <strong>{{ $u->name }}</strong>
                        </div>
                    </td>
                    <td class="small">{{ $u->email }}</td>
                    <td>{!! $u->isAdmin() ? '<span class="badge bg-warning text-dark">مسؤول</span>' : '<span class="badge bg-info">مستخدم</span>' !!}</td>
                    <td>{{ number_format($u->total_score) }}</td>
                    <td>{{ $u->games_played }}</td>
                    <td>{{ $u->win_rate }}%</td>
                    <td>{!! $u->is_active ? '<span class="badge bg-success">نشط</span>' : '<span class="badge bg-danger">معطل</span>' !!}</td>
                    <td>
                        <a href="{{ route('admin.users.show', $u) }}" class="btn btn-sm btn-outline-info"><i class="fas fa-eye"></i></a>
                        @if(!$u->isAdmin())
                        <form method="POST" action="{{ route('admin.users.toggle-status', $u) }}" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-outline-{{ $u->is_active ? 'danger' : 'success' }}" title="{{ $u->is_active ? 'تعطيل' : 'تفعيل' }}">
                                <i class="fas {{ $u->is_active ? 'fa-ban' : 'fa-check' }}"></i>
                            </button>
                        </form>
                        <form method="POST" action="{{ route('admin.users.destroy', $u) }}" class="d-inline" onsubmit="return confirm('حذف المستخدم؟')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                        </form>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="text-center text-muted">لا يوجد مستخدمون</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $users->links() }}
</div>
@endsection
