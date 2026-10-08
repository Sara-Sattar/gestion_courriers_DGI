@props([
    'type' => 'date',
    'param',
    'value' => '',
    'title' => null,
    'placeholder' => null,
    'min' => null,
    'max' => null,
    'largeur' => null,
])

@php
    $classes = 'form-control liste-select';
    if ($type === 'date' || $largeur === 'lg') {
        $classes .= ' liste-select-lg';
    }
@endphp

<div class="liste-filtre-effacable" data-filtre-effacable>
    <input
        type="{{ $type }}"
        class="{{ $classes }}"
        data-param="{{ $param }}"
        value="{{ $value }}"
        @if ($title) title="{{ $title }}" @endif
        @if ($placeholder) placeholder="{{ $placeholder }}" @endif
        @if ($min !== null) min="{{ $min }}" @endif
        @if ($max !== null) max="{{ $max }}" @endif
        {{ $attributes }}
    >
    <button
        type="button"
        class="liste-filtre-clear"
        data-clear-filtre
        title="Effacer"
        aria-label="Effacer le filtre"
        @if ($value === null || $value === '') hidden @endif
    >
        <i class="bi bi-x-lg" aria-hidden="true"></i>
    </button>
</div>
