<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Applications') · BCC Portal</title>
    <link rel="icon" href="{{ asset('images/bcc-logo-web.png') }}">
    <link rel="stylesheet" href="{{ asset('css/tokens.css') }}">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('css/portal.css') }}">
    <script src="{{ asset('js/portal.js') }}" defer></script>
    <link rel="stylesheet" href="{{ asset('css/toasts.css') }}">
    <script src="{{ asset('js/toasts.js') }}" defer></script>
</head>
<body class="workspace-body portal">
    @php
        $isRegistrar = auth()->user()->role === 'registrar';
        $navigation = $isRegistrar ? [
            ['Applications', 'registrar.applications.index', 'registrar.applications.*', 'applications'],
            ['Students', 'registrar.students', 'registrar.students', 'students'],
            ['Programs', 'registrar.programs', 'registrar.programs*', 'programs'],
            ['Subjects', 'registrar.subjects', 'registrar.subjects', 'subjects'],
            ['Grades', 'registrar.grades', 'registrar.grades', 'grades'],
        ] : [
            ['My applications', 'student.enrollments.index', 'student.enrollments.index', 'applications'],
            ['New enrollment', 'student.enrollments.create', 'student.enrollments.create', 'plus'],
            ['My grades', 'student.grades', 'student.grades', 'grades'],
        ];
    @endphp
    <a class="skip-link" href="#main-content">Skip to content</a>
    <div class="app-shell">
        <aside class="sidebar" id="portal-navigation" aria-label="{{ $isRegistrar ? 'Registrar' : 'Student' }} navigation">
            <a class="brand-lockup" href="{{ route('dashboard') }}" aria-label="BCC applications">
                <img src="{{ asset('images/bcc-logo-web.png') }}" alt="" width="44" height="44">
                <span><strong>BCC<span class="brand-portal">Portal</span></strong><small>Buenavista Community College</small></span>
            </a>
            <button class="menu-close" type="button" aria-label="Close navigation"><x-portal-icon name="close" /></button>
            <p class="nav-caption">{{ $isRegistrar ? 'Registrar' : 'Student' }} workspace</p>
            <nav class="primary-nav" aria-label="Main navigation">
                @foreach ($navigation as [$label, $route, $match, $icon])
                    @php($isActive = request()->routeIs($match))
                    <a href="{{ route($route) }}" @class(['nav-link', 'is-active' => $isActive]) @if($isActive) aria-current="page" @endif>
                        <x-portal-icon :name="$icon" /><span>{{ $label }}</span>
                    </a>
                @endforeach
            </nav>
        </aside>
        <button class="nav-backdrop" type="button" aria-label="Close navigation" tabindex="-1" hidden></button>
        <div class="main-area">
            <header class="topbar">
                <div class="topbar-location">
                    <button class="menu-toggle" type="button" aria-expanded="false" aria-controls="portal-navigation" aria-label="Open navigation"><x-portal-icon name="menu" /></button>
                    <span class="portal-label">{{ $isRegistrar ? 'Registrar' : 'Student' }} portal</span>
                    <span class="breadcrumb-divider" aria-hidden="true">/</span><span class="current-page">@yield('title')</span>
                </div>
                <details class="account-dropdown">
                    <summary class="account-toggle" aria-label="Account menu">
                        <span class="account-icon"><x-portal-icon :name="$isRegistrar ? 'person' : 'student'" /></span>
                        <x-portal-icon name="chevron" class="account-chevron" />
                    </summary>
                    <div class="account-panel">
                        <p class="account-name">{{ auth()->user()->first_name }} {{ auth()->user()->last_name }}</p>
                        <form class="sign-out-form" method="post" action="{{ route('logout') }}">
                            @csrf
                            <button class="account-sign-out" type="submit"><x-portal-icon name="logout" /><span>Sign out</span></button>
                        </form>
                    </div>
                </details>
            </header>
            <main class="page-wrap" id="main-content" tabindex="-1">
@yield('content')
            </main>
        </div>
    </div>
    <x-toast-notifications />
</body>
</html>
