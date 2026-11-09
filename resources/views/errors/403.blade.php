<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>403 Access Restricted · BCC Portal</title>
    <link rel="icon" href="{{ asset('images/bcc-logo-web.png') }}">
    <link rel="stylesheet" href="{{ asset('css/tokens.css') }}">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('css/portal.css') }}">
</head>
<body class="workspace-body portal">
    <div class="auth-card" style="margin: 80px auto; max-width: 480px; text-align: center;">
        <div style="margin-bottom: 20px;">
            <img src="{{ asset('images/bcc-logo-web.png') }}" alt="BCC Logo" width="60" height="60">
        </div>
        <span class="status-badge status-rejected" style="margin-bottom: 12px; display: inline-flex;">403 · Access Restricted</span>
        <h1 style="font-size: 22px; font-weight: 700; color: #14271c; margin: 0 0 10px;">Permission Required</h1>
        <p style="color: #556c5f; font-size: 14px; line-height: 1.5; margin: 0 0 24px;">
            {{ $exception->getMessage() ?: 'You do not have permission to access this page with your current account.' }}
        </p>

        @auth
            <div style="background: #f5f8f6; border: 1px solid #e1ebe4; border-radius: 8px; padding: 12px; margin-bottom: 24px; text-align: left; font-size: 13px;">
                <p style="margin: 0 0 4px; color: #647b6e; font-size: 11px; font-weight: 700; text-transform: uppercase;">Currently Signed In As</p>
                <strong style="color: #14271c;">{{ auth()->user()->first_name }} {{ auth()->user()->last_name }}</strong>
                <span style="color: #556c5f;">({{ ucfirst(auth()->user()->role) }})</span>
            </div>
            <div style="display: flex; gap: 12px; justify-content: center; flex-wrap: wrap;">
                <a href="{{ route('dashboard') }}" class="button button-primary">Go to Dashboard</a>
                <form method="post" action="{{ route('logout') }}" style="display: inline;">
                    @csrf
                    <button type="submit" class="button button-quiet">Sign out to switch account</button>
                </form>
            </div>
        @else
            <div style="display: flex; gap: 12px; justify-content: center;">
                <a href="{{ route('login') }}" class="button button-primary">Sign in</a>
            </div>
        @endauth
    </div>
</body>
</html>
