<?php

namespace App\Controllers\Billing;

use App\Controllers\BaseController;

/**
 * Data yang dibutuhkan modul Tagihan: pencarian pelanggan, tarif yang
 * berlaku, dan data meter terakhir. Dipisah dari Tagihan supaya controller
 * utamanya fokus ke aksi (simpan pemakaian, pembayaran, dll).
 */
class Tagihan_data extends BaseController
{
    /**
     * Autocomplete pelanggan: nomor sambungan atau nama.
     */
    public function pelanggan()
    {
        $cari = trim((string) $this->request->getVar('search'));

        $builder = $this->db->table('m_pelanggan p')
            ->select('
                p.id_pelanggan,
                p.no_sambungan,
                p.nama_pelanggan,
                p.status_aktif,
                t_kecamatan.kecamatan,
                t_kelurahan.kelurahan
            ')
            ->join('t_kecamatan', 'p.id_kec = t_kecamatan.id_kec', 'left')
            ->join('t_kelurahan', 'p.id_kelurahan = t_kelurahan.id_kelurahan', 'left')
            ->orderBy('p.no_sambungan', 'ASC')
            ->limit(25);

        if ($cari !== '') {
            $builder->groupStart()
                ->like('p.no_sambungan', $cari)
                ->orLike('p.nama_pelanggan', $cari)
                ->groupEnd();
        }

        $list = [];

        foreach ($builder->get()->getResultArray() as $row) {
            $list[] = [
                'id'   => $row['id_pelanggan'],
                'text' => $row['no_sambungan'] . ' | ' . $row['nama_pelanggan']
                    . ($row['kecamatan'] ? ' (' . $row['kecamatan'] . ')' : ''),
            ];
        }

        return $this->response->setJSON($list);
    }

    /**
     * Detail pelanggan + tarif + meter terakhir + status rekening bulan ini.
     */
    public function pelanggan_detail()
    {
        $idPelanggan = (int) $this->request->getVar('id_pelanggan');
        $tanggal     = (string) $this->request->getVar('tanggal');

        if ($idPelanggan <= 0) {
            return $this->response->setJSON([
                'status' => 'error',
                'msg'    => 'Pelanggan tidak valid.',
            ]);
        }

        $pelanggan = $this->db->table('m_pelanggan p')
            ->select('
                p.id_pelanggan,
                p.no_sambungan,
                p.nama_pelanggan,
                p.alamat,
                p.id_kab,
                p.id_kec,
                p.id_kelurahan,
                p.id_jasa,
                p.id_jenis,
                t_kecamatan.kecamatan,
                t_kelurahan.kelurahan,
                t_kabupaten.kabupaten,
                m_jasa.kelompok_jasa,
                m_jasa.tarif_bawah_10,
                m_jasa.tarif_atas_10,
                m_jasa.bea_admin,
                m_jenis_pelanggan.jenis_pelanggan,
                m_jenis_pelanggan.kd_jenis
            ')
            ->join('t_kecamatan', 'p.id_kec = t_kecamatan.id_kec', 'left')
            ->join('t_kelurahan', 'p.id_kelurahan = t_kelurahan.id_kelurahan', 'left')
            ->join('t_kabupaten', 'p.id_kab = t_kabupaten.id_kab', 'left')
            ->join('m_jasa', 'p.id_jasa = m_jasa.id_jasa', 'left')
            ->join('m_jenis_pelanggan', 'p.id_jenis = m_jenis_pelanggan.id_jenis', 'left')
            ->where('p.id_pelanggan', $idPelanggan)
            ->get()
            ->getRowArray();

        if (! $pelanggan) {
            return $this->response->setJSON([
                'status' => 'error',
                'msg'    => 'Pelanggan tidak ditemukan.',
            ]);
        }

        // Bea admin punya pengecualian: Hankam (id_jenis 13) selalu 35000.
        $beaAdmin = (int) $pelanggan['id_jenis'] === 13
            ? 35000
            : (float) $pelanggan['bea_admin'];

        $meterTerakhir = $this->db->table('t_tagihan')
            ->select('bulan_tagihan, pemakaian_air')
            ->where('id_pelanggan', $idPelanggan)
            ->orderBy('bulan_tagihan', 'DESC')
            ->limit(1)
            ->get()
            ->getRowArray();

        // Sudah dientri untuk periode yang dipilih?
        $sudahAda = false;

        if ($tanggal !== '') {
            $ts = strtotime($tanggal);

            if ($ts !== false) {
                $sudahAda = $this->db->table('t_tagihan')
                    ->where('id_pelanggan', $idPelanggan)
                    ->where('MONTH(bulan_tagihan)', (int) date('n', $ts), false)
                    ->where('YEAR(bulan_tagihan)', (int) date('Y', $ts), false)
                    ->countAllResults() > 0;
            }
        }

        return $this->response->setJSON([
            'status'        => 'success',
            'pelanggan'     => $pelanggan,
            'bea_admin'     => $beaAdmin,
            'meter_terakhir' => $meterTerakhir,
            'sudah_ada'     => $sudahAda,
        ]);
    }

    /**
     * Kelurahan untuk dropdown (autoRoute dari view).
     */
    public function kelurahan()
    {
        $builder = $this->db->table('t_kelurahan k')
            ->select('k.id_kelurahan, k.kelurahan, c.kecamatan')
            ->join('t_kecamatan c', 'c.id_kec = k.id_kec', 'left')
            ->orderBy('c.kecamatan', 'ASC')
            ->orderBy('k.kelurahan', 'ASC');

        $list = [];

        foreach ($builder->get()->getResultArray() as $row) {
            $list[] = [
                'id'   => $row['id_kelurahan'],
                'text' => ($row['kecamatan'] ? '[' . $row['kecamatan'] . '] ' : '') . $row['kelurahan'],
            ];
        }

        return $this->response->setJSON($list);
    }
}
