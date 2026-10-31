// student go phase 6: perilaku halaman autentikasi (masuk, registrasi, lupa/reset password).
// endpoint, method, nama field dan alur respons server tidak berubah; modul ini hanya mengatur
// validasi setelah blur/kirim, status memuat, wizard langkah dan pilihan provinsi -> kabupaten/kota.

const reduceMotion = () => window.matchMedia('(prefers-reduced-motion: reduce)').matches;

// x-show alpine menampilkan elemen pada frame berikutnya; fokus dipindah setelah itu
const afterPaint = (fn) => requestAnimationFrame(() => requestAnimationFrame(fn));

const CONTROLS = 'input:not([type=hidden]), select, textarea';

// aturan password sama dengan Password::min(8)->mixedCase()->numbers()->symbols() di form request
export const passwordRules = {
    length: (v) => v.length >= 8,
    mixed: (v) => /\p{Ll}/u.test(v) && /\p{Lu}/u.test(v),
    number: (v) => /\p{N}/u.test(v),
    symbol: (v) => /[\p{Z}\p{S}\p{P}]/u.test(v),
};
const passwordOk = (v) => Object.values(passwordRules).every((rule) => rule(v));

// nama field untuk pesan: data-sg-label, <label for>, atau aria-labelledby (input file x-ui.upload)
function labelOf(el) {
    if (el.dataset.sgLabel) return el.dataset.sgLabel;
    // aria-labelledby lebih dulu: input file x-ui.upload juga dibungkus <label> dropzone ("Pilih berkas …")
    const source = document.getElementById(el.getAttribute('aria-labelledby') || '') || el.labels?.[0];
    const text = source?.textContent.replace(/\s+/g, ' ').replace(/\s*\(opsional\)\s*$/i, '').trim();
    return text || 'Kolom ini';
}

// pesan bahasa indonesia untuk validitas bawaan peramban (umpan balik awal; server tetap memvalidasi)
function messageFor(el) {
    const v = el.validity;
    if (v.valid) return '';
    const label = labelOf(el);
    if (v.valueMissing) {
        if (el.dataset.sgRequired) return el.dataset.sgRequired;
        if (el.type === 'file') return `${label} wajib diunggah.`;
        if (el.tagName === 'SELECT') return `${label} wajib dipilih.`;
        return `${label} wajib diisi.`;
    }
    if (v.customError) return el.validationMessage;
    if (v.typeMismatch && el.type === 'email') return 'Format email tidak valid.';
    if (v.typeMismatch && el.type === 'url') return 'Format URL tidak valid. Awali dengan https://';
    if (v.tooShort) return `${label} minimal ${el.minLength} karakter.`;
    if (v.tooLong) return `${label} maksimal ${el.maxLength} karakter.`;
    if (v.rangeUnderflow) return `${label} minimal ${el.min}.`;
    if (v.rangeOverflow) return `${label} maksimal ${el.max}.`;
    if (v.patternMismatch && el.dataset.sgPattern) return el.dataset.sgPattern;
    return `${label} tidak valid.`;
}

// aturan tambahan yang tidak dimiliki html: konfirmasi password dan syarat password
function applyCustomRules(form, el) {
    if (!el.setCustomValidity) return;
    let msg = '';
    if (el.dataset.sgPasswordRules !== undefined && el.value && !passwordOk(el.value)) {
        msg = 'Password belum memenuhi semua syarat.';
    }
    if (el.dataset.sgMatch) {
        const other = form.elements[el.dataset.sgMatch];
        if (el.value && other && other.value !== el.value) msg = 'Konfirmasi password tidak cocok.';
    }
    el.setCustomValidity(msg);
}

// elemen galat per field: [data-sg-error-for="nama"] (dibuat oleh view), ditautkan lewat aria-describedby
function errorNode(form, name) {
    return form.querySelector(`[data-sg-error-for="${CSS.escape(name)}"]`);
}

function describe(el, id, on) {
    const ids = (el.getAttribute('aria-describedby') || '').split(/\s+/).filter(Boolean).filter((x) => x !== id);
    if (on) ids.push(id);
    if (ids.length) el.setAttribute('aria-describedby', ids.join(' '));
    else el.removeAttribute('aria-describedby');
}

export function showError(form, el, message) {
    const node = errorNode(form, el.name);
    // galat server yang dirender blade ("{id}-error") disembunyikan begitu field divalidasi ulang
    const serverNode = el.id ? document.getElementById(`${el.id}-error`) : null;
    if (serverNode && serverNode !== node) serverNode.hidden = true;
    el.setAttribute('data-touched', '');
    if (message) el.setAttribute('aria-invalid', 'true');
    else el.removeAttribute('aria-invalid');
    if (!node) return;
    if (!node.id) node.id = `${el.id || el.name}-live-error`;
    const text = node.querySelector('[data-sg-error-text]') || node;
    text.textContent = message || '';
    node.hidden = !message;
    describe(el, node.id, !!message);
}

function check(form, el) {
    applyCustomRules(form, el);
    const message = messageFor(el);
    showError(form, el, message);
    return message;
}

// kontrol yang divalidasi: punya name, tidak disabled, bukan tombol
function controlsIn(root) {
    return [...root.querySelectorAll(CONTROLS)].filter((el) => el.name && !el.disabled && el.type !== 'submit' && el.type !== 'button');
}

// validasi setelah interaksi: blur pada field yang sudah diubah, lalu ulang saat mengetik setelah itu
export function bindValidation(form) {
    form.addEventListener('input', (e) => {
        const el = e.target;
        if (!el.name) return;
        el.dataset.sgDirty = '1';
        if (el.hasAttribute('data-touched')) check(form, el);
        // konfirmasi ikut dicek ulang bila password berubah
        form.querySelectorAll(`[data-sg-match="${CSS.escape(el.name)}"][data-touched]`).forEach((c) => check(form, c));
    });
    form.addEventListener('change', (e) => {
        const el = e.target;
        if (!el.name) return;
        el.dataset.sgDirty = '1';
        if (el.type === 'file' || el.type === 'checkbox' || el.tagName === 'SELECT' || el.hasAttribute('data-touched')) check(form, el);
    });
    form.addEventListener('focusout', (e) => {
        const el = e.target;
        if (!el.name || !el.matches?.(CONTROLS)) return;
        if (el.dataset.sgDirty || el.hasAttribute('data-touched')) check(form, el);
    });
}

// periksa semua kontrol dalam root; kembalikan daftar galat [{id, label, message}]
export function validateWithin(form, root) {
    return controlsIn(root)
        .map((el) => ({ el, message: check(form, el) }))
        .filter((x) => x.message)
        .map(({ el, message }) => ({ id: el.id, name: el.name, label: labelOf(el), message }));
}

function focusField(form, name) {
    const el = form.elements[name];
    const target = el && el.length && !el.tagName ? el[0] : el;
    if (!target) return;
    target.focus({ preventScroll: true });
    target.scrollIntoView({ block: 'center', behavior: reduceMotion() ? 'auto' : 'smooth' });
}

export function registerAuth(Alpine) {
    // form biasa (masuk, lupa/reset password): validasi setelah blur, status memuat saat dikirim.
    // pengiriman tetap native (action/method sama), sehingga redirect dan flash server tidak berubah.
    Alpine.data('sgAuthForm', () => ({
        submitting: false,
        init() {
            bindValidation(this.$root);
            // halaman dimuat ulang dari bfcache setelah tombol kembali: tombol aktif lagi
            window.addEventListener('pageshow', () => { this.submitting = false; });
        },
        submit(e) {
            const errors = validateWithin(this.$root, this.$root);
            if (errors.length) {
                e.preventDefault();
                focusField(this.$root, errors[0].name);
                return;
            }
            this.submitting = true;
        },
    }));

    // wizard registrasi: satu stepper untuk mahasiswa (3), instansi (4) dan perusahaan (4).
    // cfg.steps = [[field langkah 1], ...]; cfg.validateUrl = validasi server per langkah (mahasiswa);
    // cfg.submitUrl = route submit yang sama; cfg.mode = 'redirect' (ke redirect_url) | 'panel' (pesan sukses di halaman).
    Alpine.data('sgRegister', (cfg = {}) => ({
        step: cfg.initialStep || 1,
        total: (cfg.steps || []).length,
        busy: false,
        errors: [],
        formError: '',
        done: null,
        init() {
            bindValidation(this.$root);
        },
        stepOf(name) {
            const i = (cfg.steps || []).findIndex((fields) => fields.includes(name));
            return i === -1 ? this.total : i + 1;
        },
        section(n) {
            return this.$root.querySelector(`[data-step="${n}"]`);
        },
        go(n, focusHeading = true) {
            this.step = Math.min(Math.max(1, n), this.total);
            this.$nextTick(() => afterPaint(() => {
                const heading = this.section(this.step)?.querySelector('[data-step-heading]');
                if (!focusHeading || !heading) return;
                heading.focus({ preventScroll: true });
                this.$root.scrollIntoView({ block: 'start', behavior: reduceMotion() ? 'auto' : 'smooth' });
            }));
        },
        back() {
            this.errors = [];
            this.formError = '';
            this.go(this.step - 1);
        },
        // tampilkan galat dari server (422) di field masing-masing dan di ringkasan
        applyServerErrors(errs) {
            const list = [];
            Object.entries(errs || {}).forEach(([name, messages]) => {
                const el = this.$root.elements[name];
                const message = Array.isArray(messages) ? messages[0] : String(messages);
                if (el && el.tagName) showError(this.$root, el, message);
                list.push({ id: el?.id || name, name, label: el?.tagName ? labelOf(el) : name, message });
            });
            return list;
        },
        // ringkasan (role=alert, dibacakan) + fokus ke field pertama yang perlu diperbaiki
        showSummary(list) {
            this.errors = list;
            if (list.length) this.$nextTick(() => afterPaint(() => focusField(this.$root, list[0].name)));
        },
        async next() {
            if (this.busy) return;
            this.formError = '';
            const local = validateWithin(this.$root, this.section(this.step));
            if (local.length) {
                this.showSummary(local);
                return;
            }
            if (cfg.validateUrl) {
                this.busy = true;
                try {
                    const body = new FormData(this.$root);
                    body.append('step', this.step);
                    const res = await fetch(cfg.validateUrl, { method: 'POST', headers: { Accept: 'application/json' }, body });
                    if (res.status === 422) {
                        const data = await res.json();
                        this.showSummary(this.applyServerErrors(data.errors));
                        return;
                    }
                    if (!res.ok) throw new Error(String(res.status));
                } catch (e) {
                    this.formError = 'Tidak dapat memeriksa data ke server. Periksa koneksi lalu coba lagi.';
                    return;
                } finally {
                    this.busy = false;
                }
            }
            this.errors = [];
            this.go(this.step + 1);
        },
        async submit() {
            if (this.busy) return;
            this.formError = '';
            const local = validateWithin(this.$root, this.section(this.step));
            if (local.length) {
                this.showSummary(local);
                return;
            }
            this.busy = true;
            const controller = new AbortController();
            const timer = setTimeout(() => controller.abort(), 60000);
            try {
                const res = await fetch(cfg.submitUrl, {
                    method: 'POST',
                    headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    body: new FormData(this.$root),
                    signal: controller.signal,
                });
                const data = await res.json().catch(() => ({}));
                if (res.status === 422) {
                    const list = this.applyServerErrors(data.errors);
                    const first = Math.min(...list.map((x) => this.stepOf(x.name)));
                    if (Number.isFinite(first) && first !== this.step) this.go(first, false);
                    this.$nextTick(() => this.showSummary(list));
                    return;
                }
                const redirect = data.redirect_url || data.data?.redirect_url || null;
                if (res.ok && data.success) {
                    if (cfg.mode === 'redirect' && redirect) {
                        window.location.assign(redirect);
                        return;
                    }
                    this.done = { message: data.message || 'Pendaftaran berhasil.', redirect };
                    this.$nextTick(() => afterPaint(() => this.$refs.done?.focus()));
                    return;
                }
                this.formError = data.message || 'Terjadi kesalahan saat pendaftaran. Silakan coba lagi.';
            } catch (e) {
                this.formError = e.name === 'AbortError'
                    ? 'Server terlalu lama merespons. Data mungkin sudah tersimpan; coba masuk sebelum mendaftar ulang.'
                    : 'Tidak dapat terhubung ke server. Periksa koneksi lalu coba lagi.';
            } finally {
                clearTimeout(timer);
                if (!this.done) this.busy = false;
            }
            this.$nextTick(() => afterPaint(() => this.$refs.formError?.focus()));
        },
    }));

    // provinsi -> kabupaten/kota: request sebelumnya dibatalkan, status memuat, perubahan diumumkan (J30).
    // cfg.url berisi "__ID__" (mis. /api/public/regencies/__ID__); bentuk respons api tidak berubah.
    Alpine.data('sgRegions', (cfg = {}) => ({
        province: String(cfg.province ?? ''),
        regency: String(cfg.regency ?? ''),
        regencies: Array.isArray(cfg.regencies) ? cfg.regencies : [],
        loading: false,
        failed: false,
        status: '',
        seq: 0,
        controller: null,
        init() {
            // pilihan lama (old input) dipasang setelah opsi x-for dirender
            this.$nextTick(() => {
                if (this.$refs.regency && this.regency) this.$refs.regency.value = this.regency;
            });
        },
        provinceName() {
            const opt = this.$refs.province?.selectedOptions?.[0];
            return opt && opt.value ? opt.textContent.trim() : '';
        },
        async load() {
            this.controller?.abort();
            this.regency = '';
            this.failed = false;
            if (!this.province) {
                this.regencies = [];
                this.loading = false;
                this.status = '';
                return;
            }
            const seq = ++this.seq;
            const controller = new AbortController();
            this.controller = controller;
            this.loading = true;
            this.regencies = [];
            this.status = 'Memuat daftar kabupaten/kota…';
            try {
                const res = await fetch(cfg.url.replace('__ID__', encodeURIComponent(this.province)), {
                    headers: { Accept: 'application/json' },
                    signal: controller.signal,
                });
                if (!res.ok) throw new Error(String(res.status));
                const data = await res.json();
                if (seq !== this.seq) return;
                this.regencies = Array.isArray(data) ? data : [];
                this.status = this.regencies.length
                    ? `${this.regencies.length} kabupaten/kota dimuat untuk ${this.provinceName()}.`
                    : `Belum ada data kabupaten/kota untuk ${this.provinceName()}.`;
            } catch (e) {
                if (e.name === 'AbortError' || seq !== this.seq) return;
                this.failed = true;
                this.status = 'Daftar kabupaten/kota gagal dimuat.';
            } finally {
                if (seq === this.seq) this.loading = false;
            }
        },
    }));

    // password: tampil/sembunyi + daftar syarat yang dicek saat mengetik
    Alpine.data('sgPassword', () => ({
        show: false,
        value: '',
        met(rule) {
            return passwordRules[rule](this.value);
        },
    }));
}
