<?php

namespace App\Controllers\E_Kas\Pemasukan;

use App\Controllers\BaseController;
use App\Models\PenerimaanKas;

/**
 * Penerimaan Kas Air.
 *
 * Rekap uang tagihan air yang sudah dibayar (t_tagihan.tgl_bayar) per unit
 * penagihan (m_pelanggan.id_kec) dan per jenis pelanggan
 * (m_jenis_pelanggan), lalu disimpan ke `penerimaan_kas`.
 *
 * Di aplikasi lama (Kas_masuk::getLPPAir / simpan_penerimaan) query-nya
 * memakai prefix `dbpdam.` dan GROUP BY tanpa agregasi kolom lain, sehingga
 * error di MySQL dengan ONLY_FULL_GROUP_BY. Di sini kolom non-agregat
 * dibungkus agregat dan prefix dihapus.
 */
class PenerimaanKasAir extends BaseController
{
    var $title;

    public function __construct()
    {
        $this->title = 'Penerimaan Kas Air';
    }

    public function index()
    {
        return view('e-kas/pemasukan/penerimaan_kas_air/main', ['title' => $this->title]);
    }

    /**
     * Tarik data billing (AJAX): rekap penerimaan per jenis pelanggan.
     */
    public function tampil()
    {
        if (! $this->request->isAJAX()) {
            return $this->response->setStatusCode(403)->setJSON([
                'status'  => false,
                'message' => 'Permintaan harus melalui AJAX',
            ]);
        }

        $tglawal  = (string) $this->request->getPost('tglawal');
        $tglakhir = (string) $this->request->getPost('tglakhir');
        $unit     = (int) $this->request->getPost('unit');

        if (! $this->tanggalValid($tglawal) || ! $this->tanggalValid($tglakhir)) {
            return $this->response->setJSON([
                'status'  => false,
                'message' => 'Tanggal penagihan awal dan akhir wajib diisi dengan benar.',
            ]);
        }

        if ($tglakhir < $tglawal) {
            return $this->response->setJSON([
                'status'  => false,
                'message' => 'Tanggal akhir tidak boleh lebih kecil dari tanggal awal.',
            ]);
        }

        $unitRow = $this->db->table('t_kecamatan')
            ->select('id_kec, kecamatan')
            ->where('id_kec', $unit)
            ->get()
            ->getRow();

        if ($unitRow === null) {
            return $this->response->setJSON([
                'status'  => false,
                'message' => 'Unit penagihan tidak ditemukan.',
            ]);
        }

        // tgl_bayar bertipe DATETIME; pakai rentang setengah terbuka supaya
        // transaksi pada tanggal akhir (jam berapa pun) tetap ikut.
        $batasAkhir = date('Y-m-d', strtotime($tglakhir . ' +1 day'));

        $builder = $this->db->table('t_tagihan t');
        $builder->select('t.id_jenis AS id_jenis');
        $builder->select('MAX(j.jenis_pelanggan) AS jenis_pelanggan');
        $builder->select('MAX(j.id_jasa) AS id_kelompok');
        $builder->select('MAX(p.id_kec) AS id_kec');
        $builder->select('SUM(t.bayar) AS jumlah');
        $builder->select('COUNT(DISTINCT t.id_pelanggan) AS jml_pelanggan');
        $builder->select('MAX(t.tgl_bayar) AS tgl_terakhir');
        $builder->join('m_jenis_pelanggan j', 'j.id_jenis = t.id_jenis', 'left');
        $builder->join('m_pelanggan p', 'p.id_pelanggan = t.id_pelanggan', 'inner');
        $builder->where('p.id_kec', $unit);
        $builder->where('t.tgl_bayar >=', $tglawal . ' 00:00:00');
        $builder->where('t.tgl_bayar <', $batasAkhir);
        $builder->groupBy('t.id_jenis');
        $builder->orderBy('t.id_jenis', 'ASC');

        $rows  = [];
        $total = 0;

        foreach ($builder->get()->getResultArray() as $row) {
            $jumlah = (int) $row['jumlah'];
            $total += $jumlah;

            $rows[] = [
                'id_jenis'        => (int) $row['id_jenis'],
                'jenis_pelanggan' => $row['jenis_pelanggan'] ?? '(tidak diketahui)',
                'id_kelompok'     => (int) $row['id_kelompok'],
                'id_kec'          => (int) $row['id_kec'],
                'jumlah'          => $jumlah,
                'jml_pelanggan'   => (int) $row['jml_pelanggan'],
                'tgl_terakhir'    => $row['tgl_terakhir'],
            ];
        }

        if ($rows === []) {
            return $this->response->setJSON([
                'status'  => false,
                'message' => 'Tidak ada pembayaran tagihan pada periode dan unit tersebut.',
            ]);
        }

        return $this->response->setJSON([
            'status'  => true,
            'info'    => [
                'unit'     => $unitRow->kecamatan,
                'tglawal'  => $tglawal,
                'tglakhir' => $tglakhir,
            ],
            'rows'    => $rows,
            'total'   => $total,
        ]);
    }

    /**
     * Simpan hasil tarik data billing ke `penerimaan_kas`.
     */
    public function simpan()
    {
        if (! $this->request->isAJAX()) {
            return $this->response->setStatusCode(403)->setJSON([
                'status'  => false,
                'message' => 'Permintaan harus melalui AJAX',
            ]);
        }

        $tglPenerimaan = (string) $this->request->getPost('tgl_penerimaan');
        $keterangan    = trim((string) $this->request->getPost('keterangan'));
        $nomorBukti    = trim((string) $this->request->getPost('nomor_bukti'));

        $idJenis    = (array) ($this->request->getPost('id_jenis') ?? []);
        $idKelompok = (array) ($this->request->getPost('id_kelompok') ?? []);
        $idKec      = (array) ($this->request->getPost('id_kec') ?? []);
        $jumlah     = (array) ($this->request->getPost('jumlah') ?? []);

        if (! $this->tanggalValid($tglPenerimaan)) {
            return $this->response->setJSON([
                'status'  => false,
                'message' => 'Tanggal penerimaan wajib diisi dengan benar.',
            ]);
        }

        if (mb_strlen($nomorBukti) > 50) {
            return $this->response->setJSON([
                'status'  => false,
                'message' => 'Nomor bukti maksimal 50 karakter.',
            ]);
        }

        $jumlahBaris = count($idJenis);

        if ($jumlahBaris === 0 || $jumlahBaris !== count($idKelompok) || $jumlahBaris !== count($idKec) || $jumlahBaris !== count($jumlah)) {
            return $this->response->setJSON([
                'status'  => false,
                'message' => 'Data penerimaan tidak lengkap, silakan tarik ulang data billing.',
            ]);
        }

        if ($keterangan === '') {
            $keterangan = 'Penerimaan Air';
        }

        // Hanya jenis pelanggan & unit yang benar-benar ada yang boleh masuk.
        $jenisSah = array_map('intval', array_column(
            $this->db->table('m_jenis_pelanggan')->select('id_jenis')->get()->getResultArray(),
            'id_jenis'
        ));
        $unitSah = array_map('intval', array_column(
            $this->db->table('t_kecamatan')->select('id_kec')->get()->getResultArray(),
            'id_kec'
        ));

        $data = [];

        for ($i = 0; $i < $jumlahBaris; $i++) {
            if (! in_array((int) $idJenis[$i], $jenisSah, true) || ! in_array((int) $idKec[$i], $unitSah, true)) {
                return $this->response->setJSON([
                    'status'  => false,
                    'message' => 'Data jenis pelanggan atau unit tidak valid. Silakan tarik ulang data billing.',
                ]);
            }

            $nilai = (int) preg_replace('/[^0-9]/', '', (string) $jumlah[$i]);

            if ($nilai <= 0) {
                continue;
            }

            if ($nilai > 2147483647) {
                return $this->response->setJSON([
                    'status'  => false,
                    'message' => 'Nominal penerimaan melebihi batas yang dapat disimpan.',
                ]);
            }

            $data[] = [
                'nomor_bukti'   => $nomorBukti,
                'tgl_penerimaan' => $tglPenerimaan,
                'id_kec'        => (int) $idKec[$i],
                'id_kelompok'   => (int) $idKelompok[$i],
                'id_jenis'      => (int) $idJenis[$i],
                'jumlah'        => $nilai,
                'keterangan'    => mb_substr($keterangan, 0, 150),
            ];
        }

        if ($data === []) {
            return $this->response->setJSON([
                'status'  => false,
                'message' => 'Tidak ada nominal penerimaan yang bisa disimpan.',
            ]);
        }

        $model = new PenerimaanKas();

        // Nomor bukti kosong -> dibuat otomatis di dalam transaksi simpan.
        $hasil = $model->simpanBatch($data, $nomorBukti === '' ? 'PA' : null);

        if ($hasil['tersimpan'] === 0) {
            return $this->response->setJSON([
                'status'  => false,
                'message' => 'Gagal menyimpan penerimaan kas, silakan coba lagi.',
            ]);
        }

        return $this->response->setJSON([
            'status'  => true,
            'message' => $hasil['tersimpan'] . ' baris penerimaan tanggal ' . $tglPenerimaan . ' berhasil disimpan dengan nomor bukti ' . $hasil['nomor_bukti'] . '.',
        ]);
    }

    private function tanggalValid(string $tanggal): bool
    {
        if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggal)) {
            return false;
        }

        [$tahun, $bulan, $hari] = array_map('intval', explode('-', $tanggal));

        return checkdate($bulan, $hari, $tahun);
    }
}
