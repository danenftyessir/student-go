# paket F capture: overflow per lebar, celah footer @1440x900, screenshot 390 + 1440. tidak pernah screenshot 5xx.
import re, sys, json, os
from playwright.sync_api import sync_playwright
ROOT, OUT = sys.argv[1], sys.argv[2]
ONLY = sys.argv[3].split(",") if len(sys.argv) > 3 else None
BASE = "http://127.0.0.1:8000"
os.makedirs(OUT, exist_ok=True)
def pw(path, line):
    src = open(ROOT + "/" + path, encoding="utf-8").read().splitlines()[line-1]
    return re.search(r"Hash::make\('([^']+)'\)", src).group(1)
D = ("database/seeders/DummyDataSeeder.php", 359)
ACCTS = {
  "student": ("ulfamaulana1066@untag-smd.ac.id", D),
  "institution": ("pemerintahdesacangkringan28@pemerintahdesa.go.id", D),
  "company": ("hr@koperasitanimakmurjaya.co.id", ("database/seeders/UserSeeder.php", 135)),
}
PAGES = {
  "guest": ["/contact", "/halaman-tidak-ada-29f", "/about", "LOGIN_ERROR"],
  "student": ["/student/wishlist", "/student/projects", "/student/friends", "/student/jobs", "/student/jobs/saved", "/student/browse-problems"],
  "institution": ["/institution/problems", "/institution/projects", "/institution/reviews", "/institution/dashboard"],
  "company": ["/company/dashboard", "/company/jobs"],
}
METRIC = """() => { const f = document.querySelector('footer'); const r = f ? f.getBoundingClientRect() : null;
  return { sw: document.documentElement.scrollWidth, iw: window.innerWidth,
    footerBottom: r ? Math.round(r.bottom + window.scrollY) : null, docH: document.documentElement.scrollHeight,
    gapBelowFooter: r ? Math.max(0, Math.round(window.innerHeight - (r.bottom + window.scrollY))) : null,
    emptyImgs: [...document.querySelectorAll('main img')].filter(i => /empty-states/.test(i.src)).map(i => i.src.split('/').pop()),
    emptyTitles: [...document.querySelectorAll('main .sg-empty__title, main [class*=sg-empty] h2, main [class*=sg-empty] h3')].map(e => e.textContent.trim()).slice(0,4) } }"""
res = {}
def slug(u): return re.sub(r"[^a-z0-9]+", "-", u.lower()).strip("-") or "home"
with sync_playwright() as p:
    b = p.chromium.launch()
    for role, urls in PAGES.items():
        if ONLY and role not in ONLY: continue
        ctx = b.new_context(viewport={"width": 1440, "height": 900}); pg = ctx.new_page(); pg.set_default_timeout(540000)
        if role != "guest":
            em, src = ACCTS[role]
            pg.goto(BASE + "/login"); pg.fill('input[name="email"]', em); pg.fill('input[name="password"]', pw(*src))
            pg.click('form[action$="/login"] button[type="submit"]'); pg.wait_for_url(lambda u: "/login" not in u, wait_until="commit")
        for u in urls:
            key = role + ":" + u
            if u == "LOGIN_ERROR":
                pg.goto(BASE + "/login"); pg.fill('input[name="email"]', "tidak-terdaftar-29f@contoh.test"); pg.fill('input[name="password"]', "salah-sandi-29f")
                pg.click('form[action$="/login"] button[type="submit"]'); pg.wait_for_url(lambda x: "/login" in x, wait_until="load"); pg.wait_for_load_state("load")
                status = 200
            else:
                r = pg.goto(BASE + u, wait_until="load"); status = r.status if r else None
            entry = {"status": status, "widths": {}}
            if status and status >= 500:
                res[key] = entry; print(key, status, "(no screenshot)"); continue
            for w in (360, 390, 768, 1440):
                pg.set_viewport_size({"width": w, "height": 900 if w == 1440 else 844}); pg.wait_for_timeout(300)
                m = pg.evaluate(METRIC); entry["widths"][w] = m
                if w in (390, 1440):
                    pg.screenshot(path=f"{OUT}/{role}-{slug(u)}-{w}.png", full_page=True)
            pg.set_viewport_size({"width": 1440, "height": 900})
            res[key] = entry
            m = entry["widths"][1440]
            print(key, status, "overflow", {w: v["sw"] - v["iw"] for w, v in entry["widths"].items()}, "gap1440", m["gapBelowFooter"], "empty", m["emptyImgs"], m["emptyTitles"])
        ctx.close()
    b.close()
json.dump(res, open(f"{OUT}/metrics.json", "w"), indent=1)
