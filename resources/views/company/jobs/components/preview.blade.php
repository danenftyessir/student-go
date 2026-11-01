{{-- pratinjau lowongan dari isian wizard (alpine formData). dipakai rail >= 1024px dan modal Pratinjau (D-26: front-end saja,
     tidak menyimpan dan tidak menerbitkan apa pun). $companyName = nama perusahaan yang login.
     $note (opsional, phase 29 paket E) = catatan penutup; bawaan = catatan wizard create. --}}
<article class="sg-co-preview">
    <p class="sg-co-preview__eyebrow">Pratinjau untuk mahasiswa</p>
    <h2 class="sg-co-preview__title" :class="{ 'is-empty': !formData.title.trim() }" x-text="formData.title.trim() || 'Judul lowongan'">Judul lowongan</h2>
    <p class="sg-co-preview__company">{{ $companyName }}</p>

    <ul class="sg-co-preview__facts">
        <li x-show="formData.location.trim()"><x-icon name="map-pin" size="sm" /><span x-text="formData.location"></span></li>
        <li x-show="formData.job_type"><x-icon name="briefcase" size="sm" /><span x-text="jobTypeLabel()"></span></li>
        <li x-show="categoryLabel()"><x-icon name="folder-open" size="sm" /><span x-text="categoryLabel()"></span></li>
        <li x-show="formData.department.trim()"><x-icon name="building-2" size="sm" /><span x-text="formData.department"></span></li>
        <li x-show="formData.salary_range.trim()"><x-icon name="clipboard-list" size="sm" /><span x-text="formData.salary_range"></span></li>
    </ul>

    <template x-for="part in previewParts()" :key="part.label">
        <section class="sg-co-preview__part">
            <h3 x-text="part.label"></h3>
            <p x-text="part.text"></p>
        </section>
    </template>

    <section class="sg-co-preview__part" x-show="formData.skills.length">
        <h3>Keahlian</h3>
        <ul class="sg-co-preview__chips"><template x-for="skill in formData.skills" :key="skill"><li class="sg-chip" x-text="skill"></li></template></ul>
    </section>
    <section class="sg-co-preview__part" x-show="formData.sdg_alignment.length">
        <h3>SDG</h3>
        <ul class="sg-co-preview__chips"><template x-for="id in formData.sdg_alignment" :key="id"><li class="sg-chip" x-text="sdgLabels[id] || ('SDG ' + id)"></li></template></ul>
    </section>

    @isset($note)
    <p class="sg-co-preview__note">{{ $note }}</p>
    @else
    <p class="sg-co-preview__note">Disusun dari isian formulir di peramban ini. Belum ada yang terbit sampai Anda menekan "Buat lowongan".</p>
    @endisset
</article>
