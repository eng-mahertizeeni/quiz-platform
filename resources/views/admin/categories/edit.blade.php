@extends('Admin')
@section('title', 'تعديل فئة')
@section('content')
<div class="card-admin p-4">
    <h5 class="fw-bold mb-3"><i class="fas fa-edit text-warning me-2"></i>تعديل الفئة: {{ $category->name }}</h5>
    <form method="POST" action="{{ route('admin.categories.update', $category) }}" enctype="multipart/form-data">
        @csrf @method('PUT')
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label fw-bold">الاسم</label>
                <input type="text" name="name" class="form-control" value="{{ old('name', $category->name) }}" required>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-bold">الاسم بالعربية</label>
                <input type="text" name="name_ar" class="form-control" value="{{ old('name_ar', $category->name_ar) }}">
            </div>
            <div class="col-md-6">
                <label class="form-label fw-bold">الرابط (slug)</label>
                <input type="text" name="slug" class="form-control" value="{{ old('slug', $category->slug) }}">
            </div>
            <div class="col-md-6">
                <label class="form-label fw-bold">الأيقونة</label>
                <input type="text" name="icon" class="form-control" value="{{ old('icon', $category->icon) }}" placeholder="مثال: football">
            </div>
            <div class="col-md-6">
                <label class="form-label fw-bold">اللون</label>
                <input type="color" name="color" class="form-control form-control-color" value="{{ old('color', $category->color ?? '#F59E0B') }}">
            </div>
            <div class="col-md-6">
                <label class="form-label fw-bold">الصورة</label>
                <input type="file" name="thumbnail" class="form-control" accept="image/*">
                @if($category->thumbnail)<small class="text-muted d-block">موجودة حالياً</small>@endif
            </div>
            <div class="col-12">
                <label class="form-label fw-bold">الوصف</label>
                <textarea name="description" class="form-control" rows="3">{{ old('description', $category->description) }}</textarea>
            </div>
            <div class="col-md-4">
                <label class="form-label fw-bold">ترتيب الفرز</label>
                <input type="number" name="sort_order" class="form-control" value="{{ old('sort_order', $category->sort_order) }}">
            </div>
            <div class="col-md-4 d-flex align-items-center gap-3">
                <div class="form-check">
                    <input type="hidden" name="is_featured" value="0">
                    <input type="checkbox" name="is_featured" class="form-check-input" value="1" id="isFeatured" {{ $category->is_featured ? 'checked' : '' }}>
                    <label class="form-check-label" for="isFeatured">مميزة</label>
                </div>
            </div>
            <div class="col-md-4 d-flex align-items-center gap-3">
                <div class="form-check">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" class="form-check-input" value="1" id="isActive" {{ $category->is_active ? 'checked' : '' }}>
                    <label class="form-check-label" for="isActive">نشطة</label>
                </div>
            </div>
        </div>
        <button type="submit" class="btn btn-primary-custom mt-3">تحديث</button>
    </form>
</div>
@endsection
