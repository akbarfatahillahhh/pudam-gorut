# Hak Akses Modul per User

Status: **sudah dikerjakan** 2026-11-23. Halaman `/master/akses`.

## 1. Apa yang ditambahkan

Dua lapis hak akses:

| Lapis | Tabel | Menentukan |
|---|---|---|
| Sistem | `t_akses_sistem` (sudah ada) | boleh masuk Billing? boleh masuk E-Kas? |
| **Modul** | `t_akses_modul` (**baru**) | modul mana di dalam sistem itu |

Daftar modul tidak di-hardcode di form: sumbernya `App\Helpers\sistem_helper.php`
-> `sistem_modul()`, yang membaca kunci `'modul'` pada tiap item di
`sistem_menu()`. Tambah menu baru + isi `'modul'` => otomatis muncul di form.

## 2. DDL tabel baru

**Belum dijalankan otomatis.** Terapkan manual:

```bash
podman exec -i mysql-import-db mysql -updam_user -p'#PDAMWater!' db_pdam \
    < database/t_akses_modul.sql
```

```sql
CREATE TABLE IF NOT EXISTS `t_akses_modul` (
    `id`         INT          NOT NULL AUTO_INCREMENT,
    `id_session` VARCHAR(100) NOT NULL,
    `sistem`     VARCHAR(20)  NOT NULL,
    `modul`      VARCHAR(100) NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uniq_user_modul` (`id_session`, `modul`),
    KEY `idx_user_sistem` (`id_session`, `sistem`),
    KEY `idx_sistem` (`sistem`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
```

Kolom `id_session` sengaja `VARCHAR(100)` supaya cocok dengan
`t_akses_sistem` dan `users.id_session` (yang menyimpan 50 karakter hex).
Tidak ada foreign key, konsisten dengan tabel akses lama.

## 3. Aturan penyimpanan

| Kondisi | Arti |
|---|---|
| Tidak ada baris `t_akses_modul` untuk user + sistem | **belum dibatasi** = semua modul sistem itu boleh |
| Ada minimal satu baris | hanya modul yang terdaftar yang boleh |
| Semua modul dicentang di form | baris **tidak disimpan** (= belum dibatasi), supaya modul baru nanti ikut otomatis. Terverifikasi: 10 modul Billing dicentang -> `t_akses_modul` 0 baris, kolom Modul di tabel menampilkan "Semua modul" |
| Nol sistem dicentang | **ditolak**. Tanpa baris `t_akses_sistem`, `sistem_akses_user()` jatuh ke fallback level dan justru memberi akses bawaan penuh |
| Sistem dicentang tapi modulnya nol | **ditolak**, karena alasan yang sama di tingkat modul |
| `users.level = 'admin'` | selalu semua sistem + semua modul; baris di dua tabel itu tidak berlaku |

Untuk mematikan akses user sepenuhnya: pakai **blokir akun** di User/Akun,
bukan mengosongkan centang sistem.

## 4. Yang tetap tidak bisa diatur di sini

Grup menu bertanda `'admin' => true` (Data Master, Tagihan Kantor,
Administrasi) tidak masuk `sistem_modul()`, jadi:

- tidak muncul di form,
- tetap dijaga `sistem_prefix_admin()` + `sistem_wajib_admin()`,
- centang modul **tidak** bisa membukanya untuk non-admin.

Aturan admin itu dicek lebih dulu di `sistem_boleh_path()`, sebelum cek modul.

## 5. Penegakan (enforcement)

Dua tempat dipakai bersama, jadi menu dan URL tidak bisa berbeda:

```
FilterSistem::before()
  -> sistem_boleh_path($path)
       -> sistem_wajib_admin()          // admin-only
       -> sistem_boleh()                // t_akses_sistem
       -> sistem_boleh_modul()          // t_akses_modul
            -> sistem_modul_dari_path() // prefix terpanjang menang
```

`sistem_menu_terlihat($sistem)` menyaring menu untuk sidebar dan
`/Home/get_sidebar`, sehingga item yang tidak boleh dibuka juga hilang dari
daftar.

**Penting — modul `selalu`:** item dashboard ditandai `'selalu' => true`.
Modul ini tidak bisa dicabut, dan pencekannya **hanya untuk path persis**
(`billing`), bukan sub-path. Kalau ikut menaungi sub-path, semua halaman yang
belum punya modul akan lolos lewat dashboard — ini sempat terjadi saat uji
(`/Billing/Loket/tagihan` dan `Master/Akun/data` lolos untuk user terbatas)
dan sudah diperbaiki.

## 6. Perbaikan redirect loop

`FilterSistem` dulu melempar user ke `sistem_home_url(sistem_aktif())`.
Untuk user tanpa akses E-Kas yang membuka `/ekas`, tujuan itu `/ekas` lagi:
**redirect loop**. Sekarang:

```php
$tujuan = sistem_aktif();
if (!sistem_boleh($tujuan)) {
    $tujuan = sistem_akses_user()[0] ?? null;
}
if ($tujuan === null) {
    session()->destroy();
    return redirect()->to('/login');
}
```

## 7. Daftar kode modul

Grup Home & Billing (non-admin), **10 modul yang bisa diatur**:

| Kode | Label |
|---|---|
| `billing.pelanggan` | Pelanggan |
| `billing.loket` | Loket Pembayaran |
| `billing.laporan.drd_air` | DRD Air |
| `billing.laporan.rekap_drd` | DRD Unit/Jenis |
| `billing.laporan.piutang_perunit` | Rincian Piutang Perunit |
| `billing.laporan.pelanggan_duplikat` | Pelanggan Duplikat |
| `billing.laporan.rekapitulasi_piutang` | Rekapitulasi Piutang |
| `billing.laporan.lpp_berjalan` | LPP Air Periodik |
| `billing.laporan.lpp_petugas` | LPP Per Petugas |
| `billing.laporan.tunggakan_unit` | Rincian Tunggakan per Unit |

`billing.home` (dashboard) memang ada di `sistem_modul()` dengan
`'selalu' => true`, tetapi **disaring keluar** oleh
`sistem_modul_per_sistem()`: tidak muncul di form, tidak dihitung, tidak
disimpan. Alasannya, mencabutnya membuat redirect setelah login berputar.

Grup E-Kas (non-admin), **10 modul yang bisa diatur**:

| Kode | Label |
|---|---|
| `kas.keluar` | Kas Pengeluaran |
| `kas.riwayat` | Riwayat Kas Pengeluaran |
| `kas.masuk` | Penerimaan Kas Air |
| `kas.masuk_non_air` | Penerimaan Kas Non Air |
| `kas.faktur_pembelian` | Faktur Pembelian |
| `kas.permintaan` | Entry Permintaan |
| `kas.gudang.barang` | Data Barang |
| `kas.gudang.cek_permintaan` | Cek Permintaan |
| `kas.gudang.opname` | Opname Stok |
| `kas.laporan.rekap_persediaan` | Rekap Persediaan |

Modul admin (tidak bisa diatur): pelanggan/pasang-meter/kelompok tarif/jenis
pelanggan/wilayah, tagihan, catat meter, billing system, cek no rekening,
perkiraan, grup perkiraan, bank, jabatan, tugas pokok, pokok, pegawai,
user/akun, absensi, slip gaji, persuratan, daftar hadir, kinerja pegawai.

## 8. Hasil uji 2026-11-23

Login sebagai `admin04` (`level = loket`, non-admin), lalu akses diatur lewat
form dan diverifikasi per URL:

| Setelan | 200 | 307 (ditolak) |
|---|---|---|
| Billing: `pelanggan` + `loket` | `/billing`, `/billing/pelanggan`, `/billing/loket`, `/billing/loket/kwitansi_pdf/1`, `/Billing/Loket/tagihan` | `/billing/laporan/*`, `/ekas`, `/master/akses`, `/master/akun`, `/Master/Akun/data`, `/Master/Pelanggan/data`, `/billing/tagihan` |
| Dipulihkan (tanpa baris) | `/billing`, `/billing/loket`, `/billing/laporan/drd-air`, `/billing/laporan/lpp-petugas` | `/ekas`, `/ekas/keluar` |
| E-Kas: `masuk` + `gudang.barang` | `/ekas`, `/ekas/masuk`, `/ekas/gudang/barang` | `/ekas/keluar`, `/ekas/masuk-non-air`, `/ekas/gudang/cek-permintaan`, `/ekas/laporan/rekap-persediaan`, `/billing`, `/billing/pelanggan` |

Sidebar ikut menyusut. Untuk user Billing terbatas hanya tersisa
`Home > Dashboard`, `Billing > Pelanggan`, `Billing > Loket Pembayaran`, dan
`/Home/get_sidebar` mengembalikan
`["dashboard_menu","pelanggan_menu","loket_menu"]`.

Validasi yang diuji:

| Kiriman | Hasil |
|---|---|
| Sistem dicentang, modul kosong | ditolak: "Pilih minimal satu modul untuk sistem Billing…" |
| Tanpa sistem sama sekali | ditolak: "Pilih minimal satu sistem…" |
| `sistem[]=billing` + `modul[billing][]=kas.keluar` | modul asing dibuang, lalu ditolak karena kosong |
| Target user `level = admin` | ditolak: "Admin selalu punya akses ke semua sistem dan modul." |
| Semua 10 modul Billing dicentang | tersimpan `t_akses_sistem` = billing, `t_akses_modul` **0 baris** |
| Billing: `loket` + `pelanggan`; E-Kas: `masuk` | kolom Modul menampilkan "2 dari 10 modul" (Billing) dan "1 dari 10 modul" (E-Kas) |

Regresi halaman sebagai admin (`admin01`): 35 URL Billing + E-Kas + master
semua 200, termasuk tab Billing System, seluruh laporan, dan perkiraan group.

## 9. Berkas yang disentuh

| Berkas | Perubahan |
|---|---|
| `database/t_akses_modul.sql` | baru — DDL |
| `app/Helpers/sistem_helper.php` | `sistem_modul()`, `sistem_modul_per_sistem()`, `sistem_modul_dari_path()`, `sistem_akses_modul()`, `sistem_boleh_modul()`, `sistem_menu_terlihat()`; kunci `'modul'`/`'selalu'` di menu; `sistem_boleh_path()` + `sistem_prefix_admin()` |
| `app/Controllers/Master/AksesModul.php` | baru — index/data/edit/simpan |
| `app/Views/master/akses/main.php` | baru — tabel user |
| `app/Views/master/akses/modal/edit.php` | baru — form centang sistem + modul |
| `app/Filters/FilterSistem.php` | tujuan redirect yang tidak bisa loop |
| `app/Controllers/Home.php` | `get_sidebar()` memakai `sistem_menu_terlihat()` |
| `app/Views/layouts/partials/sidebar.php` | memakai `sistem_menu_terlihat()`, filter admin dipindah ke helper |
| `app/Config/Routes.php` | `GET /master/akses` |

## 10. Skema tetap

Tidak ada perubahan tipe kolom. Migrasi `t_tagihan` varchar -> numerik masih
tertunda; lihat bagian lampiran di `docs/rencana-reklama-piutang.md`.
