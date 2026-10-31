{{-- ikon lucide dari sprite lokal (phase 2). nama ikon: public/assets/icons/ui/icons.json --}}
{{-- size: 'sm' = 16px, null = 20px, 'lg' = 24px. tanpa label = dekoratif (aria-hidden) --}}
@props(['name', 'size' => null, 'label' => null])
<svg {{ $attributes->class(['sg-icon', 'sg-icon--' . $size => in_array($size, ['sm', 'lg'], true)]) }} @if ($label) role="img" aria-label="{{ $label }}" @else aria-hidden="true" @endif focusable="false"><use href="{{ asset('assets/icons/ui/sprite.svg') }}#{{ $name }}"></use></svg>
