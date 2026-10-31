{{-- foto masalah (phase 9, D-14). seluruh bagian tersembunyi sampai minimal satu foto benar-benar termuat; foto yang gagal
     dibuang beserta keterangannya, jadi tidak pernah ada gambar rusak, kotak alt abu-abu, atau ruang kosong.
     keterangan berasal dari kolom caption; tanpa label "Gallery N". alt kosong karena keterangan tertulis di figcaption. --}}
<section class="sg-brief-section sg-brief-gallery" aria-labelledby="brief-photos"
         x-data="sgBriefGallery(@js($gallery))" x-show="visible" x-cloak>
    <h2 id="brief-photos">Foto</h2>
    <figure class="sg-figure sg-brief-gallery__figure">
        <img :src="images[current].src" alt="" decoding="async" x-on:load="loaded(current)" x-on:error="fail(current)">
        <figcaption x-show="images[current].caption" x-text="images[current].caption"></figcaption>
    </figure>
    <template x-if="images.length > 1">
        <ul class="sg-brief-gallery__thumbs" aria-label="Pilih foto">
            <template x-for="(image, i) in images" :key="i">
                <li x-show="!failed.includes(i)">
                    <button type="button" class="sg-brief-gallery__thumb" @click="show(i)" :aria-pressed="(current === i).toString()"
                            :aria-label="'Tampilkan foto ' + (usable.indexOf(i) + 1) + (image.caption ? ': ' + image.caption : '')">
                        <img :src="image.src" alt="" decoding="async" x-on:load="loaded(i)" x-on:error="fail(i)">
                    </button>
                </li>
            </template>
        </ul>
    </template>
</section>
