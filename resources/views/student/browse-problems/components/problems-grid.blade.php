{{-- resources/views/student/browse-problems/components/problems-grid.blade.php --}}
{{-- tampilan galeri (opsional, phase 8): kartu per masalah dari halaman yang sama dengan daftar. kosong ditangani di index. --}}
<ul class="sg-browse-grid">
    @foreach ($problems as $problem)
        <li>@include('student.browse-problems.components.problem-card', ['problem' => $problem])</li>
    @endforeach
</ul>
