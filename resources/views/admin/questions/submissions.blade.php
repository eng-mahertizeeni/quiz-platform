@extends('Admin')
@section('title', 'الأسئلة المقترحة')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="fw-bold mb-0">الأسئلة المقترحة</h5>
</div>

<div class="card-admin p-3">
    <form method="GET" class="row g-2 mb-3">
        <div class="col-md-4">
            <select name="status" class="form-select">
                <option value="">كل الحالات</option>
                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>قيد المراجعة</option>
                <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>مقبول</option>
                <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>مرفوض</option>
            </select>
        </div>
        <div class="col-md-2">
            <button class="btn btn-outline-secondary w-100" type="submit"><i class="fas fa-filter"></i> تصفية</button>
        </div>
    </form>
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead>
                <tr>
                    <th>السؤال</th>
                    <th>المستخدم</th>
                    <th>الفئة</th>
                    <th>الصعوبة</th>
                    <th>الحالة</th>
                    <th>التاريخ</th>
                    <th>الإجراءات</th>
                </tr>
            </thead>
            <tbody>
                @forelse($submissions as $s)
                <tr>
                    <td class="small">{{ Str::limit($s->question_text, 50) }}</td>
                    <td>{{ $s->user->name ?? '--' }}</td>
                    <td>{{ $s->category->name ?? '--' }}</td>
                    <td><span class="badge badge-points-{{ $s->difficulty === 'medium' ? 250 : ($s->difficulty === 'hard' ? 500 : 750) }}">{{ $s->difficulty }}</span></td>
                    <td>
                        @if($s->isPending())
                            <span class="badge bg-warning text-dark">بانتظار المراجعة</span>
                        @elseif($s->isApproved())
                            <span class="badge bg-success">مقبول</span>
                        @else
                            <span class="badge bg-danger">مرفوض</span>
                        @endif
                    </td>
                    <td class="small text-muted">{{ $s->created_at->diffForHumans() }}</td>
                    <td>
                        <a href="{{ route('admin.questions.review', $s) }}" class="btn btn-sm btn-outline-info"><i class="fas fa-eye"></i></a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center text-muted">لا توجد أسئلة مقترحة</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $submissions->links() }}
</div>
@endsection
