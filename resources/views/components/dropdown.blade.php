@props(['align' => 'right', 'width' => '48', 'contentClasses' => 'bg-white'])

<div class="dropdown">
    <div class="dropdown-toggle" role="button" data-bs-toggle="dropdown">
        {{ $trigger }}
    </div>
    <div class="dropdown-menu {{ $align === 'left' ? '' : 'dropdown-menu-end' }} {{ $contentClasses }}">
        {{ $content }}
    </div>
</div>