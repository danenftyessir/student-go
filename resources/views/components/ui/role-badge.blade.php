{{-- badge peran: hanya di menu akun (dan header profil publik). role = users.user_type --}}
@props(['role'])
@php
    [$label, $icon] = match ($role) {
        'student' => ['Mahasiswa', 'graduation-cap'],
        'institution' => ['Instansi', 'building-2'],
        'company' => ['Perusahaan', 'briefcase'],
        'admin' => ['Admin', 'shield-check'],
        default => [ucfirst((string) $role), 'user'],
    };
@endphp
<span {{ $attributes->class('sg-role') }}><x-icon :name="$icon" />{{ $label }}</span>
