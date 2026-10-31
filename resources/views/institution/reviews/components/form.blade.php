{{-- phase 19: isian ulasan untuk create dan edit. nama field sama seperti sebelumnya: rating (1-5), review, strengths,
     improvements (+ would_collaborate_again di create). rating sekarang radio berlabel (dulu input hidden + tombol bintang);
     nilai yang dikirim tetap 1-5. aturan server: review wajib maks. 1000, strengths/improvements maks. 500. --}}
@php
    $labels = [1 => 'Kurang', 2 => 'Cukup', 3 => 'Baik', 4 => 'Sangat baik', 5 => 'Istimewa'];
    $currentRating = (int) round((float) old('rating', $review?->rating ?? 0));
@endphp
<div class="sg-inst-form sg-inst-page__narrow">
    <x-ui.fieldset legend="Rating keseluruhan" name="rating" required help="Penilaian Anda atas kerja mahasiswa selama proyek.">
        <div class="sg-inst-rating-input">
            @foreach ($labels as $value => $label)
                <x-ui.radio name="rating" :value="$value" :label="$value.' · '.$label" :checked="$currentRating === $value" required />
            @endforeach
        </div>
    </x-ui.fieldset>

    <x-ui.field label="Ulasan" for="review" required help="Maksimal 1.000 karakter.">
        <x-ui.textarea name="review" :value="$review?->review_text" rows="6" maxlength="1000" required help
                       placeholder="Ceritakan kontribusi, sikap kerja dan hasil yang dicapai mahasiswa" />
    </x-ui.field>

    <x-ui.field label="Kekuatan (opsional)" for="strengths" help="Maksimal 500 karakter.">
        <x-ui.textarea name="strengths" :value="$review?->strengths" rows="3" maxlength="500" help />
    </x-ui.field>

    <x-ui.field label="Saran pengembangan (opsional)" for="improvements" help="Maksimal 500 karakter.">
        <x-ui.textarea name="improvements" :value="$review?->improvements" rows="3" maxlength="500" help />
    </x-ui.field>

    @if ($review === null)
        <x-ui.checkbox name="would_collaborate_again" id="would_collaborate_again" value="1" label="Bersedia bekerja sama lagi dengan mahasiswa ini" />

    @endif

    <div class="sg-inst-decision__actions">
        <x-ui.button type="submit">{{ $submitLabel }}</x-ui.button>
        <x-ui.button variant="tertiary" :href="$cancelHref">Batal</x-ui.button>
    </div>
</div>
