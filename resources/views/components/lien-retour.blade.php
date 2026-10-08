@props([
    'href',
])

<a {{ $attributes->merge(['href' => $href, 'class' => 'lien-retour']) }}>
    <i class="bi bi-chevron-left" aria-hidden="true"></i>
    <span>{{ $slot }}</span>
</a>

@once
    @push('styles')
    <style>
        .lien-retour {
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
            margin-bottom: 0.65rem;
            padding: 0.3rem 0.7rem 0.3rem 0.45rem;
            font-size: 0.8125rem;
            font-weight: 500;
            line-height: 1.2;
            color: #495057;
            text-decoration: none;
            background-color: #fff;
            border: 1px solid #dee2e6;
            border-radius: 0.375rem;
            box-shadow: 0 1px 1px rgba(0, 0, 0, 0.03);
            transition: color 0.15s, border-color 0.15s, background-color 0.15s;
        }

        .lien-retour .bi {
            font-size: 0.85rem;
        }

        .lien-retour:hover {
            color: #0d47a1;
            background-color: rgba(13, 71, 161, 0.05);
            border-color: rgba(13, 71, 161, 0.35);
        }

        .lien-retour:focus-visible {
            outline: 2px solid rgba(13, 71, 161, 0.35);
            outline-offset: 2px;
        }
    </style>
    @endpush
@endonce
