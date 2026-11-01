{{-- lima langkah wizard lowongan (phase 23), dipakai create dan edit (phase 29 paket E). isian terikat ke alpine formData;
     tidak ada atribut name (payload dibangun submitForm() halaman). parameter:
     $jobTypes (nilai => label), $categories (id => nama), $departments, $availableSkills, $sdgOptions,
     $statusOptions (opsional, hanya edit: nilai => label StatusVocabulary), $editing (opsional, true di edit: gaji tersimpan
     $salaryText hanya ditampilkan; selain itu tampil isian salary_range), $finalNote (catatan di langkah 5). --}}
@php
    $statusOptions ??= null;
    $editing ??= false;
    $salaryText ??= null;
@endphp

            {{-- langkah 1: detail lowongan --}}
            <section class="sg-co-wizard__step" x-show="currentStep === 0" aria-labelledby="langkah-1">
                <h2 id="langkah-1" tabindex="-1">1. Detail Lowongan</h2>
                <div class="sg-co-wizard__grid">
                    <div class="sg-field sg-co-wizard__wide">
                        <label for="job-title" class="sg-label" data-required>Judul lowongan</label>
                        <input id="job-title" type="text" class="sg-input" maxlength="255" autocomplete="off"
                               x-model="formData.title" @blur="touch('title')" aria-describedby="job-title-error"
                               :aria-invalid="errors.title ? 'true' : null" :data-touched="errors.title ? '' : null"
                               placeholder="cth: Pendamping Koperasi Desa">
                        <p id="job-title-error" class="sg-error" x-show="errors.title" x-cloak><x-icon name="circle-alert" size="sm" /><span x-text="errors.title"></span></p>
                    </div>

                    <div class="sg-field">
                        <label for="job-department" class="sg-label" data-required>Departemen atau bidang</label>
                        <input id="job-department" type="text" class="sg-input" maxlength="100" list="departments" autocomplete="off"
                               x-model="formData.department" @blur="touch('department')" aria-describedby="job-department-help job-department-error"
                               :aria-invalid="errors.department ? 'true' : null" :data-touched="errors.department ? '' : null"
                               placeholder="cth: Pemberdayaan Masyarakat">
                        <datalist id="departments">
                            @foreach ($departments as $dept)
                                <option value="{{ $dept }}">
                            @endforeach
                        </datalist>
                        <p id="job-department-help" class="sg-help">Pilih dari saran atau tulis sendiri.</p>
                        <p id="job-department-error" class="sg-error" x-show="errors.department" x-cloak><x-icon name="circle-alert" size="sm" /><span x-text="errors.department"></span></p>
                    </div>

                    <div class="sg-field">
                        <label for="job-type" class="sg-label" data-required>Tipe pekerjaan</label>
                        <select id="job-type" class="sg-select" x-model="formData.job_type" @blur="touch('job_type')" @change="touch('job_type')"
                                aria-describedby="job-type-error" :aria-invalid="errors.job_type ? 'true' : null" :data-touched="errors.job_type ? '' : null">
                            <option value="">Pilih tipe pekerjaan</option>
                            @foreach ($jobTypes as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        <p id="job-type-error" class="sg-error" x-show="errors.job_type" x-cloak><x-icon name="circle-alert" size="sm" /><span x-text="errors.job_type"></span></p>
                    </div>

                    @if ($statusOptions)
                        <div class="sg-field">
                            <label for="job-status" class="sg-label">Status</label>
                            <select id="job-status" class="sg-select" x-model="formData.status" @change="touch('status')"
                                    aria-describedby="job-status-help job-status-error" :aria-invalid="errors.status ? 'true' : null" :data-touched="errors.status ? '' : null">
                                @foreach ($statusOptions as $status => $label)
                                    <option value="{{ $status }}">{{ $label }}</option>
                                @endforeach
                            </select>
                            <p id="job-status-help" class="sg-help">Hanya lowongan berstatus Dibuka yang tampil untuk mahasiswa.</p>
                            <p id="job-status-error" class="sg-error" x-show="errors.status" x-cloak><x-icon name="circle-alert" size="sm" /><span x-text="errors.status"></span></p>
                        </div>
                    @endif

                    <div class="sg-field">
                        <label for="job-category" class="sg-label">Kategori <span class="sg-co-wizard__optional">(opsional)</span></label>
                        <select id="job-category" class="sg-select" x-model="formData.job_category_id" @change="touch('job_category_id')"
                                aria-describedby="job-category-help job-category-error" :aria-invalid="errors.job_category_id ? 'true' : null" :data-touched="errors.job_category_id ? '' : null">
                            <option value="">Tanpa kategori</option>
                            @foreach ($categories as $id => $name)
                                <option value="{{ $id }}">{{ $name }}</option>
                            @endforeach
                        </select>
                        <p id="job-category-help" class="sg-help">Dipakai untuk filter di daftar lowongan.</p>
                        <p id="job-category-error" class="sg-error" x-show="errors.job_category_id" x-cloak><x-icon name="circle-alert" size="sm" /><span x-text="errors.job_category_id"></span></p>
                    </div>

                    <div class="sg-field">
                        <label for="job-location" class="sg-label" data-required>Lokasi</label>
                        <input id="job-location" type="text" class="sg-input" maxlength="255" autocomplete="off"
                               x-model="formData.location" @blur="touch('location')" aria-describedby="job-location-error"
                               :aria-invalid="errors.location ? 'true' : null" :data-touched="errors.location ? '' : null"
                               placeholder="cth: Kabupaten Garut, Jawa Barat">
                        <p id="job-location-error" class="sg-error" x-show="errors.location" x-cloak><x-icon name="circle-alert" size="sm" /><span x-text="errors.location"></span></p>
                    </div>

                    @if ($editing)
                        {{-- gaji hanya ditampilkan: update() mempertahankan gaji tersimpan, jadi isian di sini tidak akan tersimpan --}}
                        <div class="sg-co-wizard__wide sg-co-wizard__fixed">
                            <p class="sg-label">Gaji atau honor</p>
                            <p class="sg-co-wizard__fixed-value">{{ $salaryText ?? 'Belum diisi' }}</p>
                            <p class="sg-help">Gaji yang sudah tersimpan tidak berubah lewat formulir ini.</p>
                        </div>
                    @else
                    <div class="sg-field sg-co-wizard__wide">
                        <label for="job-salary" class="sg-label">Rentang gaji atau honor <span class="sg-co-wizard__optional">(opsional)</span></label>
                        <input id="job-salary" type="text" class="sg-input" maxlength="255" autocomplete="off"
                               x-model="formData.salary_range" @blur="touch('salary_range')" aria-describedby="job-salary-help job-salary-error"
                               :aria-invalid="errors.salary_range ? 'true' : null" :data-touched="errors.salary_range ? '' : null"
                               placeholder="cth: Rp 3 juta - 4,5 juta per bulan">
                        <p id="job-salary-help" class="sg-help">Tulis satu angka atau rentang dalam rupiah, boleh dengan "juta", dan "per bulan" atau "per proyek".</p>
                        <p id="job-salary-error" class="sg-error" x-show="errors.salary_range" x-cloak><x-icon name="circle-alert" size="sm" /><span x-text="errors.salary_range"></span></p>
                    </div>
                    @endif
                </div>
            </section>

            {{-- langkah 2: deskripsi --}}
            <section class="sg-co-wizard__step" x-show="currentStep === 1" x-cloak aria-labelledby="langkah-2">
                <h2 id="langkah-2" tabindex="-1">2. Deskripsi</h2>
                <div class="sg-co-wizard__stack">
                    <div class="sg-field">
                        <label for="job-description" class="sg-label" data-required>Deskripsi pekerjaan</label>
                        <textarea id="job-description" class="sg-textarea" rows="5" x-model="formData.description" @blur="touch('description')"
                                  aria-describedby="job-description-error" :aria-invalid="errors.description ? 'true' : null" :data-touched="errors.description ? '' : null"
                                  placeholder="cth: Mendampingi pengurus koperasi tani beralih dari buku kas ke pencatatan digital selama tiga bulan."></textarea>
                        <p id="job-description-error" class="sg-error" x-show="errors.description" x-cloak><x-icon name="circle-alert" size="sm" /><span x-text="errors.description"></span></p>
                    </div>
                    <div class="sg-field">
                        <label for="job-responsibilities" class="sg-label" data-required>Tanggung jawab</label>
                        <textarea id="job-responsibilities" class="sg-textarea" rows="5" x-model="formData.responsibilities" @blur="touch('responsibilities')"
                                  aria-describedby="job-responsibilities-help job-responsibilities-error" :aria-invalid="errors.responsibilities ? 'true' : null" :data-touched="errors.responsibilities ? '' : null"
                                  placeholder="- Melatih pengurus mencatat transaksi harian&#10;- Menyusun laporan bulanan koperasi&#10;- Berkoordinasi dengan perangkat desa"></textarea>
                        <p id="job-responsibilities-help" class="sg-help">Satu tanggung jawab per baris.</p>
                        <p id="job-responsibilities-error" class="sg-error" x-show="errors.responsibilities" x-cloak><x-icon name="circle-alert" size="sm" /><span x-text="errors.responsibilities"></span></p>
                    </div>
                    <div class="sg-field">
                        <label for="job-qualifications" class="sg-label" data-required>Kualifikasi</label>
                        <textarea id="job-qualifications" class="sg-textarea" rows="5" x-model="formData.qualifications" @blur="touch('qualifications')"
                                  aria-describedby="job-qualifications-error" :aria-invalid="errors.qualifications ? 'true' : null" :data-touched="errors.qualifications ? '' : null"
                                  placeholder="- Mahasiswa aktif minimal semester 5&#10;- Pernah terlibat kegiatan KKN atau pengabdian masyarakat&#10;- Terbiasa memakai spreadsheet"></textarea>
                        <p id="job-qualifications-error" class="sg-error" x-show="errors.qualifications" x-cloak><x-icon name="circle-alert" size="sm" /><span x-text="errors.qualifications"></span></p>
                    </div>
                </div>
            </section>

            {{-- langkah 3: keahlian --}}
            <section class="sg-co-wizard__step" x-show="currentStep === 2" x-cloak aria-labelledby="langkah-3">
                <h2 id="langkah-3" tabindex="-1">3. Keahlian</h2>
                <fieldset class="sg-fieldset" aria-describedby="job-skills-error" :aria-invalid="errors.skills ? 'true' : null">
                    <legend class="sg-label" data-required>Keahlian yang dibutuhkan</legend>
                    <p class="sg-help"><span x-text="formData.skills.length"></span> dipilih. Pilih minimal satu.</p>
                    <div class="sg-co-wizard__checks sg-co-wizard__checks--skills">
                        @foreach ($availableSkills as $skill)
                            <label class="sg-check"><input type="checkbox" value="{{ $skill }}" x-model="formData.skills" @change="touch('skills')"><span>{{ $skill }}</span></label>
                        @endforeach
                    </div>
                    <p id="job-skills-error" class="sg-error" x-show="errors.skills" x-cloak><x-icon name="circle-alert" size="sm" /><span x-text="errors.skills"></span></p>
                </fieldset>
            </section>

            {{-- langkah 4: SDG Alignment (khas produk; dipertahankan) --}}
            <section class="sg-co-wizard__step" x-show="currentStep === 3" x-cloak aria-labelledby="langkah-4">
                <h2 id="langkah-4" tabindex="-1">4. SDG Alignment</h2>
                <fieldset class="sg-fieldset">
                    <legend class="sg-label">SDG yang relevan dengan posisi ini <span class="sg-co-wizard__optional">(opsional)</span></legend>
                    <p class="sg-help"><span x-text="formData.sdg_alignment.length"></span> dipilih. Mahasiswa melihat SDG ini di lowongan.</p>
                    <div class="sg-co-wizard__checks">
                        @foreach ($sdgOptions as $sdg)
                            <label class="sg-check"><input type="checkbox" value="{{ $sdg['id'] }}" x-model="formData.sdg_alignment"><span>{{ $sdg['name'] }}</span></label>
                        @endforeach
                    </div>
                </fieldset>
            </section>

            {{-- langkah 5: Kriteria Dampak (khas produk; dipertahankan) --}}
            <section class="sg-co-wizard__step" x-show="currentStep === 4" x-cloak aria-labelledby="langkah-5">
                <h2 id="langkah-5" tabindex="-1">5. Kriteria Dampak</h2>
                <div class="sg-co-wizard__stack">
                    <div class="sg-field">
                        <label for="job-impact" class="sg-label">Metrik dampak <span class="sg-co-wizard__optional">(opsional)</span></label>
                        <textarea id="job-impact" class="sg-textarea" rows="4" x-model="formData.impact_metrics" aria-describedby="job-impact-help"
                                  placeholder="cth: 30 anggota koperasi mencatat penjualan hasil panen secara digital setiap pekan"></textarea>
                        <p id="job-impact-help" class="sg-help">Bagaimana dampak posisi ini diukur di lapangan.</p>
                    </div>
                    <div class="sg-field">
                        <label for="job-success" class="sg-label">Kriteria keberhasilan <span class="sg-co-wizard__optional">(opsional)</span></label>
                        <textarea id="job-success" class="sg-textarea" rows="4" x-model="formData.success_criteria" aria-describedby="job-success-help"
                                  placeholder="cth: Laporan keuangan bulanan koperasi tersusun tanpa bantuan pendamping pada bulan ketiga"></textarea>
                        <p id="job-success-help" class="sg-help">Apa yang menandakan kandidat berhasil di posisi ini.</p>
                    </div>
                </div>
                <p class="sg-co-wizard__publish-note">{{ $finalNote }}</p>
            </section>
