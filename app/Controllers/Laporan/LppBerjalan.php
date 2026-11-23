<?php

namespace App\Controllers\Laporan;

use App\Controllers\BaseController;

/**
 * LPP Air Periodik (Laporan Penerimaan Penagih) per kelurahan.
 *
 * Padanan module/laporan/lppberjalan.php + get_lppberjalan.php di
 * aplikasi lama. Isinya daftar tagihan LUNAS pada rentang bulan terpilih
 * untuk satu kelurahan, lengkap dengan harga air, biaya admin, jumlah
 * tagihan, dan pemakaian air.
 *
 * Kubikasi memakai aturan yang sama dengan DrdAir::getPemakaian():
 *   - tag_diatas_10 > 0  -> 10 + tag_diatas_10 / tarif_diatas_10
 *   - selain itu         -> selisih meter, dibatasi 0..10
 *
 * Jangan pakai `pemakaian_air - pemakaian_air_lalu` mentah: 7.914 baris
 * tahun 2026 punya pemakaian_air_lalu = 0 padahal pemakaian_air-nya
 * ribuan, sehingga hasilnya meledak. Kedua kolom itu juga varchar, jadi
 * pengurangan string di PHP 8 melempar TypeError.
 */
class LppBerjalan extends BaseController
{
    var $title;

    public function __construct()
    {
        $this->title = 'LPP Air Periodik';
    }

    public function index()
    {
        return view('laporan/lpp_berjalan/main', ['title' => $this->title]);
    }

    /**
     * Daftar kelurahan untuk dropdown.
     */
    public function kelurahan()
    {
        $list = [];

        $rows = $this->db->table('t_kelurahan k')
            ->select('k.id_kelurahan, k.kelurahan, c.kecamatan')
            ->join('t_kecamatan c', 'c.id_kec = k.id_kec', 'left')
            ->orderBy('c.kecamatan', 'ASC')
            ->orderBy('k.kelurahan', 'ASC')
            ->get()
            ->getResultArray();

        foreach ($rows as $row) {
            $list[] = [
                'id'   => $row['id_kelurahan'],
                'text' => ($row['kecamatan'] ? '[' . $row['kecamatan'] . '] ' : '') . $row['kelurahan'],
            ];
        }

        return $this->response->setJSON($list);
    }

    /**
     * Data LPP untuk tabel & PDF.
     */
    public function data()
    {
        $param = $this->parameter();

        if ($param === null) {
            return $this->response->setJSON([
                'status' => 'error',
                'msg'    => 'Tahun, bulan awal, bulan akhir, dan kelurahan wajib dipilih dengan benar.',
            ]);
        }

        [$tahun, $bulanAwal, $bulanAkhir, $idKelurahan] = $param;

        $rows = $this->ambilData($tahun, $bulanAwal, $bulanAkhir, $idKelurahan);

        $total = [
            'harga_air'     => 0,
            'by_admin'      => 0,
            'jlh_tagihan'   => 0,
            'pemakaian_air' => 0,
        ];

        foreach ($rows as $i => $r) {
            $rows[$i]['no']            = $i + 1;
            $rows[$i]['harga_air']     = (float) $r['tag_dibawah_10'] + (float) $r['tag_diatas_10'];
            $rows[$i]['pemakaian']     = $this->kubikasi($r);
            $rows[$i]['bulan_text']    = $this->namaBulan((int) date('n', strtotime($r['bulan_tagihan'])))
                . ' ' . date('Y', strtotime($r['bulan_tagihan']));

            $total['harga_air']     += $rows[$i]['harga_air'];
            $total['by_admin']      += (float) $r['by_admin'];
            $total['jlh_tagihan']   += (float) $r['jlh_tagihan'];
            $total['pemakaian_air'] += $rows[$i]['pemakaian'];
        }

        $kelurahan = $this->db->table('t_kelurahan k')
            ->select('k.kelurahan, c.kecamatan')
            ->join('t_kecamatan c', 'c.id_kec = k.id_kec', 'left')
            ->where('k.id_kelurahan', $idKelurahan)
            ->get()
            ->getRowArray();

        return $this->response->setJSON([
            'status'    => 'success',
            'rows'      => $rows,
            'total'     => $total,
            'kelurahan' => $kelurahan,
            'periode'   => $this->namaBulan($bulanAwal) . ' s/d ' . $this->namaBulan($bulanAkhir) . ' ' . $tahun,
        ]);
    }

    public function export_pdf()
    {
        $param = $this->parameter();

        if ($param === null) {
            return $this->response->setStatusCode(400)->setBody('Parameter laporan tidak lengkap.');
        }

        [$tahun, $bulanAwal, $bulanAkhir, $idKelurahan] = $param;

        $rows = $this->ambilData($tahun, $bulanAwal, $bulanAkhir, $idKelurahan);

        $total = ['harga_air' => 0, 'by_admin' => 0, 'jlh_tagihan' => 0, 'pemakaian' => 0];
        $baris = [];

        foreach ($rows as $i => $r) {
            $hargaAir  = (float) $r['tag_dibawah_10'] + (float) $r['tag_diatas_10'];
            $pemakaian = $this->kubikasi($r);

            $baris[] = [
                'no'         => $i + 1,
                'sambungan'  => $r['no_sambungan'] ?? '-',
                'nama'       => $r['nama_pelanggan'] ?? '-',
                'rek'        => $r['no_rek'] ?? '-',
                'bulan'      => $this->namaBulan((int) date('n', strtotime($r['bulan_tagihan'])))
                    . ' ' . date('Y', strtotime($r['bulan_tagihan'])),
                'harga_air'  => $hargaAir,
                'by_admin'   => (float) $r['by_admin'],
                'jlh_tagihan' => (float) $r['jlh_tagihan'],
                'pemakaian'  => $pemakaian,
            ];

            $total['harga_air']   += $hargaAir;
            $total['by_admin']    += (float) $r['by_admin'];
            $total['jlh_tagihan'] += (float) $r['jlh_tagihan'];
            $total['pemakaian']   += $pemakaian;
        }

        $kelurahan = $this->db->table('t_kelurahan k')
            ->select('k.kelurahan, k.kode_kelurahan, c.kecamatan')
            ->join('t_kecamatan c', 'c.id_kec = k.id_kec', 'left')
            ->where('k.id_kelurahan', $idKelurahan)
            ->get()
            ->getRowArray();

        $mpdf = new \Mpdf\Mpdf([
            'mode'              => 'utf-8',
            'format'            => 'A4',
            'orientation'       => 'P',
            'default_font_size' => 9,
            'default_font'      => 'arial',
            'tempDir'           => WRITEPATH . 'mpdf_temp',
        ]);

        // Kop ditulis sekali, baris menyusul per potongan: satu
        // WriteHTML dengan 2.500+ baris melewati pcre.backtrack_limit.
        $mpdf->WriteHTML(view('laporan/lpp_berjalan/pdf', [
            'baris_awal' => true,
            'baris'      => [],
            'total'      => $total,
            'kelurahan'  => $kelurahan,
            'tahun'      => $tahun,
            'periode'    => $this->namaBulan($bulanAwal) . ' S/D ' . $this->namaBulan($bulanAkhir) . ' ' . $tahun,
        ]));

        $potongan = array_chunk($baris, 200);
        $akhir    = count($potongan) - 1;

        foreach ($potongan as $i => $chunk) {
            $mpdf->WriteHTML(view('laporan/lpp_berjalan/pdf', [
                'baris_awal'  => false,
                'baris'       => $chunk,
                'total'       => $total,
                'kelurahan'   => $kelurahan,
                'tahun'       => $tahun,
                'periode'     => '',
                'baris_total' => $i === $akhir,
            ]));
        }

        $mpdf->SetTitle('LPP AIR PERIODIK');

        return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setBody($mpdf->Output('', 'S'));
    }

    // =================================================================
    // BANTUAN
    // =================================================================

    /**
     * @return array{0:int, 1:int, 2:int, 3:int}|null
     */
    private function parameter(): ?array
    {
        $tahun       = $this->request->getVar('tahun');
        $bulanAwal   = $this->request->getVar('bulan_awal');
        $bulanAkhir  = $this->request->getVar('bulan_akhir');
        $idKelurahan = $this->request->getVar('kelurahan');

        if (! is_numeric($tahun) || ! is_numeric($bulanAwal) || ! is_numeric($bulanAkhir) || ! is_numeric($idKelurahan)) {
            return null;
        }

        $tahun       = (int) $tahun;
        $bulanAwal   = (int) $bulanAwal;
        $bulanAkhir  = (int) $bulanAkhir;
        $idKelurahan = (int) $idKelurahan;

        if ($tahun < 2000 || $bulanAwal < 1 || $bulanAwal > 12 || $bulanAkhir < 1 || $bulanAkhir > 12 || $idKelurahan <= 0) {
            return null;
        }

        if ($bulanAwal > $bulanAkhir) {
            [$bulanAwal, $bulanAkhir] = [$bulanAkhir, $bulanAwal];
        }

        return [$tahun, $bulanAwal, $bulanAkhir, $idKelurahan];
    }

    /**
     * Tagihan lunas pada rentang bulan, satu kelurahan.
     */
    private function ambilData(int $tahun, int $bulanAwal, int $bulanAkhir, int $idKelurahan): array
    {
        return $this->db->table('t_tagihan t')
            ->select('
                t.id_pemakaian,
                t.no_rek,
                t.bulan_tagihan,
                t.pemakaian_air,
                t.pemakaian_air_lalu,
                t.tag_dibawah_10,
                t.tag_diatas_10,
                t.tarif_diatas_10,
                t.by_admin,
                t.jlh_tagihan,
                t.bayar,
                t.tgl_bayar,
                p.no_sambungan,
                p.nama_pelanggan,
                j.kd_jenis
            ')
            ->join('m_pelanggan p', 'p.id_pelanggan = t.id_pelanggan')
            ->join('m_jenis_pelanggan j', 'j.id_jenis = t.id_jenis', 'left')
            ->where('t.status', '1')
            ->where('YEAR(t.bulan_tagihan)', $tahun, false)
            ->where('MONTH(t.bulan_tagihan) >=', $bulanAwal, false)
            ->where('MONTH(t.bulan_tagihan) <=', $bulanAkhir, false)
            // Wilayah dari m_pelanggan: t_tagihan.id_kelurahan bisa basi,
            // dan 11.474 baris lama punya id_kec NULL.
            ->where('p.id_kelurahan', $idKelurahan)
            ->orderBy('t.bulan_tagihan', 'ASC')
            ->orderBy('p.no_sambungan', 'ASC')
            ->get()
            ->getResultArray();
    }

    /**
     * Kubikasi satu baris. Lihat penjelasan di docblock kelas.
     */
    private function kubikasi(array $r): int
    {
        $tarifAtas = (float) ($r['tarif_diatas_10'] ?? 0);
        $tagAtas   = (float) ($r['tag_diatas_10'] ?? 0);

        if ($tarifAtas > 0 && $tagAtas > 0) {
            return (int) round(10 + $tagAtas / $tarifAtas);
        }

        $selisih = (float) ($r['pemakaian_air'] ?? 0) - (float) ($r['pemakaian_air_lalu'] ?? 0);

        return (int) max(0, min(10, $selisih));
    }

    private function namaBulan(int $bulan): string
    {
        $nama = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
                 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

        return $nama[$bulan] ?? '-';
    }
}
