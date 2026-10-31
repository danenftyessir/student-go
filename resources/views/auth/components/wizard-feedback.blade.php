{{-- umpan balik wizard registrasi: galat server dari redirect (blade), ringkasan galat langkah ini (js), galat umum (js). --}}
<x-ui.form-errors />

<div class="sg-wizard__summary" x-show="errors.length" x-cloak>
    <x-ui.alert tone="danger" title="Periksa kembali isian berikut">
        <ul>
            <template x-for="item in errors" :key="item.name">
                <li><a :href="'#' + item.id" x-text="item.message"></a></li>
            </template>
        </ul>
    </x-ui.alert>
</div>

<div x-show="formError" x-cloak x-ref="formError" tabindex="-1" class="sg-wizard__summary">
    <x-ui.alert tone="danger"><span x-text="formError"></span></x-ui.alert>
</div>
