{{-- checkbox berlabel. setelah submit gagal, status dicentang mengikuti old() (mendukung name="x[]"). --}}
@props(['name', 'value' => '1', 'label', 'id' => null, 'checked' => false])
@php
    $key = trim(str_replace(['[]', '[', ']'], ['', '.', ''], $name), '.');
    $id = $id ?? str_replace('.', '-', $key).'-'.\Illuminate\Support\Str::slug((string) $value);
    if (session()->hasOldInput()) {
        $old = old($key);
        $isChecked = is_array($old) ? in_array((string) $value, array_map('strval', $old), true) : (string) $old === (string) $value;
    } else {
        $isChecked = (bool) $checked;
    }
@endphp
<label class="sg-check" for="{{ $id }}">
    <input type="checkbox" name="{{ $name }}" id="{{ $id }}" value="{{ $value }}" @checked($isChecked) {{ $attributes }}>
    <span>{{ $label }}</span>
</label>
