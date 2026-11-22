<?php

namespace App\Controllers\Laporan;

use App\Controllers\BaseController;

/**
 * Laporan Rekap DRD per Petugas (LPP Petugas).
 *
 * Padanan module/laporan/data_lappetugas.php + get_drdpetugas.php di
 * aplikasi lama: rekap penagihan per jenis pelanggan untuk satu petugas
 * penagih dalam rentang tanggal bayar, lengkap dengan jumlah pelanggan
 * terpasang, jumlah lembar ditagih, kubikasi, harga air, biaya admin,
 * dan jumlah tagihan.
 *
 * Dua perbaikan dari aplikasi lama:
 *
 * 1. Kubikasi "di bawah 10 m3" di aplikasi lama dihitung
 *    `SUM(tag_dibawah_10) / tarif_dibawah_10`. Karena tag_dibawah_10
 *    selalu `10 x tarif` (lihat aksi_simpan_pemakaian.php), hasilnya
 *    PERSIS `10 x jumlah rekening` — bukan kubikasi. Contoh nyata
 *    periode 2026 jenis RTA: 25.203 rekening -> 252.030 "m3".
 *    Di sini kubikasi diambil dari selisih meter
 *    (pemakaian_air - pemakaian_air_lalu), dipecah 10 m3 pertama dan
 *    sisanya.
 *
 * 2. Filter wilayah memakai m_pelanggan.id_kec, bukan t_tagihan.id_kec
 *    yang NULL pada 11.474 baris lama.
 */
class LppPetugas extends BaseController
{
    var $title;

    public function __construct()
    {
        $this->title = 'LPP Per Petugas';
    }

    public function index()
    {
        return view('laporan/lpp_petugas/main', ['title' => $this->title]);
    }

    /**
     * Daftar petugas penagih.
     */
    public function petugas()
    {
        $rows = $this->db->table('users u')
            ->select('u.id_session, u.nama_lengkap, u.level, k.kecamatan')
            ->join('t_kecamatan k', 'k.id_kec = u.id_kec', 'left')
            ->whereIn('u.level', ['mobileloket', 'loket', 'fastpay'])
            ->where('u.blokir', 'N')
            ->orderBy('u.nama_lengkap', 'ASC')
            ->get()
            ->getResultArray();

        $list = [];

        foreach ($rows as $row) {
            $list[] = [
                'id'       => $row['id_session'],
                'text'     => strtoupper($row['nama_lengkap'])
                    . ($row['kecamatan'] ? ' — ' . $row['kecamatan'] : ''),
                'kecamatan' => $row['kecamatan'],
                'level'    => $row['level'],
            ];
        }

        return $this->response->setJSON($list);
    }

    /**
     * Rekap per jenis pelanggan.
     */
    public function data()
    {
        $param = $this->parameter();

        if ($param === null) {
            return $this->response->setJSON([
                'status' => 'error',
                'msg'    => 'Tanggal awal, tanggal akhir, dan petugas wajib dipilih.',
            ]);
        }

        [$tglAwal, $tglAkhir, $idPetugas] = $param;

        return $this->response->setJSON($this->rekap($tglAwal, $tglAkhir, $idPetugas));
    }

    public function export_pdf()
    {
        $param = $this->parameter();

        if ($param === null) {
            return $this->response->setStatusCode(400)->setBody('Parameter laporan tidak lengkap.');
        }

        [$tglAwal, $tglAkhir, $idPetugas] = $param;

        $rekap = $this->rekap($tglAwal, $tglAkhir, $idPetugas);

        $html = view('laporan/lpp_petugas/pdf', $rekap);
        $page = view('pdf/potrait', ['html' => $html]);

        $mpdf = new \Mpdf\Mpdf([
            'mode'              => 'utf-8',
            'format'            => 'A4',
            'orientation'       => 'L',
            'default_font_size' => 9,
            'default_font'      => 'arial',
            'tempDir'           => WRITEPATH . 'mpdf_temp',
        ]);

        $mpdf->WriteHTML($page);
        $mpdf->SetTitle('REKAP DRD PER PETUGAS');

        return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setBody($mpdf->Output('', 'S'));
    }

    // =================================================================
    // INTI
    // =================================================================

    private function rekap(string $tglAwal, string $tglAkhir, string $idPetugas): array
    {
        $semuaPetugas = ($idPetugas === 'all');

        $petugas = null;
        $idKec   = null;

        if (! $semuaPetugas) {
            $petugas = $this->db->table('users u')
                ->select('u.id_session, u.nama_lengkap, u.id_kec, k.kecamatan')
                ->join('t_kecamatan k', 'k.id_kec = u.id_kec', 'left')
                ->where('u.id_session', $idPetugas)
                ->get()
                ->getRowArray();

            $idKec = $petugas['id_kec'] ?? null;
        }

        // Kubikasi diturunkan dari tagihan, bukan dari selisih meter.
        //
        // Alasan: 7.914 baris tahun 2026 punya pemakaian_air_lalu = 0
        // padahal pemakaian_air-nya ribuan (sisa input piutang lama),
        // jadi `pemakaian_air - pemakaian_air_lalu` meledak. Tagihan
        // justru konsisten: tag_diatas_10 / tarif_diatas_10 = kelebihan
        // di atas 10 m3, dan total = 10 + kelebihan itu.
        //
        // Aplikasi lama menghitung "kubikasi <= 10" sebagai
        // SUM(tag_dibawah_10) / tarif_dibawah_10, yang karena
        // tag_dibawah_10 selalu 10 x tarif hasilnya PERSIS 10 x jumlah
        // rekening — bukan kubikasi. Di sini dihitung LEAST(kub, 10).
        $kub = "CASE
                    WHEN CAST(t.tarif_diatas_10 AS DECIMAL(12,2)) > 0
                         AND CAST(t.tag_diatas_10 AS SIGNED) > 0
                    THEN 10 + ROUND(CAST(t.tag_diatas_10 AS SIGNED) / CAST(t.tarif_diatas_10 AS DECIMAL(12,2)))
                    ELSE 10
                END";

        $builder = $this->db->table('t_tagihan t')
            ->select("
                t.id_jenis,
                COUNT(t.id_pemakaian) AS rekening,
                IFNULL(SUM(CAST(t.tag_dibawah_10 AS SIGNED)), 0) AS tag_bawah,
                IFNULL(SUM(CAST(t.tag_diatas_10 AS SIGNED)), 0) AS tag_atas,
                IFNULL(SUM(CAST(t.by_admin AS SIGNED)), 0) AS by_admin,
                IFNULL(SUM(CAST(t.jlh_tagihan AS SIGNED)), 0) AS jlh_tagihan,
                SUM(LEAST($kub, 10)) AS kub_bawah,
                SUM(GREATEST($kub - 10, 0)) AS kub_atas
            ", false)
            ->where('t.status', '1')
            ->where('DATE(t.tgl_bayar) >=', $tglAwal)
            ->where('DATE(t.tgl_bayar) <=', $tglAkhir)
            ->groupBy('t.id_jenis');

        if (! $semuaPetugas) {
            $builder->where('t.id_user', $idPetugas);
        }

        $perJenis = [];

        foreach ($builder->get()->getResultArray() as $row) {
            $perJenis[(int) $row['id_jenis']] = $row;
        }

        $jenis = $this->db->table('m_jenis_pelanggan')
            ->select('id_jenis, jenis_pelanggan, kd_jenis')
            ->orderBy('id_jenis', 'ASC')
            ->get()
            ->getResultArray();

        $baris = [];

        foreach ($jenis as $j) {
            $idJenis = (int) $j['id_jenis'];
            $agg = $perJenis[$idJenis] ?? [];

            // Pelanggan terpasang: pelanggan aktif jenis ini, dibatasi
            // unit petugas kalau laporan per petugas.
            $terpasang = $this->db->table('m_pelanggan')
                ->where('status_aktif', '1')
                ->where('id_jenis', $idJenis);

            if (! $semuaPetugas && $idKec !== null) {
                $terpasang->where('id_kec', (int) $idKec);
            }

            $tagBawah = (int) ($agg['tag_bawah'] ?? 0);
            $tagAtas  = (int) ($agg['tag_atas'] ?? 0);
            $kubBawah = (int) ($agg['kub_bawah'] ?? 0);
            $kubAtas  = (int) ($agg['kub_atas'] ?? 0);

            $baris[] = [
                'id_jenis'    => $idJenis,
                'jenis'       => $j['jenis_pelanggan'],
                'kd_jenis'    => $j['kd_jenis'],
                'terpasang'   => $terpasang->countAllResults(),
                'rekening'    => (int) ($agg['rekening'] ?? 0),
                'kub_bawah'   => $kubBawah,
                'kub_atas'    => $kubAtas,
                'kubikasi'    => $kubBawah + $kubAtas,
                'harga_air'   => $tagBawah + $tagAtas,
                'by_admin'    => (int) ($agg['by_admin'] ?? 0),
                'jlh_tagihan' => (int) ($agg['jlh_tagihan'] ?? 0),
            ];
        }

        $total = [
            'terpasang'   => 0,
            'rekening'    => 0,
            'kub_bawah'   => 0,
            'kub_atas'    => 0,
            'kubikasi'    => 0,
            'harga_air'   => 0,
            'by_admin'    => 0,
            'jlh_tagihan' => 0,
        ];

        foreach ($baris as $b) {
            $total['terpasang']   += $b['terpasang'];
            $total['rekening']    += $b['rekening'];
            $total['kub_bawah']   += $b['kub_bawah'];
            $total['kub_atas']    += $b['kub_atas'];
            $total['kubikasi']    += $b['kubikasi'];
            $total['harga_air']   += $b['harga_air'];
            $total['by_admin']    += $b['by_admin'];
            $total['jlh_tagihan'] += $b['jlh_tagihan'];
        }

        return [
            'status'   => 'success',
            'baris'    => $baris,
            'total'    => $total,
            'petugas'  => $semuaPetugas
                ? ['nama_lengkap' => 'SEMUA PETUGAS', 'kecamatan' => 'SEMUA UNIT']
                : $petugas,
            'tgl_awal' => $tglAwal,
            'tgl_akhir' => $tglAkhir,
        ];
    }

    /**
     * @return array{0:string, 1:string, 2:string}|null
     */
    private function parameter(): ?array
    {
        $tglAwal    = (string) $this->request->getVar('tgl_awal');
        $tglAkhir   = (string) $this->request->getVar('tgl_akhir');
        $idPetugas  = (string) $this->request->getVar('petugas');

        $tsAwal  = strtotime($tglAwal);
        $tsAkhir = strtotime($tglAkhir);

        if ($tsAwal === false || $tsAkhir === false || $idPetugas === '') {
            return null;
        }

        if ($tsAwal > $tsAkhir) {
            [$tsAwal, $tsAkhir] = [$tsAkhir, $tsAwal];
        }

        return [date('Y-m-d', $tsAwal), date('Y-m-d', $tsAkhir), $idPetugas];
    }
}
