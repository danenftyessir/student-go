# status-only probe: prints HTTP codes, never page bodies
import re, sys, json
from playwright.sync_api import sync_playwright
ROOT = sys.argv[1]
BASE = "http://127.0.0.1:8000"
def pw(path, line):
    src = open(ROOT + "/" + path, encoding="utf-8").read().splitlines()[line-1]
    return re.search(r"Hash::make\('([^']+)'\)", src).group(1)
acct = {"student": ("ulfamaulana1066@untag-smd.ac.id", pw("database/seeders/DummyDataSeeder.php", 359))}
urls = json.loads(sys.argv[2])
with sync_playwright() as p:
    b = p.chromium.launch(); pg = b.new_page(); pg.set_default_timeout(540000)
    pg.goto(BASE + "/login"); pg.fill('input[name="email"]', acct["student"][0]); pg.fill('input[name="password"]', acct["student"][1])
    pg.click('form[action$="/login"] button[type="submit"]'); pg.wait_for_url(lambda u: "/login" not in u, wait_until="commit")
    print("logged in ->", pg.url.replace(BASE, ""))
    for u in urls:
        r = pg.request.get(BASE + u, timeout=180000, max_redirects=0)
        ct = r.headers.get("content-type", "")
        print(u, r.status, ct.split(";")[0])
    b.close()
