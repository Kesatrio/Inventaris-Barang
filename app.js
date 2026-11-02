const API = 'api.php', KEY = 'inventaris_demo_v1', STOK_MIN = 10, $ = s => document.querySelector(s);
const esc = s => String(s ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
const rp = n => 'Rp ' + Number(n || 0).toLocaleString('id-ID');
const tgl = s => String(s).split('-').reverse().join('-');
const sum = (a, f) => a.reduce((s, x) => s + Number(f(x)), 0);
const hariIni = () => { const d = new Date(), p = n => String(n).padStart(2, '0'); return `${d.getFullYear()}-${p(d.getMonth() + 1)}-${p(d.getDate())}`; };
let DEMO = false, D = {}, R = [];

const byId = (t, id) => (D[t] || []).find(r => r[TBL[t].pk] == id) || {};
const stokTxt = r => r.id_barang ? ' (stok: ' + byId('barang', r.id_barang).stok + ')' : '';
const TBL = {
  barang: { label:'Barang', add:'Tambah Barang', pk:'id_barang', edit:true, lab:r=>r.kode_barang+' - '+r.nama_barang, labf:r=>r.kode_barang+' - '+r.nama_barang+' (stok: '+r.stok+')',
    cols:[['kode_barang','Kode'],['nama_barang','Nama Barang'],['id_kategori','Kategori'],['id_lokasi','Lokasi'],['stok','Stok','stok'],['harga','Harga','rp']],
    form:[['kode_barang','Kode Barang','text'],['nama_barang','Nama Barang','text'],['id_kategori','Kategori',{fk:'kategori'}],['id_lokasi','Lokasi',{fk:'lokasi'}],
          ['satuan','Satuan','text',{def:'pcs'}],['stok','Stok','number',{def:0,lock:'Stok diubah lewat menu Barang Masuk / Keluar.'}],['harga','Harga (Rp)','number',{def:0,step:'any'}]] },
  barang_masuk: { label:'Barang Masuk', add:'Catat Barang Masuk', pk:'id_masuk',
    cols:[['tanggal','Tanggal','tgl'],['id_barang','Barang'],['id_supplier','Supplier'],['jumlah','Jumlah','+']],
    form:[['tanggal','Tanggal','date',{def:'today'}],['id_barang','Barang',{fk:'barang'}],['id_supplier','Supplier',{fk:'supplier'}],['jumlah','Jumlah','number',{min:1}]] },
  barang_keluar: { label:'Barang Keluar', add:'Catat Barang Keluar', pk:'id_keluar',
    cols:[['tanggal','Tanggal','tgl'],['id_barang','Barang'],['jumlah','Jumlah','-'],['keterangan','Keterangan']],
    form:[['tanggal','Tanggal','date',{def:'today'}],['id_barang','Barang',{fk:'barang'}],['jumlah','Jumlah','number',{min:1}],['keterangan','Keterangan','text',{opt:1}]] },
  kategori: { label:'Kategori', add:'Tambah Kategori', pk:'id_kategori', edit:true, lab:r=>r.nama_kategori,
    cols:[['nama_kategori','Nama Kategori']], form:[['nama_kategori','Nama Kategori','text']] },
  lokasi: { label:'Lokasi', add:'Tambah Lokasi', pk:'id_lokasi', edit:true, lab:r=>r.nama_lokasi,
    cols:[['nama_lokasi','Nama Lokasi'],['keterangan','Keterangan']], form:[['nama_lokasi','Nama Lokasi','text'],['keterangan','Keterangan','text',{opt:1}]] },
  supplier: { label:'Supplier', add:'Tambah Supplier', pk:'id_supplier', edit:true, lab:r=>r.nama_supplier,
    cols:[['nama_supplier','Nama Supplier'],['telepon','Telepon'],['alamat','Alamat']],
    form:[['nama_supplier','Nama Supplier','text'],['telepon','Telepon','text',{opt:1}],['alamat','Alamat','text',{opt:1}]] },
};

const SEED = {"kategori": [{"id_kategori": 1, "nama_kategori": "Elektronik"}, {"id_kategori": 2, "nama_kategori": "Alat Tulis"}, {"id_kategori": 3, "nama_kategori": "Perabot"}], "lokasi": [{"id_lokasi": 1, "nama_lokasi": "Gudang A", "keterangan": "Lantai 1"}, {"id_lokasi": 2, "nama_lokasi": "Gudang B", "keterangan": "Lantai 2"}, {"id_lokasi": 3, "nama_lokasi": "Rak Kantor", "keterangan": "Ruang administrasi"}], "supplier": [{"id_supplier": 1, "nama_supplier": "PT Maju Jaya", "telepon": "081234567890", "alamat": "Bengkulu"}, {"id_supplier": 2, "nama_supplier": "CV Sumber Rejeki", "telepon": "082345678901", "alamat": "Padang"}], "barang": [{"id_barang": 1, "kode_barang": "BRG-001", "nama_barang": "Laptop 14 inci", "id_kategori": 1, "id_lokasi": 1, "satuan": "unit", "stok": 10, "harga": 7500000}, {"id_barang": 2, "kode_barang": "BRG-002", "nama_barang": "Pulpen Hitam", "id_kategori": 2, "id_lokasi": 3, "satuan": "pcs", "stok": 200, "harga": 3000}, {"id_barang": 3, "kode_barang": "BRG-003", "nama_barang": "Kursi Kantor", "id_kategori": 3, "id_lokasi": 2, "satuan": "unit", "stok": 15, "harga": 450000}, {"id_barang": 4, "kode_barang": "BRG-004", "nama_barang": "Mouse Wireless", "id_kategori": 1, "id_lokasi": 1, "satuan": "unit", "stok": 8, "harga": 150000}, {"id_barang": 5, "kode_barang": "BRG-005", "nama_barang": "Kertas A4", "id_kategori": 2, "id_lokasi": 3, "satuan": "rim", "stok": 50, "harga": 55000}, {"id_barang": 6, "kode_barang": "BRG-006", "nama_barang": "Meja Kerja", "id_kategori": 3, "id_lokasi": 2, "satuan": "unit", "stok": 5, "harga": 1200000}], "barang_masuk": [{"id_masuk": 1, "tanggal": "2026-10-01", "id_barang": 1, "id_supplier": 1, "jumlah": 5}, {"id_masuk": 2, "tanggal": "2026-10-05", "id_barang": 2, "id_supplier": 2, "jumlah": 100}, {"id_masuk": 3, "tanggal": "2026-10-08", "id_barang": 4, "id_supplier": 1, "jumlah": 10}, {"id_masuk": 4, "tanggal": "2026-10-15", "id_barang": 5, "id_supplier": 2, "jumlah": 60}], "barang_keluar": [{"id_keluar": 1, "tanggal": "2026-10-10", "id_barang": 1, "jumlah": 2, "keterangan": "Dipakai bagian IT"}, {"id_keluar": 2, "tanggal": "2026-10-12", "id_barang": 2, "jumlah": 20, "keterangan": "Kebutuhan kantor"}, {"id_keluar": 3, "tanggal": "2026-10-18", "id_barang": 3, "jumlah": 3, "keterangan": "Ruang rapat"}, {"id_keluar": 4, "tanggal": "2026-10-20", "id_barang": 4, "jumlah": 2, "keterangan": "Dipakai bagian IT"}]};
const DEP = { kategori:[['barang','id_kategori']], lokasi:[['barang','id_lokasi']], supplier:[['barang_masuk','id_supplier']],
              barang:[['barang_masuk','id_barang'],['barang_keluar','id_barang']] };
const UNI = { kategori:'nama_kategori', barang:'kode_barang' };
let M = JSON.parse(JSON.stringify(SEED));
try { const s = localStorage.getItem(KEY); if (s) M = JSON.parse(s); } catch (e) {}
const simpan = () => { try { localStorage.setItem(KEY, JSON.stringify(M)); } catch (e) {} };

function reports() {
  const f = (t, id) => M[t].find(r => r[TBL[t].pk] == id) || {}, B = M.barang, kat = M.kategori;
  const grp = (rows, keyf) => { const o = {}; rows.forEach(r => { const k = keyf(r); (o[k] = o[k] || []).push(r); }); return o; };
  const perBarang = (T) => { const g = grp(M[T], r => r.id_barang);
    return Object.entries(g).map(([id, rs]) => ({ Barang: f('barang', id).nama_barang, 'Jumlah Transaksi': rs.length, ['Total ' + (T == 'barang_masuk' ? 'Masuk' : 'Keluar')]: sum(rs, r => r.jumlah) }))
      .sort((a, b) => Object.values(b)[2] - Object.values(a)[2]); };
  return [
    { title:'Daftar Barang Lengkap (JOIN 3 tabel)', rows: [...B].sort((a, b) => f('kategori', a.id_kategori).nama_kategori.localeCompare(f('kategori', b.id_kategori).nama_kategori) || a.nama_barang.localeCompare(b.nama_barang))
        .map(b => ({ Kode:b.kode_barang, Barang:b.nama_barang, Kategori:f('kategori', b.id_kategori).nama_kategori, Lokasi:f('lokasi', b.id_lokasi).nama_lokasi, Stok:b.stok + ' ' + b.satuan, 'Harga (Rp)':b.harga })) },
    { title:'Jumlah Barang dan Total Stok per Kategori (COUNT + SUM + GROUP BY)', rows: [...kat].sort((a, b) => a.nama_kategori.localeCompare(b.nama_kategori))
        .map(k => { const bs = B.filter(b => b.id_kategori == k.id_kategori); return { Kategori:k.nama_kategori, 'Jumlah Barang':bs.length, 'Total Stok':sum(bs, b => b.stok) }; }) },
    { title:'Nilai Persediaan per Kategori (SUM)', rows: kat.map(k => ({ Kategori:k.nama_kategori, 'Nilai Persediaan (Rp)': sum(B.filter(b => b.id_kategori == k.id_kategori), b => b.stok * b.harga) }))
        .sort((a, b) => b['Nilai Persediaan (Rp)'] - a['Nilai Persediaan (Rp)']) },
    { title:'Total Barang Masuk per Barang (JOIN + SUM)', rows: perBarang('barang_masuk') },
    { title:'Total Barang Keluar per Barang (JOIN + SUM)', rows: perBarang('barang_keluar') },
    { title:'Total Pasokan per Supplier (JOIN + SUM)', rows: Object.entries(grp(M.barang_masuk, r => r.id_supplier))
        .map(([id, rs]) => ({ Supplier:f('supplier', id).nama_supplier, 'Jumlah Transaksi':rs.length, 'Total Dipasok':sum(rs, r => r.jumlah) })).sort((a, b) => b['Total Dipasok'] - a['Total Dipasok']) },
    { title:'Barang Stok Menipis (stok 10 atau kurang)', rows: B.filter(b => b.stok <= STOK_MIN).sort((a, b) => a.stok - b.stok).map(b => ({ Kode:b.kode_barang, Barang:b.nama_barang, Stok:b.stok + ' ' + b.satuan })) },
  ];
}

function mock(m, t, id, b) {
  if (t === 'laporan') return reports();
  const a = M[t], c = TBL[t], pk = c.pk, brg = i => M.barang.find(x => x.id_barang == i);
  if (m === 'GET') return a.map(r => ({ ...r }));

  if (m === 'DELETE') {
    const r = a.find(x => x[pk] == id); if (!r) throw new Error('Data tidak ditemukan.');
    if (t == 'barang_masuk') {
      const s = brg(r.id_barang);
      if (s.stok < r.jumlah) throw new Error(`Tidak bisa dihapus: stok saat ini (${s.stok}) lebih kecil dari jumlah masuk (${r.jumlah}), artinya sebagian barang sudah keluar.`);
      s.stok -= r.jumlah;
    } else if (t == 'barang_keluar') brg(r.id_barang).stok += r.jumlah;
    else if ((DEP[t] || []).some(([tt, k]) => M[tt].some(x => x[k] == id))) throw new Error('Gagal: data masih dipakai tabel lain.');
    M[t] = a.filter(x => x[pk] != id); simpan(); return { ok: 1 };
  }

  if (m === 'PUT' && (t == 'barang_masuk' || t == 'barang_keluar')) throw new Error('Catatan transaksi tidak bisa diubah. Hapus lalu catat ulang.');
  b = { ...b };
  c.form.forEach(([k, l, ty, o = {}]) => { if (!o.opt && (b[k] === '' || b[k] == null)) throw new Error(l + ' wajib diisi.'); if (b[k] === '') b[k] = null; });
  if (UNI[t] && a.some(x => x[pk] != id && String(x[UNI[t]]).toLowerCase() == String(b[UNI[t]]).toLowerCase())) throw new Error('Gagal: data kembar, nilai tersebut sudah ada.');
  if (t == 'barang' && (b.stok < 0 || b.harga < 0)) throw new Error('Gagal: nilai tidak valid (stok/harga tidak boleh negatif).');
  const next = Math.max(0, ...a.map(r => r[pk])) + 1;

  if (t == 'barang_masuk' || t == 'barang_keluar') {
    const s = brg(b.id_barang); if (!s) throw new Error('Barang tidak ditemukan.');
    if (!(b.jumlah > 0) || !Number.isInteger(b.jumlah)) throw new Error('Jumlah harus berupa angka bulat lebih dari 0.');
    if (t == 'barang_masuk') s.stok += b.jumlah;
    else { if (s.stok < b.jumlah) throw new Error('Stok tidak cukup. Stok tersedia: ' + s.stok + '.'); s.stok -= b.jumlah; }
  }
  if (m === 'POST') { b[pk] = next; a.push(b); }
  else { const r = a.find(x => x[pk] == id); if (t == 'barang') delete b.stok; Object.assign(r, b); }
  simpan(); return { ok: 1 };
}

async function api(m, t, id, body) {
  if (!DEMO) {
    let r, j;
    try {
      const u = `${API}?${t === 'laporan' ? 'laporan=1' : 't=' + t}${id ? '&id=' + id : ''}`;
      r = await fetch(u, { method: m, headers: { 'Content-Type': 'application/json' }, body: body ? JSON.stringify(body) : undefined });
      if ((r.headers.get('content-type') || '').includes('json')) j = await r.json();
      else if (r.status >= 500) throw new Error('Server error. Cek Apache/PHP dan MySQL di Laragon.');
      else DEMO = true;                       // tidak ada PHP (mis. GitHub Pages) -> Mode Demo
    } catch (e) { if (e instanceof TypeError) DEMO = true; else throw e; }
    if (!DEMO) { if (!r.ok) throw new Error(j.error || 'Terjadi kesalahan.'); return j; }
  }
  return mock(m, t, id, body);
}

async function load() {
  D.kategori = await api('GET', 'kategori');
  await Promise.all(Object.keys(TBL).filter(t => t != 'kategori').map(async t => D[t] = await api('GET', t)));
  R = await api('GET', 'laporan');
  $('#mode').textContent = DEMO ? 'Mode Demo (data contoh di browser)' : 'Terhubung ke MySQL';
  $('#mode').className = 'badge ' + (DEMO ? 'demo' : 'live');
  $('#reset').hidden = !DEMO;
}

const tbl = (h, rows) => `<div class="scroll"><table><thead><tr>${h.map(x => `<th>${x}</th>`).join('')}</tr></thead><tbody>${
  rows.map(r => `<tr>${r.map(x => `<td>${x}</td>`).join('')}</tr>`).join('') || '<tr><td>Belum ada data</td></tr>'}</tbody></table></div>`;

const cell = (t, r, [k, , fmt]) => {
  const f = TBL[t].form.find(x => x[0] == k), v = r[k];
  if (f && f[2].fk) { const T = TBL[f[2].fk], o = byId(f[2].fk, v); return o[T.pk] == null ? '-' : T.lab(o); }
  if (fmt == 'rp') return rp(v);
  if (fmt == 'tgl') return tgl(v);
  if (fmt == 'stok') return v + ' ' + r.satuan;
  if (fmt == '+' || fmt == '-') return fmt + v + ' ' + (byId('barang', r.id_barang).satuan || '');
  return v ?? '-';
};

const dash = () => {
  const B = D.barang, low = B.filter(b => b.stok <= STOK_MIN).sort((a, b) => a.stok - b.stok);
  const S = [['Jenis Barang', B.length], ['Total Stok', sum(B, b => b.stok)], ['Nilai Persediaan', rp(sum(B, b => b.stok * b.harga))],
             ['Stok Menipis', low.length], ['Transaksi Masuk', D.barang_masuk.length], ['Transaksi Keluar', D.barang_keluar.length]];
  const per = D.kategori.map(k => [k.nama_kategori, sum(B.filter(b => b.id_kategori == k.id_kategori), b => b.stok)]), mx = Math.max(1, ...per.map(x => x[1]));
  return `<div class="stats">${S.map(([l, v]) => `<div class="stat"><b>${v}</b><span>${l}</span></div>`).join('')}</div>
    <div class="card"><h3>Barang Stok Menipis (stok ${STOK_MIN} atau kurang)</h3>${tbl(['Kode', 'Barang', 'Kategori', 'Lokasi', 'Stok'],
      low.map(b => [esc(b.kode_barang), esc(b.nama_barang), esc(byId('kategori', b.id_kategori).nama_kategori), esc(byId('lokasi', b.id_lokasi).nama_lokasi), `<span class="low">${b.stok} ${esc(b.satuan)}</span>`]))}</div>
    <div class="card"><h3>Total Stok per Kategori</h3>${per.map(([n, v]) => `<div class="bar"><span>${esc(n)}</span><i style="width:${v / mx * 60}%"></i><b>${v}</b></div>`).join('')}</div>`;
};

const lap = () => R.map(x => { const h = Object.keys(x.rows[0] || {});
  return `<div class="card"><h3>${esc(x.title)}</h3>` + (h.length ? tbl(h, x.rows.map(r => h.map(k => k.includes('(Rp)') ? rp(r[k]) : esc(r[k])))) : '<p class="mut">Tidak ada data.</p>') + '</div>'; }).join('');

const crud = t => { const c = TBL[t], rows = [...D[t]];
  if (t == 'barang_masuk' || t == 'barang_keluar') rows.sort((a, b) => b.tanggal.localeCompare(a.tanggal) || b[c.pk] - a[c.pk]);
  return `<div class="card"><div class="tools"><input id="q" placeholder="Cari ${c.label}..."><button class="btn" data-a="add" data-t="${t}">+ ${c.add}</button></div>` +
    tbl(['No', ...c.cols.map(x => x[1]), 'Aksi'], rows.map((r, i) => [i + 1, ...c.cols.map(x => esc(cell(t, r, x))),
      (c.edit ? `<button class="btn sm" data-a="edit" data-t="${t}" data-id="${r[c.pk]}">Edit</button> ` : '') +
      `<button class="btn sm d" data-a="del" data-t="${t}" data-id="${r[c.pk]}">Hapus</button>`])) + '</div>'; };

function render() {
  const p = location.hash.slice(1) || 'dashboard';
  const pages = [['dashboard', 'Dashboard'], ...Object.entries(TBL).map(([k, v]) => [k, v.label]), ['laporan', 'Laporan']];
  $('#nav').innerHTML = pages.map(([k, l]) => `<a href="#${k}" class="${k == p ? 'on' : ''}">${l}</a>`).join('');
  $('#ttl').textContent = (pages.find(x => x[0] == p) || pages[0])[1];
  $('#view').innerHTML = p == 'laporan' ? lap() : TBL[p] ? crud(p) : dash();
}

function fld([k, l, ty, o = {}], v, edit) {
  const lock = edit && o.lock; let h;
  if (ty.fk) { const T = TBL[ty.fk];
    h = `<select name="${k}" required><option value="">-- pilih --</option>${D[ty.fk].map(r => `<option value="${r[T.pk]}" ${r[T.pk] == v ? 'selected' : ''}>${esc((T.labf || T.lab)(r))}</option>`).join('')}</select>`; }
  else h = `<input name="${k}" type="${ty}" value="${esc(v)}" ${o.opt ? '' : 'required'} ${ty == 'number' ? `min="${o.min ?? 0}" step="${o.step ?? 1}"` : ''} ${lock ? 'readonly' : ''}>`;
  return `<label>${l}${h}${lock ? `<small>${o.lock}</small>` : ''}</label>`;
}

async function form(t, row) {
  const c = TBL[t];
  await load();
  $('#dt').textContent = (row ? 'Edit ' : 'Tambah ') + c.label;
  $('#ferr').textContent = '';
  $('#ff').innerHTML = c.form.map(f => fld(f, row ? row[f[0]] : (f[3] && f[3].def === 'today' ? hariIni() : (f[3] && f[3].def) ?? ''), !!row)).join('');
  $('#dlg').showModal();
  $('#frm').onsubmit = async e => {
    e.preventDefault();
    const fd = new FormData(e.target), b = {};
    c.form.forEach(([k, , ty]) => { const v = fd.get(k); b[k] = (ty == 'number' || ty.fk) && v !== '' ? Number(v) : v; });
    try {
      await api(row ? 'PUT' : 'POST', t, row && row[c.pk], b);
      $('#dlg').close(); await load(); render(); toast(row ? 'Data berhasil diperbarui' : 'Data berhasil disimpan');
    } catch (err) { $('#ferr').textContent = err.message; }
  };
}

const toast = (m, bad) => { const t = $('#toast'); t.textContent = m; t.style.background = bad ? '#d64545' : ''; t.classList.add('on'); setTimeout(() => t.classList.remove('on'), 3200); };

$('#cx').onclick = () => $('#dlg').close();
$('#reset').onclick = async () => {
  if (!confirm('Kembalikan data contoh ke kondisi awal?')) return;
  M = JSON.parse(JSON.stringify(SEED)); simpan(); await load(); render(); toast('Data contoh dikembalikan');
};
$('#view').onclick = async e => {
  const b = e.target.closest('button[data-a]'); if (!b) return;
  const { a, t, id } = b.dataset;
  if (a == 'add') form(t); else if (a == 'edit') form(t, byId(t, id));
  else if (confirm('Hapus data ini?')) {
    try { await api('DELETE', t, id); await load(); render(); toast('Data berhasil dihapus'); } catch (err) { toast(err.message, 1); }
  }
};
$('#view').oninput = e => {
  if (e.target.id != 'q') return;
  const v = e.target.value.toLowerCase();
  document.querySelectorAll('#view tbody tr').forEach(tr => tr.hidden = !tr.textContent.toLowerCase().includes(v));
};
addEventListener('hashchange', render);
load().then(render).catch(e => toast(e.message, 1));
