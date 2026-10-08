@props([
    'placeholder' => 'Rechercher…',
    'value' => '',
    'name' => 'recherche',
    'id' => 'search-input',
])

<div class="position-relative liste-search-wrap">
    <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted fs-5" aria-hidden="true"></i>
    <input
        type="text"
        id="{{ $id }}"
        name="{{ $name }}"
        class="form-control ps-5 liste-search-input"
        placeholder="{{ $placeholder }}"
        value="{{ $value }}"
        autocomplete="off"
        data-param="{{ $name }}"
        {{ $attributes }}
    >
</div>
