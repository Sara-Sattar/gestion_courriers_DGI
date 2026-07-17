<option value="{{ $unite->id }}">{{ str_repeat('—', $niveau) }} {{ $unite->nom }}</option>
@foreach($unite->enfants as $child)
    @include('partials.unite-option', ['unite' => $child, 'niveau' => $niveau + 1])
@endforeach
