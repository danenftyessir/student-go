import json, sys, csv, collections, re

S = sys.argv[1]
wide = sys.argv[2] == 'wide'
d = json.load(open(S + '/db_before.json', encoding='utf-8'))
m = json.load(open(S + '/match.json'))

# photo -> lowest-numbered existing bucket object holding it
obj = {}
for r in sorted(m, key=lambda r: (int(re.search(r'-(\d+)\.', r['file']).group(1)), r['file'].endswith('.jpeg'))):
    obj.setdefault(r['match'].split('/')[-1][:-5], 'problems/' + r['file'])

# real location of each photo (credits.json), in Indonesian; None = no finer location recorded
loc = {
    'purwojati-banyumas-rice-fields': 'Purwojati, Banyumas',
    'flooded-rice-paddies-farmers-aerial': None,
    'jatiluwih-bali-rice-planting': 'Jatiluwih, Tabanan, Bali',
    'aceh-rice-field-farmer': 'Aceh',
    'tegalwaru-west-java-aerial-rice-fields': 'Tegalwaru, Jawa Barat',
    'yogyakarta-farmer-planting-rice': 'Yogyakarta',
    'bali-aerial-tractor-plowing-rice-field': 'Bali',
    'west-java-drone-cropland-near-forest': 'Jawa Barat',
    'bali-rice-terraces-lone-farmer': 'Bali',
    'bengkulu-aerial-rice-terraces-sunset': 'Bengkulu',
    'sidemen-bali-rice-terraces-at-dawn': 'Sidemen, Bali',
    'bali-woman-working-in-rice-field': 'Bali',
    'phinisi-boats-in-harbor': None,
    'bangka-belitung-fisherman-casting-net-sunrise': 'Kepulauan Bangka Belitung',
    'north-maluku-boats-on-beach-aerial': 'Maluku Utara',
    'gili-gede-mangroves-on-shore': 'Gili Gede, Nusa Tenggara Barat',
    'batam-beach-litter-in-tree-roots': 'Pantai Viovio, Batam',
    'bontang-wooden-dock-and-mangroves': 'Bontang, Kalimantan Timur',
    'komodo-boats-on-water': 'Pulau Komodo, Manggarai Barat',
    'maluku-islands-aerial': 'Kepulauan Maluku',
    'waigeo-barat-coastal-huts-aerial': 'Waigeo Barat',
    'papua-island-landscape': 'Papua',
    'ntb-fish-market': 'Nusa Tenggara Barat',
    'surakarta-market-shoppers': 'Surakarta',
    'universitas-indonesia-graduation-flags': 'Universitas Indonesia, Depok',
    'umy-graduates-in-red-gowns': 'Universitas Muhammadiyah Yogyakarta, Bantul',
    'southeast-sulawesi-stilt-houses-by-river': 'Sulawesi Tenggara',
    'penglipuran-village-path': 'Desa Penglipuran, Bangli, Bali',
    'rural-road-house-and-mountain': None,
    'kebumen-three-cyclists-across-field': 'Kebumen, Jawa Tengah',
    'cirebon-aerial-village-red-roofs': 'Ciledug, Cirebon',
    'kutai-kartanegara-aerial-town-and-river': 'Loa Kulu, Kutai Kartanegara',
}
P = {
    'pertanian': ['purwojati-banyumas-rice-fields', 'flooded-rice-paddies-farmers-aerial', 'bengkulu-aerial-rice-terraces-sunset',
                  'jatiluwih-bali-rice-planting', 'aceh-rice-field-farmer', 'tegalwaru-west-java-aerial-rice-fields',
                  'yogyakarta-farmer-planting-rice', 'bali-aerial-tractor-plowing-rice-field', 'west-java-drone-cropland-near-forest',
                  'bali-rice-terraces-lone-farmer', 'sidemen-bali-rice-terraces-at-dawn', 'bali-woman-working-in-rice-field'],
    'pesisir': ['phinisi-boats-in-harbor', 'bangka-belitung-fisherman-casting-net-sunrise', 'north-maluku-boats-on-beach-aerial',
                'gili-gede-mangroves-on-shore', 'batam-beach-litter-in-tree-roots', 'bontang-wooden-dock-and-mangroves',
                'komodo-boats-on-water', 'maluku-islands-aerial', 'waigeo-barat-coastal-huts-aerial', 'papua-island-landscape'],
    'pasar': ['ntb-fish-market', 'surakarta-market-shoppers'],
    'pendidikan': ['universitas-indonesia-graduation-flags', 'umy-graduates-in-red-gowns'],
    'desa': ['southeast-sulawesi-stilt-houses-by-river', 'penglipuran-village-path', 'rural-road-house-and-mountain',
             'kebumen-three-cyclists-across-field', 'cirebon-aerial-village-red-roofs', 'kutai-kartanegara-aerial-town-and-river'],
}
P['wilayah'] = P['desa'] + (['maluku-islands-aerial', 'papua-island-landscape', 'waigeo-barat-coastal-huts-aerial'] if wide else [])
withheld = {'central-java-farmer-terraced-fields', 'west-java-elderly-farmers-harvest-rice', 'central-java-farmer-casting-net-in-paddy',
            'west-sumatra-fisherman-net-from-canoe', 'banjarmasin-floating-market'}
allp = set(sum(P.values(), []))
assert not (withheld & allp) and allp <= set(obj) and len(allp) == 32 and set(loc) == allp

pool_of = {}
for s in (2, 15): pool_of[s] = 'pertanian'
for s in (6, 13, 14): pool_of[s] = 'pesisir'
for s in (8, 12): pool_of[s] = 'pasar'
pool_of[4] = 'pendidikan'
for s in (9, 10, 11, 16, 17): pool_of[s] = 'desa'
for s in (1, 3, 5, 7): pool_of[s] = 'wilayah'

probs = {p['id']: p for p in d['problems']}
rows = collections.defaultdict(list)
for r in d['rows']:
    rows[r['problem_id']].append(r)

use = collections.Counter()
cursor = collections.Counter()
out = []
for pid in sorted(rows):
    pr = probs[pid]
    s = json.loads(pr['sdg_categories'])[0]
    pool = pool_of[s]
    chosen = []
    for r in sorted(rows[pid], key=lambda r: r['order']):
        src = pool if len(chosen) < len(P[pool]) else 'wilayah'
        cand = [x for x in P[src] if x not in chosen]
        cand.sort(key=lambda x: (use[x], (P[src].index(x) - cursor[src]) % len(P[src])))
        ph = cand[0]
        chosen.append(ph)
        use[ph] += 1
        cursor[src] = (P[src].index(ph) + 1) % len(P[src])
        neutral = pool == 'wilayah' or src != pool
        caption = 'Gambaran wilayah' if neutral else ('Foto ilustrasi: ' + loc[ph] if loc[ph] else 'Foto ilustrasi')
        out.append({'id': r['id'], 'problem_id': pid, 'title': pr['title'], 'first_sdg': s, 'pool': pool, 'slot_pool': src,
                    'order': r['order'], 'old_path': r['image_path'], 'old_caption': r['caption'], 'new_path': obj[ph],
                    'photo': ph, 'new_caption': caption, 'regency': pr['regency'], 'province': pr['province']})

fn = S + ('/plan-wide.csv' if wide else '/plan.csv')
with open(fn, 'w', newline='', encoding='utf-8') as f:
    w = csv.DictWriter(f, fieldnames=list(out[0]))
    w.writeheader()
    w.writerows(out)

print('variant', 'wide' if wide else 'narrow', '| rows', len(out), '| problems', len(rows), '| path changed', sum(o['old_path'] != o['new_path'] for o in out))
print(' problems per pool', dict(collections.Counter(pool_of[json.loads(probs[p]['sdg_categories'])[0]] for p in rows)))
print(' rows per slot pool', dict(collections.Counter(o['slot_pool'] for o in out)), '| fill slots', sum(o['slot_pool'] != o['pool'] for o in out))
print(' captions', dict(collections.Counter('Gambaran wilayah' if o['new_caption'] == 'Gambaran wilayah' else ('Foto ilustrasi: <lokasi>' if ':' in o['new_caption'] else 'Foto ilustrasi') for o in out)))
print(' problems with a repeated photo', sum(len(set(o['photo'] for o in out if o['problem_id'] == p)) != len(rows[p]) for p in rows))
print(' uses per photo (min,max)', {k: (min(use[x] for x in v), max(use[x] for x in v)) for k, v in P.items() if k != 'wilayah'})
print(' distinct objects referenced', len(set(o['new_path'] for o in out)), 'of 92')
