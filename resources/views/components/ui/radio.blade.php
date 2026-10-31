{{-- radio berlabel; kelompokkan dalam x-ui.fieldset. terpilih: old(name, checked ? value : null) --}}
@props(['name', 'value', 'label', 'id' => null, 'checked' => false])
@php
    $key = trim(str_replace(['[]', '[', ']'], ['', '.', ''], $name), '.');
    $id = $id ?? str_replace('.', '-', $key).'-'.\Illuminate\Support\Str::slug((string) $value);
    $isChecked = session()->hasOldInput() ? (string) old($key) === (string) $value : (bool) $checked;
@endphp
<label class="sg-check" for="{{ $id }}">
    <input type="radio" name="{{ $name }}" id="{{ $id }}" value="{{ $value }}" @checked($isChecked) {{ $attributes }}>
    <span>{{ $label }}</span>
</label>
