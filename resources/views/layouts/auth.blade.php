<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>BCC Portal</title>
    <link rel="icon" href="{{ asset('images/bcc-logo-web.png') }}">
    <link rel="stylesheet" href="{{ asset('css/tokens.css') }}">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('css/toasts.css') }}">
    <script src="{{ asset('js/toasts.js') }}" defer></script>
</head>
<body class="auth-body">
    <main class="auth-page">
        <section class="auth-card" aria-labelledby="auth-title">
            <a class="auth-brand" href="{{ route('login') }}">
                <img src="{{ asset('images/bcc-logo-web.png') }}" alt="" width="48" height="48">
                <strong>Buenavista Community College</strong>
            </a>
@yield('form')
        </section>
    </main>
    <script>
        (() => {
            const passwordInput = document.querySelector('[data-password-input]');
            const passwordTooltip = document.getElementById('password-requirements');

            if (!passwordInput || !passwordTooltip) {
                return;
            }

            const passwordRules = {
                length: (value) => [...value].length >= 8,
                uppercase: (value) => /[A-Z]/.test(value),
                lowercase: (value) => /[a-z]/.test(value),
                number: (value) => /\d/.test(value),
            };
            let passwordHasInput = passwordInput.value.length > 0;

            const updatePasswordTooltip = () => {
                const value = passwordInput.value;
                let allRequirementsMet = true;

                Object.entries(passwordRules).forEach(([rule, isMet]) => {
                    const ruleElement = passwordTooltip.querySelector(`[data-password-rule="${rule}"]`);
                    const requirementIsMet = isMet(value);

                    ruleElement.hidden = requirementIsMet;
                    allRequirementsMet = allRequirementsMet && requirementIsMet;
                });

                passwordTooltip.querySelector('[data-password-prompt]').hidden = allRequirementsMet;
                passwordTooltip.querySelector('[data-password-complete]').hidden = !allRequirementsMet;
                passwordTooltip.hidden = document.activeElement !== passwordInput && (!passwordHasInput || allRequirementsMet);
            };

            passwordInput.addEventListener('focus', updatePasswordTooltip);
            passwordInput.addEventListener('input', () => {
                passwordHasInput = passwordInput.value.length > 0;
                updatePasswordTooltip();
            });
            passwordInput.addEventListener('blur', updatePasswordTooltip);
        })();
    </script>
    <x-toast-notifications />
</body>
</html>
