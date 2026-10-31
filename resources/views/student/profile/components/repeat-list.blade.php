{{-- phase 12: daftar isian berulang (stories[], skills[], interests[]) di edit profil. nilai tersimpan dirender di server
     (old() setelah galat); baris baru diklon dari <template>. name tetap "{field}[]", galat per baris "{field}.{n}".
     variabel: $field, $label (tunggal, mis. 'Keterampilan'), $items, $multiline, $maxlength, $placeholder, $addLabel --}}
@php
    $items = array_values(array_filter((array) $items, fn ($v) => is_string($v) || is_numeric($v)));
@endphp
<div x-data="sgRepeatList(@js($label))">
    <ol class="m-0 grid gap-3 p-0" x-ref="list" aria-label="{{ $label }}">
        @foreach ($items as $i => $item)
            @include('student.profile.components.repeat-row', ['value' => $item, 'number' => $i + 1, 'error' => $errors->first($field . '.' . $i)])
        @endforeach
    </ol>
    <template x-ref="row">
        @include('student.profile.components.repeat-row', ['value' => '', 'number' => 0, 'error' => null])
    </template>
    <x-ui.button variant="secondary" icon="plus" class="mt-3" x-ref="add" @click="add()">{{ $addLabel }}</x-ui.button>
</div>
