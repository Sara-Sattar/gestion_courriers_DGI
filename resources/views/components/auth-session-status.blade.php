@props(['status'])

@if ($status)
    <div {{ $attributes->merge(['class' => 'login-alert login-alert--success']) }} role="status">
        {{ $status }}
    </div>
@endif
