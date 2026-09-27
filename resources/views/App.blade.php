<!DOCTYPE html>
<html lang="ar" dir="rtl" data-theme="{{ session('theme', 'dark') }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Quiz Battle') - منصة المسابقات</title>

    {{-- Bootstrap RTL --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css">
    {{-- Font Awesome --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    {{-- Google Fonts Arabic --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;900&family=Tajawal:wght@400;500;700;900&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary: 

            --primary-dark: 

            --bg-dark: 

            --bg-card: 

            --bg-card-hover: 

            --text-primary: 

            --text-muted: 

            --border: 

            --success: 

            --danger: 

            --info: 

            --warning: 

        }

        [data-theme="light"] {
            --bg-dark: 

            --bg-card: 

            --bg-card-hover: 

            --text-primary: 

            --text-muted: 

            --border: 

        }

        * { box-sizing: border-box; }

        body {
            font-family: 'Cairo', 'Tajawal', sans-serif;
            background-color: var(--bg-dark);
            color: var(--text-primary);
            min-height: 100vh;
            transition: background-color 0.3s, color 0.3s;
        }

        .navbar-brand { font-weight: 900; font-size: 1.4rem; }
        .nav-link { font-weight: 600; }

        .navbar-custom {
            background: rgba(15, 23, 42, 0.95);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid var(--border);
        }

        [data-theme="light"] .navbar-custom {
            background: rgba(255, 255, 255, 0.95);
            border-bottom: 1px solid var(--border);
        }

        .card-custom {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 16px;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .card-custom:hover { transform: translateY(-2px); }

        .btn-primary-custom {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            border: none;
            color: 

            font-weight: 700;
            border-radius: 10px;
            padding: 10px 24px;
            transition: all 0.2s;
        }

        .btn-primary-custom:hover {
            transform: translateY(-1px);
            box-shadow: 0 8px 25px rgba(245, 158, 11, 0.4);
            color: 

        }

        .badge-points-200 { background: #10B981; color: #fff; }
        .badge-points-400 { background: #F59E0B; color: #000; }
        .badge-points-600 { background: #EF4444; color: #fff; }

        .toast-container { z-index: 9999; }

        .form-control, .form-select {
            background: var(--bg-card);
            border: 1px solid var(--border);
            color: var(--text-primary);
            border-radius: 10px;
        }

        .form-control:focus, .form-select:focus {
            background: var(--bg-card);
            color: var(--text-primary);
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.15);
        }

        .table { color: var(--text-primary); }
        .table > :not(caption) > * > * { background: transparent; }
        .table-hover tbody tr:hover td { background: var(--bg-card-hover); }

        .page-header {
            background: linear-gradient(135deg, var(--bg-card) 0%, rgba(245,158,11,0.05) 100%);
            border-bottom: 1px solid var(--border);
            padding: 2rem 0;
            margin-bottom: 2rem;
        }

        footer {
            background: var(--bg-card);
            border-top: 1px solid var(--border);
            color: var(--text-muted);
        }

        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: var(--bg-dark); }
        ::-webkit-scrollbar-thumb { background: var(--border); border-radius: 3px; }
        ::-webkit-scrollbar-thumb:hover { background: var(--primary); }

        @media (max-width: 768px) {
            .navbar-brand { font-size: 1.1rem; }
        }
    </style>

    @stack('styles')
</head>
<body>

    {{-- Navbar --}}
    <nav class="navbar navbar-expand-lg navbar-custom sticky-top">
        <div class="container">
            <a class="navbar-brand text-warning" href="{{ route('home') }}">
                <i class="fas fa-brain me-2"></i>Quiz Battle
            </a>

            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navMenu">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link text-light" href="{{ route('home') }}">
                            <i class="fas fa-home me-1"></i>الرئيسية
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-light" href="{{ route('leaderboard') }}">
                            <i class="fas fa-trophy me-1"></i>المتصدرين
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-light" href="{{ route('statistics') }}">
                            <i class="fas fa-chart-bar me-1"></i>الإحصاءات
                        </a>
                    </li>
                </ul>

                <ul class="navbar-nav align-items-center gap-2">
                    {{-- Theme Toggle --}}
                    <li class="nav-item">
                        <button class="btn btn-sm btn-outline-secondary rounded-circle" id="themeToggle" title="تبديل السمة">
                            <i class="fas fa-moon" id="themeIcon"></i>
                        </button>
                    </li>

                    @auth
                        @if(auth()->user()->isAdmin())
                        <li class="nav-item">
                            <a class="btn btn-sm btn-warning fw-bold" href="{{ route('admin.dashboard') }}">
                                <i class="fas fa-cog me-1"></i>لوحة التحكم
                            </a>
                        </li>
                        @endif

                        <li class="nav-item">
                            <a class="btn btn-sm btn-warning fw-bold" href="{{ route('bluff.index') }}">
                                <i class="fas fa-dice me-1"></i>Face Off
                            </a>
                            <a class="btn btn-sm btn-success fw-bold" href="{{ route('game.create') }}">
                                <i class="fas fa-play me-1"></i>لعبة جديدة
                            </a>
                        </li>

                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle text-light d-flex align-items-center gap-2" href="#" data-bs-toggle="dropdown">
                                <img src="{{ auth()->user()->avatar_url }}" alt="" class="rounded-circle" width="32" height="32">
                                <span class="d-none d-lg-inline">{{ auth()->user()->name }}</span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end" style="background:var(--bg-card); border:1px solid var(--border);">
                                <li>
                                    <a class="dropdown-item text-light" href="{{ route('dashboard') }}">
                                        <i class="fas fa-tachometer-alt me-2 text-warning"></i>لوحتي
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item text-light" href="{{ route('profile.edit') }}">
                                        <i class="fas fa-user me-2 text-info"></i>الملف الشخصي
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item text-light" href="{{ route('questions.submit') }}">
                                        <i class="fas fa-plus-circle me-2 text-success"></i>أضف سؤالاً
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item text-light" href="{{ route('questions.my-submissions') }}">
                                        <i class="fas fa-list me-2 text-primary"></i>أسئلتي
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider" style="border-color:var(--border)"></li>
                                <li>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="dropdown-item text-danger">
                                            <i class="fas fa-sign-out-alt me-2"></i>تسجيل الخروج
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </li>
                    @else
                        <li class="nav-item">
                            <a class="btn btn-sm btn-outline-warning fw-bold" href="{{ route('login') }}">
                                <i class="fas fa-sign-in-alt me-1"></i>دخول
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="btn btn-sm btn-primary-custom" href="{{ route('register') }}">
                                <i class="fas fa-user-plus me-1"></i>تسجيل
                            </a>
                        </li>
                    @endauth
                </ul>
            </div>
        </div>
    </nav>

    {{-- Flash Messages --}}
    @if(session('success') || session('error') || session('warning') || session('info'))
    <div class="toast-container position-fixed top-0 start-50 translate-middle-x mt-4" style="z-index:9999">
        @if(session('success'))
        <div class="toast show align-items-center text-bg-success border-0 rounded-3 shadow-lg" role="alert">
            <div class="d-flex">
                <div class="toast-body fw-bold">
                    <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
        @endif
        @if(session('error'))
        <div class="toast show align-items-center text-bg-danger border-0 rounded-3 shadow-lg" role="alert">
            <div class="d-flex">
                <div class="toast-body fw-bold">
                    <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
        @endif
        @if(session('info'))
        <div class="toast show align-items-center text-bg-info border-0 rounded-3 shadow-lg" role="alert">
            <div class="d-flex">
                <div class="toast-body fw-bold">
                    <i class="fas fa-info-circle me-2"></i>{{ session('info') }}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
        @endif
    </div>
    @endif

    {{-- Validation Errors --}}
    @if($errors->any())
    <div class="container mt-3">
        <div class="alert alert-danger border-0 rounded-3">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <ul class="mb-0 mt-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    </div>
    @endif

    {{-- Main Content --}}
    @yield('content')

    {{-- Footer --}}
    <footer class="py-4 mt-5">
        <div class="container text-center">
            <p class="mb-0">
                <i class="fas fa-brain text-warning me-1"></i>
                Quiz Battle &copy; {{ date('Y') }} — منصة المسابقات الذكية
            </p>
        </div>
    </footer>

    {{-- Bootstrap JS --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        
        const themeToggle = document.getElementById('themeToggle');
        const themeIcon   = document.getElementById('themeIcon');
        const html        = document.documentElement;

        const savedTheme = localStorage.getItem('theme') || 'dark';
        html.setAttribute('data-theme', savedTheme);
        themeIcon.className = savedTheme === 'dark' ? 'fas fa-sun' : 'fas fa-moon';

        themeToggle?.addEventListener('click', () => {
            const current = html.getAttribute('data-theme');
            const next    = current === 'dark' ? 'light' : 'dark';
            html.setAttribute('data-theme', next);
            localStorage.setItem('theme', next);
            themeIcon.className = next === 'dark' ? 'fas fa-sun' : 'fas fa-moon';
        });

        document.querySelectorAll('.toast').forEach(t => {
            setTimeout(() => {
                const bsToast = bootstrap.Toast.getOrCreateInstance(t);
                bsToast.hide();
            }, 4000);
        });
    </script>

    @stack('scripts')
</body>
</html>