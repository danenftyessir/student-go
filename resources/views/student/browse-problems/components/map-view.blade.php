{{-- resources/views/student/browse-problems/components/map-view.blade.php --}}
{{-- tampilan peta (AD-08, phase 8): leaflet + markercluster dan data yang sama seperti sebelumnya (semua hasil filter, tanpa paginasi),
     titik dari koordinat provinsi. phase 8: panel "Filter Peta" yang menggandakan filter halaman dihapus (disetujui user),
     isi popup di-escape, pin paket student go, penanda bisa dibuka dengan keyboard. --}}

<div x-data="mapView()" class="sg-browse-map">
    <div id="problems-map" class="sg-browse-map__canvas" role="region" aria-label="Peta lokasi masalah KKN"></div>
    <p class="sg-browse-map__note">
        Penanda ditempatkan di titik provinsi, bukan lokasi desa yang tepat. Angka pada kotak = jumlah masalah yang digabung.
    </p>
    <div x-show="!map" class="sg-browse-map__loading">
        <x-ui.loading label="Memuat peta…" />
    </div>
</div>

@push('scripts')
<script>
function mapView() {
    return {
        map: null,
        markerCluster: null,
        problems: @json($mapProblems ?? []),
        pinUrl: @js(asset('assets/illustrations/motifs/map-pin.svg')),
        detailUrl: @js(url('/student/browse-problems')),

        init() {
            if (typeof L === 'undefined') return;
            // peta dibuat saat tampilan peta pertama kali dibuka (wadah tersembunyi tidak punya ukuran)
            window.addEventListener('mapViewActivated', () => {
                if (this.map) {
                    setTimeout(() => this.map.invalidateSize(), 100);
                    return;
                }
                setTimeout(() => {
                    this.initializeMap();
                    this.loadMarkers();
                }, 100);
            });
        },

        initializeMap() {
            const container = document.getElementById('problems-map');
            if (!container || container.offsetParent === null) return;

            this.map = L.map('problems-map', { center: [-2.5, 118], zoom: 5, zoomControl: true, scrollWheelZoom: true });
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors',
                maxZoom: 19,
            }).addTo(this.map);

            this.markerCluster = L.markerClusterGroup({
                maxClusterRadius: 50,
                spiderfyOnMaxZoom: true,
                showCoverageOnHover: false,
                zoomToBoundsOnClick: true,
                iconCreateFunction: (cluster) => L.divIcon({
                    html: '<span>' + Number(cluster.getChildCount()) + '</span>',
                    className: 'sg-browse-cluster',
                    iconSize: L.point(36, 36),
                }),
            });
            this.map.addLayer(this.markerCluster);
        },

        loadMarkers() {
            if (!this.markerCluster || !Array.isArray(this.problems)) return;
            this.markerCluster.clearLayers();
            const markers = this.problems.map((problem) => this.createMarker(problem)).filter(Boolean);
            markers.forEach((marker) => this.markerCluster.addLayer(marker));
            if (markers.length) {
                this.map.fitBounds(new L.featureGroup(markers).getBounds().pad(0.1));
            }
        },

        createMarker(problem) {
            const coords = this.getProvinceCoordinates(problem);
            if (!coords) return null;
            const icon = L.divIcon({
                className: 'sg-browse-pin',
                html: '<img src="' + this.pinUrl + '" alt="" width="28" height="38">',
                iconSize: [28, 38],
                iconAnchor: [14, 38],
                popupAnchor: [0, -34],
            });
            const marker = L.marker([problem.latitude || coords.lat, problem.longitude || coords.lng], {
                icon,
                keyboard: true,
                title: problem.title || '',
                alt: problem.title || '',
            });
            marker.bindPopup(this.createPopupContent(problem), { maxWidth: 280, minWidth: 200, className: 'sg-browse-popup' });
            // enter/spasi pada penanda divIcon membuka popup
            marker.on('add', () => {
                const el = marker.getElement();
                if (!el) return;
                el.setAttribute('role', 'button');
                el.setAttribute('aria-label', 'Masalah: ' + (problem.title || ''));
                el.addEventListener('keydown', (e) => {
                    if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); marker.openPopup(); }
                });
            });
            return marker;
        },

        escape(value) {
            return String(value ?? '').replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
        },

        createPopupContent(problem) {
            const e = (v) => this.escape(v);
            const days = this.calculateDaysLeft(problem.application_deadline);
            const place = [problem.regency?.name, problem.province?.name].filter(Boolean).join(', ');
            const flags = [problem.is_urgent ? 'Mendesak' : null, problem.is_featured ? 'Unggulan' : null].filter(Boolean).join(' · ');
            return '<div class="sg-browse-popup__body">'
                + '<p class="sg-browse-popup__title">' + e(problem.title) + '</p>'
                + (problem.institution?.name ? '<p class="sg-browse-popup__meta">' + e(problem.institution.name) + '</p>' : '')
                + (place ? '<p class="sg-browse-popup__meta">' + e(place) + '</p>' : '')
                + '<p class="sg-browse-popup__meta">Tenggat ' + (days > 0 ? Number(days) + ' hari lagi' : 'hari ini') + (flags ? ' · ' + e(flags) : '') + '</p>'
                + '<a class="sg-btn sg-btn--secondary sg-btn--block" href="' + this.detailUrl + '/' + encodeURIComponent(problem.id) + '">Lihat masalah</a>'
                + '</div>';
        },

        calculateDaysLeft(deadline) {
            const diff = new Date(deadline) - new Date();
            return Math.max(0, Math.ceil(diff / (1000 * 60 * 60 * 24)));
        },

        getProvinceCoordinates(problem) {
            // koordinat setiap provinsi di indonesia (sama seperti sebelumnya)
            const provinceCoords = {
                'Aceh': { lat: 4.695135, lng: 96.749397 },
                'Sumatera Utara': { lat: 2.115320, lng: 99.545700 },
                'Sumatera Barat': { lat: -0.947420, lng: 100.418580 },
                'Riau': { lat: 0.293640, lng: 101.704540 },
                'Kepulauan Riau': { lat: 1.114700, lng: 104.045140 },
                'Jambi': { lat: -1.610640, lng: 103.613030 },
                'Sumatera Selatan': { lat: -3.119490, lng: 104.760580 },
                'Kepulauan Bangka Belitung': { lat: -2.741980, lng: 106.443970 },
                'Bengkulu': { lat: -3.792020, lng: 102.260940 },
                'Lampung': { lat: -4.558570, lng: 105.407540 },
                'DKI Jakarta': { lat: -6.208760, lng: 106.845600 },
                'Jawa Barat': { lat: -6.914740, lng: 107.609480 },
                'Banten': { lat: -6.402480, lng: 106.063300 },
                'Jawa Tengah': { lat: -7.150975, lng: 110.140260 },
                'DI Yogyakarta': { lat: -7.797068, lng: 110.370529 },
                'Jawa Timur': { lat: -7.539890, lng: 112.734320 },
                'Bali': { lat: -8.455840, lng: 115.188920 },
                'Nusa Tenggara Barat': { lat: -8.653150, lng: 117.361700 },
                'Nusa Tenggara Timur': { lat: -8.657240, lng: 121.079440 },
                'Kalimantan Barat': { lat: -0.026820, lng: 109.342500 },
                'Kalimantan Tengah': { lat: -1.681780, lng: 113.382350 },
                'Kalimantan Selatan': { lat: -3.097440, lng: 115.283580 },
                'Kalimantan Timur': { lat: 0.539280, lng: 116.419389 },
                'Kalimantan Utara': { lat: 3.073080, lng: 116.041580 },
                'Sulawesi Utara': { lat: 0.629800, lng: 123.975100 },
                'Gorontalo': { lat: 0.543960, lng: 123.058060 },
                'Sulawesi Tengah': { lat: -1.430380, lng: 121.445620 },
                'Sulawesi Barat': { lat: -2.844480, lng: 119.233170 },
                'Sulawesi Selatan': { lat: -3.664520, lng: 119.974620 },
                'Sulawesi Tenggara': { lat: -4.145040, lng: 122.174610 },
                'Maluku': { lat: -3.238740, lng: 130.145490 },
                'Maluku Utara': { lat: 1.570340, lng: 127.808380 },
                'Papua': { lat: -4.269930, lng: 138.080350 },
                'Papua Barat': { lat: -1.334150, lng: 133.174470 },
                'Papua Tengah': { lat: -3.978350, lng: 136.264950 },
                'Papua Pegunungan': { lat: -3.976870, lng: 138.683830 },
                'Papua Selatan': { lat: -6.086530, lng: 140.512480 },
                'Papua Barat Daya': { lat: -2.529120, lng: 132.291150 },
            };

            const provinceName = problem.province?.name || problem.location_province;
            if (provinceName && provinceCoords[provinceName]) {
                // sedikit pergeseran acak agar penanda di provinsi yang sama tidak bertumpuk
                const offset = 0.1;
                return {
                    lat: provinceCoords[provinceName].lat + (Math.random() - 0.5) * offset,
                    lng: provinceCoords[provinceName].lng + (Math.random() - 0.5) * offset,
                };
            }

            // fallback ke koordinat tengah indonesia
            return { lat: -2.5, lng: 118 };
        },
    };
}
</script>
@endpush
