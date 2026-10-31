{{-- ringkasan galat validasi server di atas formulir (role="alert"). tampil hanya bila $errors berisi. --}}
@props(['title' => 'Periksa kembali isian berikut'])
@if (isset($errors) && $errors->any())
    <x-ui.alert tone="danger" :title="$title" {{ $attributes }}>
        <ul>
            @foreach ($errors->all() as $message)
                <li>{{ $message }}</li>
            @endforeach
        </ul>
    </x-ui.alert>
@endif
