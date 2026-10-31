{{-- rel metadata rekaman (definition list). items = [label => nilai] (nilai di-escape); untuk isi kaya, tulis
     <div><dt>…</dt><dd>…</dd></div> di slot. inline = label dan nilai sebaris mulai 640px. --}}
@props(['items' => [], 'inline' => false])
<dl {{ $attributes->class(['sg-dl', 'sg-dl--inline' => $inline]) }}>
    @foreach ($items as $term => $description)
        @if (! is_null($description) && $description !== '')
            <div>
                <dt>{{ $term }}</dt>
                <dd>{{ $description }}</dd>
            </div>
        @endif
    @endforeach
    {{ $slot }}
</dl>
