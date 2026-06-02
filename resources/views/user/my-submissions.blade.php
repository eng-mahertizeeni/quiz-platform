@extends('App')
@section('title', 'أسئلتي')
@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold"><i class="fas fa-list text-primary me-2"></i>أسئلتي المقترحة</h4>
        <a href="{{ route('questions.submit') }}" class="btn btn-primary-custom"><i class="fas fa-plus me-1"></i>إضافة سؤال</a>
    </div>

    <div class="card-custom p-3">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>السؤال</th>
                        <th>الفئة</th>
                        <th>الصعوبة</th>
                        <th>الحالة</th>
                        <th>التاريخ</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($submissions as $s)
                    <tr>
                        <td class="small">{{ Str::limit($s->question_text, 50) }}</td>
                        <td>{{ $s->category->name ?? '--' }}</td>
                        <td><span class="badge badge-points-{{ $s->difficulty === 'medium' ? 250 : ($s->difficulty === 'hard' ? 500 : 750) }}">{{ $s->difficulty_label }}</span></td>
                        <td>
                            @if($s->isPending())
                                <span class="badge bg-warning text-dark">قيد المراجعة</span>
                            @elseif($s->isApproved())
                                <span class="badge bg-success">مقبول</span>
                            @else
                                <span class="badge bg-danger">مرفوض</span>
                            @endif
                        </td>
                        <td class="text-muted small">{{ $s->created_at->diffForHumans() }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="text-center text-muted">لم تقم بإضافة أي أسئلة بعد</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $submissions->links() }}
    </div>
</div>
@endsection
