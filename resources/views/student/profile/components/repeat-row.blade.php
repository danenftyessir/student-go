{{-- satu baris repeat-list; nomor dan aria-label diperbarui sgRepeatList.renumber() setelah tambah/hapus --}}
<li class="list-none" data-row>
    <div class="flex items-start gap-2">
        @if ($multiline)
            <textarea name="{{ $field }}[]" rows="4" maxlength="{{ $maxlength }}" class="sg-textarea flex-1"
                      aria-label="{{ $label }} {{ $number }}" placeholder="{{ $placeholder }}"
                      @if ($error) aria-invalid="true" data-touched @endif>{{ $value }}</textarea>
        @else
            <input type="text" name="{{ $field }}[]" value="{{ $value }}" maxlength="{{ $maxlength }}" class="sg-input flex-1"
                   aria-label="{{ $label }} {{ $number }}" placeholder="{{ $placeholder }}"
                   @if ($error) aria-invalid="true" data-touched @endif>
        @endif
        <button type="button" class="sg-icon-btn sg-icon-btn--bordered flex-none" data-remove @click="remove($el)"
                aria-label="Hapus {{ \Illuminate\Support\Str::lower($label) }} {{ $number }}">
            <x-icon name="trash-2" size="sm" />
        </button>
    </div>
    @if ($error)
        <p class="sg-error"><x-icon name="circle-alert" size="sm" />{{ $error }}</p>
    @endif
</li>
