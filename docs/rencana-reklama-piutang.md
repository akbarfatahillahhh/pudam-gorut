# Port Cisiran: Reklama / Input Piutang (belum dikerjakan)

Status: **dokumen rencana**, belum ada kode. Ditulis 2026-11-23.

## 1. Apa fiturnya

Form untuk menagih **piutang lama** — tagihan yang tidak pernah terbit karena
pelanggan telat atau petugas tidak mencatat meter pada bulan tersebut. Sekali
input = satu pelanggan, satu tahun, sampai 12 bulan sekaligus.

Di aplikasi lama (`/home/reev/aa/bs`): `admin/module/tagihan/form_rek_lama.php`,
dipanggil lewat `media.php?module=tagihan&act=reklama`.
Menu yang menautkannya: **Administrasi**, **Loket/Kantor**, **Piutang**,
**Super Admin** — jadi ini fitur yang cukup sering dipakai.

## 2. Kondisi di aplikasi lama (bs)

### Form-nya hidup, simpannya mati

`form_rek_lama.php` berisi form lengkap (12 kolom bulan + autocomplete
pelanggan + konfirmasi Swal), dan mengirim ke `aksi_simpan_piutang.php`.

**`aksi_simpan_piutang.php` isinya kosong.** File itu hanya membaca variabel
`$_POST`, tanpa satu pun statement `INSERT`. Jadi tombol "Lanjut Simpan" di
produksi mengembalikan response kosong dan tidak menyimpan apa pun.

Kesimpulan: di `bs`, fitur ini **tampil tapi tidak berfungsi**.

### Rumus yang tertinggal di file mati itu

Meski simpanannya kosong, perhitungan yang dimaksud masih terbaca jelas:

```
input pengguna = nominal per bulan, SUDAH TERMASUK bea admin
tag_atas      = max((nominal − bea_admin) − (10 × tarif_bawah_10), 0)
tag_bawah     = 10 × tarif_bawah_10            // selalu penuh, bukan min(pemakaian,10)
jlh_tagihan   = nominal                        // apa adanya, tidak dihitung ulang
pemakaian_air = 0
no_rek        = '0'
denda         = 5000
```

Bulan yang diisi `0` dilewati (tidak dibuatkan baris).

### Sisa data di produksi

Nomor rekening `'0'` dan denda `5000` di atas meninggalkan jejak:

| Kondisi | Jumlah baris |
|---|---|
| `t_tagihan.no_rek = '0'` | **17.801** |
| `t_tagihan.denda = 5000` | **64.218** |

Baris `no_rek='0'` juga jadi sumber utama **506 nomor rekening duplikat** yang
sekarang terdeteksi di `/billing/tagihan/duplikat` — semua baris itu berbagi
nomor yang sama.

## 3. Kenapa ini perlu diputuskan dulu, bukan langsung diport

Keputusan desain yang harus diambil **sebelum** menulis kode:

**Piutang lama dicatat sebagai apa?**

| Opsi | Konsekuensi |
|---|---|
| a. Baris `t_tagihan` biasa (seperti maksud `bs`) | Laporan DRD, rekap piutang, dan hitungan tunggakan loket otomatis ikut. Tapi mengotori statistik pemakaian air (`pemakaian_air = 0`) dan butuh penanda agar bisa dibedakan. |
| b. Tabel terpisah, digabung saat lapor | Data bersih, tapi semua laporan penagihan harus diubah untuk ikut menghitungnya. |
| c. Baris `t_tagihan` + kolom penanda (mis. `ref_id = 'REKLAMA'`) | Kompromi. Laporan lama tetap jalan; laporan baru bisa memfilter. `t_tagihan.ref_id` **sudah ada** di skema. |

Rekomendasi: **c**, memakai `ref_id` yang sudah tersedia, dan **tidak** memakai
`no_rek = '0'` — nomor rekening tetap harus unik per periode. Nomor unik sudah
disediakan `Billing\Tagihan::nomorRekening()` (port dari perbaikan yang sama).

## 4. Rancangan kalau dilanjutkan

### Controller: `App\Controllers\Billing\Piutang`

| Method | Isi |
|---|---|
| `index()` | halaman form: autocomplete pelanggan, pilih tahun, 12 kolom bulan |
| `data_pelanggan()` | sudah ada di `Billing\Tagihan_data::pelanggan()`, pakai ulang |
| `simpan()` | validasi + insert per bulan yang > 0 |
| `cek_periode()` | apakah pelanggan sudah punya rekening di bulan itu |

### Aturan simpan

1. Tolak kalau pelanggan tidak punya `id_jasa` / `id_jenis` (tidak bisa hitung tarif).
2. Untuk tiap bulan yang nominalnya > 0:
   - tolak kalau `t_tagihan` untuk pelanggan+bulan itu **sudah ada** (supaya tidak dobel)
   - hitung `tag_bawah`, `tag_atas` seperti rumus di atas
   - `no_rek` = `nomorRekening(bulan, tahun)` — **bukan** `'0'`
   - `ref_id` = `'REKLAMA'`
   - `id_user` = `session('id_session')`
3. Seluruh baris dalam **satu transaksi**: kalau satu bulan gagal, tidak ada yang tersimpan.
4. Kembalikan ringkasan: berapa bulan tersimpan, nomor rekening yang dibuat.

### Yang harus diperbaiki dari `bs`

| Masalah di `bs` | Perbaikan |
|---|---|
| `aksi_simpan_piutang.php` kosong | tulis simpan yang benar-benar jalan |
| `no_rek = '0'` → 17.801 baris bentrok | pakai nomor urut per periode |
| `denda = 5000` dipatok tanpa alasan | tanya: denda ikut atau tidak? Jangan hardcode |
| tidak ada cek duplikat bulan | tolak pelanggan+bulan yang sudah ada |
| tidak ada transaksi | bungkus `transStart`/`transComplete` |
| `id_user` diambil dari `$_SESSION[namauser]` | pakai `session('id_session')` |

### Perkiraan kerja

Kurang lebih 1 hari: 1 controller (~250 baris), 1 view form, 1 method simpan,
plus uji dengan data pelanggan nyata.

## 5. Yang perlu dijawab sebelum mulai

1. Piutang lama dicatat sebagai baris `t_tagihan` biasa, tabel terpisah, atau
   baris bertanda `ref_id`?
2. Denda ikut dicatat? Kalau ya, berapa dan dihitung dari apa?
3. Bulan yang sudah punya rekening normal — dilewati, ditolak, atau ditimpa?
4. Apakah 17.801 baris `no_rek='0'` yang sudah ada perlu dibersihkan/dinomori
   ulang (bisa memakai fitur perbaiki nomor di `/billing/tagihan/duplikat`
   yang sudah jalan)?

---

# Lampiran: Migrasi tipe kolom angka `t_tagihan` (belum dikerjakan)

Status: **rencana**, belum ada perubahan database. Ditulis 2026-11-23.
Diminta oleh user: "tangani varchar dulu" → diputuskan **skip dulu, catat ke MD**.

## Latar belakang

Sembilan kolom angka di `t_tagihan` bertipe `varchar`, bukan angka:

| Kolom | Tipe sekarang | Dipakai untuk |
|---|---|---|
| `tarif_dibawah_10` | `varchar(16)` | tarif per m3, blok 0-10 |
| `tarif_diatas_10` | `varchar(16)` | tarif per m3, di atas 10 |
| `pemakaian_air` | `varchar(10)` | angka meter bulan ini |
| `pemakaian_air_lalu` | `varchar(10)` | angka meter bulan lalu |
| `tag_dibawah_10` | `varchar(16)` | rupiah blok 0-10 |
| `tag_diatas_10` | `varchar(16)` | rupiah di atas 10 |
| `by_admin` | `varchar(16)` | biaya admin |
| `jlh_tagihan` | `varchar(16)` | jumlah tagihan |
| `denda` | `varchar(16)` | denda |

`bayar` sudah `int`, jadi tidak termasuk.

## Akibat yang sudah terjadi (terbukti)

Tipe varchar ini bukan sekadar teori — sudah memakan korban berulang:

1. **Crash TypeError.** `LppBerjalan` melempar
   `Unsupported operand types: string - string` saat menghitung
   `pemakaian_air - pemakaian_air_lalu` (PHP 8). Sudah diperbaiki lokal
   dengan cast, tapi akarnya tetap.
2. **Perbandingan meleset.** Beberapa query memakai `jlh_tagihan > X`
   yang di MySQL membandingkan string, bukan angka. Sudah ditambal dengan
   `CAST(... AS SIGNED)` di tiap query.
3. **Urutan salah.** `ORDER BY no_rek` mengurutkan sebagai teks
   (`"10" < "9"`), sehingga `MAX(no_rek)` untuk nomor rekening harus
   memakai `CAST(no_rek AS UNSIGNED)`.
4. **Setiap query baru wajib ingat cast.** Itu beban permanen dan sumber
   bug berulang — sudah 6 tempat di branch ini yang perlu ditambal.

## Isi data saat ini (audit 2026-11-23)

`SELECT COUNT(*) FROM t_tagihan` → **494.131** baris.

Nilai yang **bukan angka murni** (`NOT REGEXP '^-?[0-9]+$'`):

| Kolom | Baris kotor | Contoh isi |
|---|---|---|
| `pemakaian_air` | 92 | `''` (54), `' 323'` (4), `'     1128'` (1) |
| `pemakaian_air_lalu` | 70 | `''` (70) |
| `jlh_tagihan` | 9 | `''` (6), `'0p'`, `'38100 '`, `'41300;'` |
| `tag_dibawah_10` | 2 | `''` (2) |
| `tag_diatas_10` | 2 | `''` (2) |
| `tarif_*`, `by_admin`, `denda` | 0 | — |

Total 175 baris. Semua bisa dibersihkan tanpa kehilangan informasi:
string kosong → `0`, spasi di-trim, `0p`/`41300;` → ambil digitnya.

## Sumber data kotor

`/home/reev/aa/bs/admin/module/laporan/save-edit.php`:

```php
$konversi_rupiah = str_replace(".", "", $_POST["editval"]);
mysql_query("UPDATE t_tagihan set " . $_POST["column"] . " = '".$konversi_rupiah."' ...");
```

- Nama kolom datang langsung dari `$_POST['column']` (tidak divalidasi).
- Nilainya string mentah dari `contenteditable` — hanya titik yang dibuang.
- **Fungsi `mysql_*`** sudah dihapus di PHP 7+, jadi baris ini tidak pernah
  jalan di PHP modern. Artinya halaman kartu kontrol / LPP tunggakan yang
  memakai `saveToDatabase()` **sudah tidak bisa menyimpan** — tombolnya
  gagal diam-diam. Ini temuan tambahan yang perlu dicatat terpisah.

## Rencana migrasi (kalau disetujui)

### Langkah 0 — backup

```bash
podman exec mysql-import-db \
  mysqldump -u pdam_user -p'#PDAMWater!' db_pdam t_tagihan \
  > backup_t_tagihan_$(date +%Y%m%d_%H%M%S).sql
```

Verifikasi: jumlah baris hasil `grep -c "INSERT INTO"` dan ukuran file wajar.

### Langkah 1 — bersihkan nilai kotor

```sql
-- String kosong / spasi saja -> 0
UPDATE t_tagihan SET pemakaian_air = '0'
  WHERE TRIM(pemakaian_air) = '';
-- sisanya: buang karakter non-digit
UPDATE t_tagihan SET jlh_tagihan = REGEXP_REPLACE(jlh_tagihan, '[^0-9-]', '')
  WHERE jlh_tagihan NOT REGEXP '^-?[0-9]+$';
```

Ulangi untuk tiap kolom yang kotor, lalu **verifikasi ulang** bahwa
`NOT REGEXP '^-?[0-9]+$'` menghasilkan 0 baris di semua kolom.

### Langkah 2 — ubah tipe

`ALTER TABLE` untuk 494.131 baris akan mengunci tabel beberapa detik sampai
menit. Lakukan di luar jam operasional.

```sql
ALTER TABLE t_tagihan
  MODIFY tarif_dibawah_10  BIGINT NULL,
  MODIFY tarif_diatas_10   BIGINT NULL,
  MODIFY pemakaian_air     INT    NULL,
  MODIFY pemakaian_air_lalu INT   NULL,
  MODIFY tag_dibawah_10    BIGINT NULL,
  MODIFY tag_diatas_10     BIGINT NULL,
  MODIFY by_admin          BIGINT NULL,
  MODIFY jlh_tagihan       BIGINT NULL,
  MODIFY denda             BIGINT NULL;
```

Ada 8 indeks pada kolom-kolom ini; MySQL akan membangun ulang. Pantau dengan
`SHOW PROCESSLIST`.

### Langkah 3 — bersihkan kode yang tidak perlu cast

Setelah kolom jadi angka, `CAST(... AS SIGNED)` di query bisa dilepas:

- `app/Controllers/Laporan/DrdAir.php`
- `app/Controllers/Laporan/LppPetugas.php`
- `app/Controllers/Laporan/LppBerjalan.php`
- `app/Controllers/Laporan/RekapDrdAir.php`
- `app/Controllers/Laporan/RincianPiutangPerunit.php`
- `app/Controllers/Laporan/RekapitulasiPiutang.php`

### Langkah 4 — perbaiki penulisnya

`save-edit.php` di `bs` harus divalidasi, atau dinonaktifkan. Tanpa ini,
`INSERT` baru dengan nilai non-numerik akan **gagal** di
`STRICT_TRANS_TABLES` (yang aktif di server sekarang) — bukan lagi diam-diam
tersimpan sebagai string.

## Risiko

| Risiko | Dampak | Mitigasi |
|---|---|---|
| `bs` menulis nilai non-numerik | INSERT gagal, transaksi hilang | perbaiki `save-edit.php` lebih dulu, atau matikan fitur itu |
| `ALTER TABLE` mengunci tabel | loket tidak bisa bayar | jalankan di luar jam kerja, ukur dulu di salinan |
| Kode yang mengandalkan string | perbandingan/urutan berubah | sudah diaudit: semua pemakai angka akan lebih benar, bukan lebih salah |
| Rollback | — | restore dari backup Langkah 0 |

## Yang harus diputuskan

1. Setuju migrasi ke angka, atau tetap varchar dan tambal per query?
   *(User sudah memilih: **skip dulu**, jadi tetap varchar.)*
2. Kalau migrasi: `BIGINT` (sesuai isi sekarang) atau `DECIMAL(15,2)`
   (kalau nanti butuh sen)? *(User: **skip**.)*
3. Apakah `save-edit.php` di `bs` perlu diperbaiki terpisah? Itu bug
   sendiri: fungsi `mysql_*` sudah tidak ada, jadi fitur edit-inline di
   kartu kontrol dan LPP tunggakan **mati** di produksi.

## Catatan tambahan yang perlu diverifikasi

Temuan `mysql_*` di `save-edit.php` bukan kasus tunggal. Hasil hitung
2026-11-23:

```
199 pemakaian mysql_query / mysql_fetch / mysql_num_rows
tersebar di 40+ file /home/reev/aa/bs
```

Fungsi `mysql_*` dihapus sejak PHP 7. Artinya **setiap jalur yang masih
memakainya sudah mati** — tidak error, tidak menyimpan, hanya diam.
Contoh yang terkonfirmasi:

| File | Pemakaian `mysql_*` | Status |
|---|---|---|
| `admin/module/laporan/save-edit.php` | 1 | mati (edit inline kartu kontrol & LPP tunggakan) |
| `admin/module/laporan/get_drdunittahun.php` | 15 | mati |
| `admin/user_login.php` | 2 | perlu dicek — login masih jalan, jadi kemungkinan ada jalur cadangan |
| `admin/module/tagihan/get_daftar_tagihan_bulanan.php` | 1 | perlu dicek |

**Koreksi penting:** `admin/module/tagihan/aksi_simpan_pemakaian.php` — jalur
utama entry pemakaian — ternyata memakai **`mysqli_*`**, bukan `mysql_*`.
Jadi entry pemakaian tetap hidup. Dugaan awal bahwa jalur tulis utama mati
**tidak terbukti**; sudah diperiksa langsung:

```
aksi_simpan_pemakaian.php: mysqli_query, mysqli_fetch_array, mysqli_num_rows
```

Ini mengurangi tingkat bahaya temuan `mysql_*`: yang mati adalah fitur
pinggiran (edit inline, rekap per tahun), bukan jalur tulis utama.

**Tetap perlu dipetakan** sebelum migrasi skema, karena tujuannya sama —
tahu persis siapa yang menulis ke `t_tagihan`:

```bash
grep -rn "mysql_query\|mysql_fetch\|mysql_num_rows" /home/reev/aa/bs --include=*.php
```

Untuk tiap file tentukan: (a) apakah dipanggil dari menu/aksi yang hidup,
dan (b) apakah ada padanan `mysqli_*` di file yang sama.
