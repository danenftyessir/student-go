{{-- input password + tombol tampil/sembunyi (phase 6). membungkus x-ui.input, jadi name/id/galat server sama seperti input biasa.
     rules = tampilkan daftar syarat Password::min(8)->mixedCase()->numbers()->symbols() yang dicek saat mengetik
     (dipakai sebagai aria-describedby "{id}-help"). atribut lain (autocomplete, required, data-*) diteruskan ke <input>. --}}
@props(['name' => 'password', 'id' => null, 'rules' => false])
@php
    $id = $id ?? $name;
@endphp
<div class="sg-password" x-data="sgPassword" @input="value = $event.target.id === @js($id) ? $event.target.value : value">
    <div class="sg-password__control">
        <x-ui.input :name="$name" :id="$id" type="password" :help="(bool) $rules" x-bind:type="show ? 'text' : 'password'" :data-sg-password-rules="$rules ? '1' : null" {{ $attributes }} />
        <button type="button" class="sg-password__toggle" aria-controls="{{ $id }}" aria-label="Tampilkan password"
                x-bind:aria-pressed="show.toString()" aria-pressed="false" @click="show = !show">
            <x-icon name="eye" x-show="!show" />
            <x-icon name="eye-off" x-show="show" x-cloak />
        </button>
    </div>
    @if ($rules)
        <ul class="sg-password__rules" id="{{ $id }}-help">
            @foreach (['length' => 'Minimal 8 karakter', 'mixed' => 'Huruf besar dan huruf kecil', 'number' => 'Setidaknya satu angka', 'symbol' => 'Setidaknya satu simbol, misalnya ! @ # atau _'] as $rule => $text)
                <li x-bind:data-met="met(@js($rule)) ? '' : null">
                    <x-icon name="check" size="sm" class="sg-password__met" />
                    <x-icon name="circle-dashed" size="sm" class="sg-password__unmet" />
                    <span>{{ $text }}<span class="sr-only" x-text="met(@js($rule)) ? ' (terpenuhi)' : ''"></span></span>
                </li>
            @endforeach
        </ul>
    @endif
</div>
