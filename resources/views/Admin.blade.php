<!DOCTYPE html>
<html lang="ar" dir="rtl" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'لوحة التحكم') - Quiz Battle Admin</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;900&display=swap" rel="stylesheet">

    <style>
        :root {
            --sidebar-w: 260px;
            --bg-dark: 

            --bg-card: 

            --bg-sidebar: 

            --border: 

            --text-primary: 

            --text-muted: 

            --primary: 

        }

        body {
            font-family: 'Cairo', sans-serif;
            background: var(--bg-dark);
            color: var(--text-primary);
            margin: 0;
        }

        .admin-sidebar {
            width: var(--sidebar-w);
            min-height: 100vh;
            background: var(--bg-sidebar);
            border-left: 1px solid var(--border);
            position: fixed;
            top: 0;
            right: 0;
            z-index: 1000;
            overflow-y: auto;
            transition: transform 0.3s;
        }

        .admin-main {
            margin-right: var(--sidebar-w);
            min-height: 100vh;
            padding: 1.5rem;
        }

        .sidebar-brand {
            padding: 1.5rem;
            border-bottom: 1px solid var(--border);
            font-weight: 900;
            font-size: 1.2rem;
            color: var(--primary);
            text-decoration: none;
            display: block;
        }

        .sidebar-nav .nav-link {
            color: var(--text-muted);
            padding: 0.75rem 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-weight: 600;
            transition: all 0.2s;
            border-right: 3px solid transparent;
        }

        .sidebar-nav .nav-link:hover,
        .sidebar-nav .nav-link.active {
            color: var(--primary);
            background: rgba(245, 158, 11, 0.08);
            border-right-color: var(--primary);
        }

        .sidebar-section-title {
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: var(--text-muted);
            padding: 1rem 1.5rem 0.25rem;
            font-weight: 700;
        }

        .card-admin {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 12px;
        }

        .stat-card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 1.5rem;
            transition: transform 0.2s;
        }

        .stat-card:hover { transform: translateY(-2px); }

        .stat-icon {
            width: 52px;
            height: 52px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
        }

        .form-control, .form-select {
            background: 

            border: 1px solid var(--border);
            color: var(--text-primary);
            border-radius: 10px;
        }

        .form-control:focus, .form-select:focus {
            background: 

            color: var(--text-primary);
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.15);
        }

        .table { color: var(--text-primary); }
        .table > :not(caption) > * > * { background: transparent; color: var(--text-primary); }

        .admin-topbar {
            background: var(--bg-card);
            border-bottom: 1px solid var(--border);
            padding: 1rem 1.5rem;
            margin: -1.5rem -1.5rem 1.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        @media (max-width: 992px) {
            .admin-sidebar { transform: translateX(100%); }
            .admin-sidebar.show { transform: translateX(0); }
            .admin-main { margin-right: 0; }
        }
    </style>
    @stack('styles')
</head>
<body>

{{-- Sidebar --}}
<aside class="admin-sidebar" id="adminSidebar">
    <a class="sidebar-brand" href="{{ route('admin.dashboard') }}">
        <i class="fas fa-shield-alt me-2"></i>Quiz Admin
    </a>

    <nav class="sidebar-nav">
        <div class="sidebar-section-title">عام</div>
        <a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
            <i class="fas fa-tachometer-alt"></i>لوحة التحكم
        </a>

        <div class="sidebar-section-title">المحتوى</div>
        <a href="{{ route('admin.categories.index') }}" class="nav-link {{ request()->routeIs('admin.categories*') ? 'active' : '' }}">
            <i class="fas fa-tags"></i>الفئات
        </a>
        <a href="{{ route('admin.questions.index') }}" class="nav-link {{ request()->routeIs('admin.questions.index') ? 'active' : '' }}">
            <i class="fas fa-question-circle"></i>الأسئلة
        </a>
        <a href="{{ route('admin.questions.submissions') }}" class="nav-link {{ request()->routeIs('admin.questions.submissions*') || request()->routeIs('admin.questions.review*') ? 'active' : '' }}">
            <i class="fas fa-inbox"></i>الأسئلة المقترحة
            @if(($pendingCount = cache()->remember('pending_submissions_count', 60, fn() => \App\Models\SubmittedQuestion::where('status','pending')->count())) > 0)
                <span class="badge bg-danger ms-auto">{{ $pendingCount }}</span>
            @endif
        </a>

        <div class="sidebar-section-title">المستخدمون</div>
        <a href="{{ route('admin.users.index') }}" class="nav-link {{ request()->routeIs('admin.users*') ? 'active' : '' }}">
            <i class="fas fa-users"></i>المستخدمون
        </a>

        <div class="sidebar-section-title">النظام</div>
        <a href="{{ route('home') }}" class="nav-link" target="_blank">
            <i class="fas fa-external-link-alt"></i>زيارة الموقع
        </a>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="nav-link w-100 border-0 bg-transparent text-start" style="cursor:pointer">
                <i class="fas fa-sign-out-alt"></i>تسجيل الخروج
            </button>
        </form>
    </nav>
</aside>

{{-- Main --}}
<div class="admin-main">
    {{-- Topbar --}}
    <div class="admin-topbar">
        <div class="d-flex align-items-center gap-3">
            <button class="btn btn-sm btn-outline-secondary d-lg-none" onclick="document.getElementById('adminSidebar').classList.toggle('show')">
                <i class="fas fa-bars"></i>
            </button>
            <h6 class="mb-0 fw-bold">@yield('title', 'لوحة التحكم')</h6>
        </div>
        <div class="d-flex align-items-center gap-2">
            <img src="{{ auth()->user()->avatar_url }}" alt="" class="rounded-circle" width="36" height="36">
            <span class="d-none d-md-inline text-muted small">{{ auth()->user()->name }}</span>
        </div>
    </div>

    {{-- Flash --}}
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show border-0 rounded-3 mb-3">
        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif
    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show border-0 rounded-3 mb-3">
        <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif
    @if($errors->any())
    <div class="alert alert-danger border-0 rounded-3 mb-3">
        <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
    @endif

    @yield('content')
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')
</body>
</html>