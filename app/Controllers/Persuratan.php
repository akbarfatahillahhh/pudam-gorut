<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use Hermawan\DataTables\DataTable;

/**
 * Modul Persuratan (pengajuan izin/cuti/sakit pegawai + surat keluar).
 *
 * Hasil port dari aplikasi lama. Perbedaan penting:
 *  - DataTables ditulis ulang memakai Hermawan\DataTables (aplikasi lama
 *    memakai Irsyadulibad\DataTables yang tidak terpasang di sini).
 *  - Join ke ekinerja_mst_pengajuan diperbaiki; query lama
 *    (`ekinerja_mst_pengajuan.id = ekinerja_pengajuan.id_pengajuan`)
 *    selalu gagal karena tabel master tidak punya kolom `id`.
 *  - Tombol Terima/Tolak sekarang benar-benar menyimpan hasil verifikasi
 *    (aplikasi lama hanya memunculkan alert 'dalam pengembangan').
 */
class Persuratan extends BaseController
{
    var $title;

    public function __construct()
    {
        $this->title = 'Persuratan';
    }

    public function index()
    {
        return view('persuratan/main', ['title' => $this->title]);
    }

    /**
     * Sumber data tabel pengajuan pegawai.
     *
     * ekinerja_pengajuan.id_pengajuan menyimpan nomor urut jenis pengajuan
     * (0 = sakit, 1 = izin, 2 = cuti), bukan PK tabel master — tabel
     * ekinerja_mst_pengajuan hanya punya kd_pengajuan dan pengajuan. Supaya
     * jenis tetap tampil, urutan itu dipetakan lewat ROW_NUMBER(). Urutan
     * kolom sengaja eksplisit (FIELD) agar hasilnya tidak bergantung pada
     * urutan fisik baris.
     */
    public function data_pengajuan()
    {
        // Hermawan\DataTables membaca parameter `columns` dari request dan
        // akan gagal kalau tidak ada (mis. saat endpoint dipanggil manual).
        // Untuk kasus itu kembalikan bentuk JSON yang sama tanpa paging.
        if (! is_array($this->request->getGet('columns'))) {
            $rows = $this->builderPengajuan()->get()->getResult();

            $data = array_map(function ($row) {
                return [
                    'id'               => esc($row->id),
                    'posting'          => $this->formatTanggal($row->posting, 'd-m-Y H:i'),
                    'terhitung_mulai'  => $this->formatTanggal($row->terhitung_mulai),
                    'mulai_masuk'      => $this->formatTanggal($row->mulai_masuk),
                    'persetujuan'      => esc($row->persetujuan),
                    'user_verifikator' => esc($row->user_verifikator),
                    'keterangan'       => esc($row->keterangan),
                    'bukti_terlampir'  => esc($row->bukti_terlampir),
                    'nik'              => esc($row->nik),
                    'nama_peg'         => esc($row->nama_peg),
                    'jenis'            => esc($row->jenis),
                    'aksi'             => $this->tombolAksi($row->id),
                ];
            }, $rows);

            return $this->response->setJSON([
                'draw'            => (int) ($this->request->getGet('draw') ?? 1),
                'recordsTotal'    => count($data),
                'recordsFiltered' => count($data),
                'data'            => $data,
            ]);
        }

        // Catatan: callback edit() di Hermawan menerima seluruh baris.
        return DataTable::of($this->builderPengajuan())
            ->edit('posting', function ($row) {
                return $this->formatTanggal($row->posting, 'd-m-Y H:i');
            })
            ->edit('terhitung_mulai', function ($row) {
                return $this->formatTanggal($row->terhitung_mulai);
            })
            ->edit('mulai_masuk', function ($row) {
                return $this->formatTanggal($row->mulai_masuk);
            })
            ->add('aksi', function ($row) {
                return $this->tombolAksi($row->id);
            })
            ->toJson(true);
    }

    private function formatTanggal($value, string $format = 'd-m-Y'): string
    {
        if (empty($value)) {
            return '-';
        }

        $waktu = strtotime((string) $value);

        return $waktu ? date($format, $waktu) : '-';
    }

    /**
     * Builder dasar daftar pengajuan (dipakai tabel dan fallback JSON).
     */
    private function builderPengajuan()
    {
        $table = $this->db->table('ekinerja_pengajuan');
        $table->select('
            ekinerja_pengajuan.id,
            ekinerja_pengajuan.posting,
            ekinerja_pengajuan.terhitung_mulai,
            ekinerja_pengajuan.mulai_masuk,
            ekinerja_pengajuan.persetujuan,
            ekinerja_pengajuan.user_verifikator,
            ekinerja_pengajuan.keterangan,
            ekinerja_pengajuan.bukti_terlampir,
            ekinerja_pengajuan.id_pegawai,
            pegawai.NIK as nik,
            pegawai.NAMA as nama_peg,
            jenis_pengajuan.pengajuan as jenis
        ');
        $table->join(
            "(SELECT pengajuan, ROW_NUMBER() OVER (ORDER BY FIELD(kd_pengajuan, 'sakit', 'izin', 'cuti')) - 1 AS urutan
              FROM ekinerja_mst_pengajuan) jenis_pengajuan",
            'jenis_pengajuan.urutan = ekinerja_pengajuan.id_pengajuan',
            'left',
            false
        );
        $table->join('pegawai', 'pegawai.ID = ekinerja_pengajuan.id_pegawai', 'left');
        $table->orderBy('ekinerja_pengajuan.posting', 'DESC');

        return $table;
    }

    private function tombolAksi(string $id): string
    {
        // esc(..., 'js') supaya id tetap aman walau nanti formatnya berubah
        // (atribut onclick di-decode HTML lebih dulu sebelum JS membacanya).
        return '<button type="button" class="btn btn-sm btn-primary" onclick="lihat(\'' . esc($id, 'js') . '\')">'
            . '<i class="ti ti-eye"></i> Lihat</button>';
    }

    /**
     * Modal verifikasi pengajuan.
     */
    public function modal_verfak()
    {
        if (! $this->request->isAJAX()) {
            return $this->response->setStatusCode(403)->setJSON(['status' => 'error', 'message' => 'Akses ditolak']);
        }

        $id = $this->request->getPost('id');

        $pengajuan = $this->builderPengajuan()
            ->where('ekinerja_pengajuan.id', $id)
            ->get()
            ->getRow();

        if (! $pengajuan) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Data pengajuan tidak ditemukan']);
        }

        // Jabatan aktif pegawai (kalau ada) untuk ditampilkan di modal.
        $jabatan = $this->db->table('riwayat_jabatan')
            ->select('jabatan.JABATAN')
            ->join('jabatan', 'jabatan.ID = riwayat_jabatan.ID_JABATAN', 'left')
            ->where('riwayat_jabatan.ID_PEGAWAI', $pengajuan->id_pegawai)
            ->where('riwayat_jabatan.STATUS', 'Aktif')
            ->orderBy('riwayat_jabatan.ID', 'DESC')
            ->get()
            ->getRow();

        // Berkas bukti hanya ditautkan kalau filenya benar-benar ada.
        $buktiAda = $pengajuan->bukti_terlampir
            && is_file(FCPATH . 'bukti_terlampir/' . $pengajuan->bukti_terlampir);

        return $this->response->setJSON([
            'status' => 'success',
            'data'   => view('persuratan/modal/verifikasi', [
                'pengajuan' => $pengajuan,
                'jabatan'   => $jabatan ? $jabatan->JABATAN : null,
                'buktiAda'  => $buktiAda,
            ]),
        ]);
    }

    /**
     * Simpan hasil verifikasi (Terima/Tolak).
     */
    public function verifikasi()
    {
        if (! $this->request->isAJAX()) {
            return $this->response->setStatusCode(403)->setJSON(['status' => 'error', 'message' => 'Akses ditolak']);
        }

        $id        = $this->request->getPost('id');
        $keputusan = $this->request->getPost('keputusan');

        if (! in_array($keputusan, ['Y', 'T'], true)) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Keputusan verifikasi tidak valid']);
        }

        $pengajuan = $this->db->table('ekinerja_pengajuan')->where('id', $id)->get()->getRow();

        if (! $pengajuan) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Data pengajuan tidak ditemukan']);
        }

        $tersimpan = $this->db->table('ekinerja_pengajuan')
            ->where('id', $id)
            ->update([
                'persetujuan'      => $keputusan,
                'user_verifikator' => session('username'),
                'status_pengajuan' => $keputusan === 'Y' ? 'Diterima' : 'Ditolak',
            ]);

        if (! $tersimpan) {
            $error = $this->db->error();
            log_message('error', 'Gagal menyimpan verifikasi persuratan: ' . ($error['message'] ?? 'unknown'));

            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Verifikasi gagal disimpan',
            ]);
        }

        return $this->response->setJSON([
            'status'  => 'success',
            'message' => $keputusan === 'Y' ? 'Pengajuan diterima' : 'Pengajuan ditolak',
        ]);
    }

    /**
     * Modal "Buat Pengajuan" (surat keluar): pilih pegawai dulu.
     */
    public function modalsuratkeluar()
    {
        if (! $this->request->isAJAX()) {
            return $this->response->setStatusCode(403)->setJSON(['status' => 'error', 'message' => 'Akses ditolak']);
        }

        return $this->response->setJSON([
            'status' => 'success',
            'data'   => view('persuratan/modal/surat_keluar'),
        ]);
    }

    /**
     * Form surat keluar setelah pegawai dipilih.
     */
    public function getform_suratkeluar()
    {
        if (! $this->request->isAJAX()) {
            return $this->response->setStatusCode(403)->setJSON(['status' => 'error', 'message' => 'Akses ditolak']);
        }

        // Nama parameter `provinsi` dipertahankan dari aplikasi lama (berisi ID pegawai).
        $idPegawai = $this->request->getPost('provinsi');

        $pegawai = $this->db->table('pegawai')
            ->select('ID, NIK, NAMA')
            ->where('ID', $idPegawai)
            ->get()
            ->getRow();

        if (! $pegawai) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Pegawai tidak ditemukan']);
        }

        return $this->response->setJSON([
            'status' => 'success',
            'data'   => view('persuratan/form/surat_keluar', ['pegawai' => $pegawai]),
        ]);
    }

    /**
     * Pratinjau format surat dari master_persuratan.TEMPLATE.
     *
     * Tidak ada tabel/endpoint penyimpanan surat keluar di aplikasi lama
     * maupun di sini, jadi yang disediakan hanya pratinjau template.
     */
    public function template_surat()
    {
        if (! $this->request->isAJAX()) {
            return $this->response->setStatusCode(403)->setJSON(['status' => 'error', 'message' => 'Akses ditolak']);
        }

        $id = $this->request->getPost('id');

        $surat = $this->db->table('master_persuratan')
            ->select('ID, JENIS_SURAT, TEMPLATE')
            ->where('ID', $id)
            ->get()
            ->getRow();

        if (! $surat) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Jenis surat tidak ditemukan']);
        }

        if (trim((string) $surat->TEMPLATE) === '') {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Template untuk jenis surat ini belum tersedia',
            ]);
        }

        return $this->response->setJSON([
            'status'  => 'success',
            'message' => 'Pratinjau format surat',
            'data'    => $surat->TEMPLATE,
        ]);
    }
}
