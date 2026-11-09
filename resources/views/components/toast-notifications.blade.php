<div class="toast-stack" aria-label="Notifications">
    @foreach (['success', 'status', 'error', 'warning', 'info'] as $type)
        @if (session($type))
            <div class="app-toast {{ in_array($type, ['error', 'warning']) ? 'app-toast-error' : '' }}" role="{{ in_array($type, ['error', 'warning']) ? 'alert' : 'status' }}" data-toast @if(in_array($type, ['success', 'status', 'info'])) data-autohide @endif>
                <span>{{ session($type) }}</span>
                <button type="button" data-toast-close aria-label="Dismiss notification">&times;</button>
            </div>
        @endif
    @endforeach
    @if ($errors->any())
        <div class="app-toast app-toast-error" role="alert" data-toast>
            <div>@foreach ($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div>
            <button type="button" data-toast-close aria-label="Dismiss validation errors">&times;</button>
        </div>
    @endif
</div>
