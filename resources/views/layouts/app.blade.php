<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Comunicaciones')</title>

    {{-- Bootstrap 5 --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        :root {
            --sidebar-bg: #123b67;
            --sidebar-bg-dark: #0e3154;
            --sidebar-item: rgba(255,255,255,0.08);
            --sidebar-item-hover: rgba(255,255,255,0.16);
            --primary-soft: #2f7ecb;
            --page-bg: #eef3f8;
            --card-radius: 16px;
            --shadow-soft: 0 10px 30px rgba(21, 44, 77, 0.08);
            --text-muted-custom: #718096;
        }

        body {
            background: var(--page-bg);
            font-family: Inter, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            color: #1f2937;
        }

        .app-shell {
            min-height: 100vh;
        }

        .sidebar {
            width: 270px;
            min-height: 100vh;
            background: linear-gradient(180deg, var(--sidebar-bg) 0%, var(--sidebar-bg-dark) 100%);
            color: #fff;
            position: fixed;
            left: 0;
            top: 0;
            padding: 1.25rem 1rem;
            z-index: 1030;
            box-shadow: 6px 0 24px rgba(10, 26, 47, 0.12);
        }

        .brand-box {
            display: flex;
            align-items: center;
            gap: .85rem;
            margin-bottom: 1.5rem;
            padding: .5rem .5rem 1rem;
            border-bottom: 1px solid rgba(255,255,255,0.10);
        }

        .brand-icon {
            width: 42px;
            height: 42px;
            border-radius: 12px;
            display: grid;
            place-items: center;
            background: rgba(255,255,255,0.12);
            font-size: 1.25rem;
        }

        .brand-title {
            font-size: 1rem;
            font-weight: 700;
            margin: 0;
            line-height: 1.1;
        }

        .brand-subtitle {
            font-size: .82rem;
            margin: 0;
            color: rgba(255,255,255,0.72);
        }

        .nav-section-title {
            font-size: .78rem;
            text-transform: uppercase;
            letter-spacing: .08em;
            color: rgba(255,255,255,0.6);
            margin: 1.25rem .75rem .5rem;
        }

        .sidebar .nav-link {
            display: flex;
            align-items: center;
            gap: .8rem;
            color: rgba(255,255,255,0.92);
            border-radius: 12px;
            padding: .82rem .9rem;
            margin-bottom: .25rem;
            font-weight: 500;
            transition: .2s ease;
        }

        .sidebar .nav-link i {
            font-size: 1rem;
            opacity: .95;
        }

        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            background: var(--sidebar-item-hover);
            color: #fff;
        }

        .sidebar-footer {
            position: absolute;
            bottom: 1rem;
            left: 1rem;
            right: 1rem;
            background: rgba(255,255,255,0.08);
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 14px;
            padding: .9rem;
            font-size: .88rem;
        }

        .main-wrapper {
            margin-left: 270px;
            min-height: 100vh;
        }

        .topbar {
            background: #fff;
            height: 78px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 2rem;
            border-bottom: 1px solid #e5edf5;
            position: sticky;
            top: 0;
            z-index: 1020;
        }

        .topbar-title {
            font-size: 1.2rem;
            font-weight: 700;
            margin: 0;
        }

        .topbar-subtitle {
            font-size: .9rem;
            color: var(--text-muted-custom);
            margin: 0;
        }

        .content-area {
            padding: 2rem;
        }

        .hero-card {
            background: linear-gradient(135deg, rgba(18,59,103,0.95), rgba(47,126,203,0.88)),
                        url('https://images.unsplash.com/photo-1516549655169-df83a0774514?q=80&w=1600&auto=format&fit=crop') center/cover;
            border-radius: 24px;
            min-height: 220px;
            color: #fff;
            padding: 2rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
            box-shadow: var(--shadow-soft);
        }

        .hero-card h1 {
            font-size: 2rem;
            font-weight: 800;
            margin-bottom: .25rem;
        }

        .hero-card p {
            margin-bottom: 0;
            color: rgba(255,255,255,0.86);
        }

        .info-alert {
            background: #fff7ed;
            border: 1px solid #fed7aa;
            color: #9a3412;
            border-radius: 18px;
            padding: 1rem 1.25rem;
            display: flex;
            gap: .85rem;
            align-items: start;
            box-shadow: var(--shadow-soft);
        }

        .stat-card,
        .panel-card {
            background: #fff;
            border: 1px solid #e5edf5;
            border-radius: var(--card-radius);
            box-shadow: var(--shadow-soft);
        }

        .stat-card {
            padding: 1.2rem 1.25rem;
            height: 100%;
        }

        .stat-label {
            font-size: .82rem;
            text-transform: uppercase;
            letter-spacing: .05em;
            color: #7a8aa0;
            margin-bottom: .45rem;
        }

        .stat-value {
            font-size: 2rem;
            font-weight: 800;
            line-height: 1;
            margin-bottom: .35rem;
        }

        .stat-help {
            font-size: .9rem;
            color: var(--text-muted-custom);
            margin: 0;
        }

        .panel-card {
            padding: 1.4rem;
        }

        .section-title {
            font-size: 1.45rem;
            font-weight: 800;
            margin-bottom: 1rem;
        }

        .chart-placeholder {
            height: 320px;
            border-radius: 16px;
            background: linear-gradient(180deg, #f8fbff 0%, #edf4fb 100%);
            border: 1px dashed #c9d8e8;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #6b7f95;
            font-weight: 600;
        }

        .table thead th {
            font-size: .8rem;
            text-transform: uppercase;
            color: #7a8aa0;
            border-bottom-width: 1px;
        }

        .badge-soft-primary {
            background: rgba(47,126,203,0.12);
            color: #2563eb;
        }

        .badge-soft-warning {
            background: rgba(245, 158, 11, 0.16);
            color: #b45309;
        }

        .badge-soft-success {
            background: rgba(16, 185, 129, 0.14);
            color: #047857;
        }

        .user-chip {
            display: flex;
            align-items: center;
            gap: .75rem;
            background: #f8fbff;
            border: 1px solid #e5edf5;
            border-radius: 14px;
            padding: .45rem .8rem;
        }

        .user-avatar {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            background: linear-gradient(135deg, #2f7ecb, #123b67);
            color: #fff;
            display: grid;
            place-items: center;
            font-weight: 700;
        }

        @media (max-width: 991.98px) {
            .sidebar {
                position: static;
                width: 100%;
                min-height: auto;
                border-radius: 0 0 20px 20px;
            }

            .sidebar-footer {
                position: static;
                margin-top: 1rem;
            }

            .main-wrapper {
                margin-left: 0;
            }

            .topbar {
                padding: 1rem;
                height: auto;
            }

            .content-area {
                padding: 1rem;
            }
        }
        .step-circle {
            width: 34px;
            height: 34px;
            border-radius: 999px;
            border: 2px solid #ced4da;
            color: #6c757d;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            background: #fff;
            transition: all 0.2s ease;
        }

        .step-circle.active {
            background: #0d6efd;
            border-color: #0d6efd;
            color: #fff;
            box-shadow: 0 0 0 4px rgba(13,110,253,.12);
        }
    </style>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body>
<div class="app-shell d-lg-flex">
    <aside class="sidebar">
        <div class="brand-box">
            <div class="brand-icon">
                <i class="bi bi-megaphone-fill"></i>
            </div>
          <a href="{{ route('home') }}" style="text-decoration: none; color: white;">
            <div>
                <h1 class="brand-title">Comunicaciones</h1>
                <p class="brand-subtitle">Plataforma institucional</p>
            </div>
        </a>
        </div>

        <div class="nav flex-column">
            <div class="nav-section-title">Campañas</div>
          
            <a href="{{ route('campanias.index') }}" 
                class="nav-link {{ request()->routeIs('campanias.*') ? 'active' : '' }}">
                    <i class="bi bi-broadcast-pin"></i>
                    <span>Campañas</span>
                </a>
           

            <div class="nav-section-title">Comunicación</div>
           
            <a href="#" class="nav-link">
                <i class="bi bi-calendar-event"></i>
                <span>Comunicación</span>
            </a>
          


   <div class="sidebar-footer">
    <div class="fw-semibold mb-1">Estado general</div>

    <div class="text-white-50 small mb-3">
        {{ $estadoGeneral['campanias_programadas_hoy'] }} campañas programadas para hoy
        y {{ $estadoGeneral['campanias_en_revision'] }} campaña{{ $estadoGeneral['campanias_en_revision'] == 1 ? '' : 's' }} en revisión.
    </div>

    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" class="btn btn-light w-100 btn-sm fw-semibold">
            Cerrar sesión
        </button>
    </form>
</div>
    </aside>

    <div class="main-wrapper w-100">
        <header class="topbar">
            <div>
                <p class="topbar-subtitle mb-1">Panel general</p>
                <h2 class="topbar-title">Campañas Segmentadas por WhatsApp</h2>
            </div>

            <div class="d-flex align-items-center gap-3">
                <button class="btn btn-light border rounded-3 px-3">
                    <i class="bi bi-bell me-2"></i>Alertas
                </button>
               <div class="user-chip">
                <div class="user-avatar">
                    {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                </div>
                <div>
                    <div class="fw-semibold">
                        {{ auth()->user()->name }}
                    </div>
                    <div class="small text-secondary">
                        {{ auth()->user()->rol ?? 'Usuario' }}
                    </div>
                </div>
            </div>
            </div>
        </header>
<main class="content-area">
    @yield('content')
</main>
      
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')
</body>
</html>
