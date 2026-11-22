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
