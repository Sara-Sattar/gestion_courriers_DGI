@props(['name', 'show' => false, 'maxWidth' => 'md'])

@php
$id = $name ?? md5($attributes->wire('model'));
@endphp

<div class="modal fade" id="modal-{{ $id }}" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body">
                {{ $slot }}
            </div>
        </div>
    </div>
</div>