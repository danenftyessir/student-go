# paket F: html dari data uji -> sebelum/sesudah. css/aset dari public/ lewat server statis lokal; permintaan eksternal diblokir.
import re, sys, os, json, subprocess, time
from playwright.sync_api import sync_playwright
PUBLIC, DUMP, OUT = sys.argv[1], sys.argv[2], sys.argv[3]
BEFORE_CSS = sys.argv[4] if len(sys.argv) > 4 else ""  # aturan css lama yang dikembalikan untuk varian sebelum
PORT = 8765; ORIGIN = f"http://127.0.0.1:{PORT}"
U6 = {"institution-problems", "institution-projects", "institution-reviews", "student-jobs-saved-folder", "student-jobs-page99"}
work = os.path.join(PUBLIC, "_sg_f_tmp")  # sementara di public/, dihapus di akhir
os.makedirs(work, exist_ok=True); os.makedirs(OUT, exist_ok=True)
import json as _j
_m=_j.load(open(os.path.join(PUBLIC,"build","manifest.json")))
def _cur(html):
    html=re.sub(r"/build/assets/app-[A-Za-z0-9_-]+\.css", "/build/"+_m["resources/css/app.css"]["file"], html)
    return re.sub(r"/build/assets/app-[A-Za-z0-9_-]+\.js", "/build/"+_m["resources/js/app.js"]["file"], html)

srv = subprocess.Popen([sys.executable, "-m", "http.server", str(PORT), "--bind", "127.0.0.1"], cwd=PUBLIC, stdout=subprocess.DEVNULL, stderr=subprocess.DEVNULL)
time.sleep(1.5)
METRIC = """() => { const f = document.querySelector('footer'); const r = f.getBoundingClientRect();
 return { overflow: document.documentElement.scrollWidth - window.innerWidth,
  gap: Math.max(0, Math.round(window.innerHeight - (r.bottom + window.scrollY))),
  emptyImgs: [...document.querySelectorAll('main .sg-empty img')].map(i => i.src.split('/').pop()) } }"""
res = {}
try:
    names = sorted(f[:-5] for f in os.listdir(DUMP) if f.endswith(".html"))
    for n in names:
        html = open(os.path.join(DUMP, n + ".html"), encoding="utf-8").read()
        html = _cur(re.sub(r"https?://(localhost|student-go\.railway\.app)(:\d+)?", ORIGIN, html))
        after = html
        assert after.count('<div class="sg-shell bg-gray-50">') == 1, n
        before = after.replace('<div class="sg-shell bg-gray-50">', '<div class="min-h-screen bg-gray-50" style="min-height:100vh">')
        if BEFORE_CSS:
            before = before.replace('</head>', '<style>' + BEFORE_CSS + '</style></head>', 1)
        if n in U6:
            before, k = re.subn(r'(<div class="sg-empty[^"]*">\s*)<img src="[^"]*/empty-states/[^"]+\.svg"[^>]*>', r'\1', before)
            assert k == 1, (n, k)
        for state, doc in (("before", before), ("after", after)):
            open(os.path.join(work, f"{n}-{state}.html"), "w", encoding="utf-8").write(doc)
    with sync_playwright() as p:
        b = p.chromium.launch(); pg = b.new_page()
        pg.route("**/*", lambda r: r.continue_() if (r.request.url.startswith(ORIGIN) or (os.environ.get("SG_NO_ALPINE") is None and r.request.url.startswith("https://cdn.jsdelivr.net/npm/alpinejs"))) else r.abort())
        for n in names:
            for state in ("before", "after"):
                pg.set_viewport_size({"width": 1440, "height": 900})
                pg.goto(f"{ORIGIN}/_sg_f_tmp/{n}-{state}.html", wait_until="load"); pg.wait_for_timeout(300)
                e = {}
                for w in (360, 390, 768, 1440):
                    pg.set_viewport_size({"width": w, "height": 900 if w == 1440 else 844}); pg.wait_for_timeout(200)
                    e[w] = pg.evaluate(METRIC)
                    if w in (390, 1440): pg.screenshot(path=f"{OUT}/{state}-{n}-{w}.png", full_page=True)
                res[f"{state}:{n}"] = e
                print(state, n, "overflow", {w: v["overflow"] for w, v in e.items()}, "gap1440", e[1440]["gap"], "img", e[1440]["emptyImgs"])
        b.close()
finally:
    srv.terminate()
    for f in os.listdir(work): os.remove(os.path.join(work, f))
    os.rmdir(work)
json.dump(res, open(f"{OUT}/metrics.json", "w"), indent=1)
