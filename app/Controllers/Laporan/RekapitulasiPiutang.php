<?php

namespace App\Controllers\Laporan;

use App\Controllers\BaseController;

/**
 * Rekapitulasi Piutang tahunan.
 *
 * Dua mode:
 *   - all     : piutang & pembayaran per kecamatan per tahun
 *   - perUnit : piutang per kecamatan x jenis pelanggan per tahun
 *
 * Sebelumnya file ini mendeklarasikan function global `getTagihanData()`
 * di dalam dua method. Selain bentrok kalau keduanya dipanggil (fatal
 * "cannot redeclare"), versi di export_pdf() memakai `$this->db` dari
 * dalam fungsi global sehingga meledak dengan "Using $this when not in
 * object context" pada mode `all`. Sekarang datanya diambil lewat satu
 * private method.
 */
class RekapitulasiPiutang extends BaseController
{
    var $title;

    public function __construct()
    {
        $this->title = 'Rekapitulasi Piutang';
    }

    public function index()
    {
        $data['title'] = $this->title;

        return view('/laporan/rekapitulasi_piutang/main', $data);
    }

    public function getPiutang()
    {
        [$tahunAwal, $tahunAkhir] = $this->rentangTahun(
            $this->request->getVar('tahunAwal'),
            $this->request->getVar('tahunAkhir')
        );

        if ($tahunAwal === null) {
            return $this->response->setStatusCode(400)->setJSON([
                'status' => 'error',
                'msg'    => 'Tahun awal dan tahun akhir wajib diisi.',
            ]);
        }

        $jenisLaporan = (string) $this->request->getVar('jenisLaporan');
        $unit         = $this->request->getVar('unit');

        // Kosong = mode rekap semua unit, sama seperti 'all'.
        if ($jenisLaporan === 'all' || $jenisLaporan === '') {
            return $this->response->setJSON(
                $this->rekapPerKecamatan($tahunAwal, $tahunAkhir)
            );
        }

        return $this->response->setJSON(
            $this->rekapPerJenisPelanggan($tahunAwal, $tahunAkhir, $unit)
        );
    }

    public function modal_export_pdf()
    {
        $data['jenisLaporan'] = $this->request->getVar('jenisLaporan');
        $data['tahunAwal']    = $this->request->getVar('tahunAwal');
        $data['tahunAkhir']   = $this->request->getVar('tahunAkhir');
        $data['unit']         = $this->request->getVar('unit');

        $msg = ['html' => view('laporan/rekapitulasi_piutang/modal/pdf', $data)];
        echo json_encode($msg);
    }

    public function export_pdf($jenisLaporan = 'all', $tahunAwal = null, $tahunAkhir = null, $unit = null)
    {
        [$tahunAwal, $tahunAkhir] = $this->rentangTahun($tahunAwal, $tahunAkhir);

        if ($tahunAwal === null) {
            return $this->response->setStatusCode(400)->setBody('Tahun awal dan tahun akhir wajib diisi.');
        }

        $periode = $tahunAwal === $tahunAkhir
            ? (string) $tahunAwal
            : $tahunAwal . ' - ' . $tahunAkhir;

        $html = $jenisLaporan === 'all' || $jenisLaporan === ''
            ? $this->htmlRekapPerKecamatan($tahunAwal, $tahunAkhir, $periode)
            : $this->htmlRekapPerJenisPelanggan($tahunAwal, $tahunAkhir, $unit, $periode);

        $page = view('pdf/potrait', ['html' => $html]);

        $mpdf = new \Mpdf\Mpdf([
            'mode'              => 'utf-8',
            'format'            => 'A4',
            'orientation'       => 'L',
            'default_font_size' => 10,
            'default_font'      => 'arial',
            'tempDir'           => WRITEPATH . 'mpdf_temp',
        ]);

        $mpdf->WriteHTML($page);
        $mpdf->SetTitle('REKAPITULASI PIUTANG');

        return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setBody($mpdf->Output('', 'S'));
    }

    // =================================================================
    // DATA
    // =================================================================

    /**
     * Rekap per kecamatan per tahun (mode 'all').
     *
     * @return array<string, array<int, array{tahun:int, jumlah_bayar:int, jumlah_piutang:int}>>
     */
    private function rekapPerKecamatan(int $tahunAwal, int $tahunAkhir): array
    {
        $tahun = range($tahunAwal, $tahunAkhir);

        $data = [];

        foreach ($this->db->table('t_kecamatan')->get()->getResultArray() as $kec) {
            $data[$kec['kecamatan']] = [];

            foreach ($tahun as $th) {
                $data[$kec['kecamatan']][$th] = [
                    'tahun'          => $th,
                    'jumlah_bayar'   => 0,
                    'jumlah_piutang' => 0,
                ];
            }
        }

        // t_tagihan.id_kec NULL pada 11.474 baris lama, jadi kecamatan
        // diambil lewat m_pelanggan.
        $select = "
            t_kecamatan.kecamatan,
            YEAR(t.bulan_tagihan) AS tahun,
            IFNULL(SUM(t.jlh_tagihan + t.denda), 0) AS tagihan,
            IFNULL(SUM(t.bayar), 0) AS bayar
        ";

        $rows = $this->db->table('t_tagihan t')
            ->select($select)
            ->join('m_pelanggan p', 'p.id_pelanggan = t.id_pelanggan')
            ->join('t_kecamatan', 't_kecamatan.id_kec = p.id_kec')
            ->where('YEAR(t.bulan_tagihan) >=', $tahunAwal)
            ->where('YEAR(t.bulan_tagihan) <=', $tahunAkhir)
            ->groupBy('t_kecamatan.kecamatan, YEAR(t.bulan_tagihan)')
            ->orderBy('t_kecamatan.kecamatan, YEAR(t.bulan_tagihan)', 'ASC')
            ->get()
            ->getResultArray();

        foreach ($rows as $row) {
            $kec = $row['kecamatan'];
            $th  = (int) $row['tahun'];

            if (! isset($data[$kec][$th])) {
                continue;
            }

            $tagihan = (float) $row['tagihan'];
            $bayar   = (float) $row['bayar'];

            $data[$kec][$th]['jumlah_bayar']   = $bayar;
            $data[$kec][$th]['jumlah_piutang'] = max(0, $tagihan - $bayar);
        }

        foreach ($data as $kec => $baris) {
            $data[$kec] = array_values($baris);
        }

        return $data;
    }

    /**
     * Rekap per jenis pelanggan per tahun (mode perUnit).
     */
    private function rekapPerJenisPelanggan(int $tahunAwal, int $tahunAkhir, $unit): array
    {
        $jenis = $this->db->table('m_jenis_pelanggan')
            ->select('jenis_pelanggan')
            ->orderBy('id_jenis', 'ASC')
            ->get()
            ->getResultArray();

        $kecamatan = $this->db->table('t_kecamatan');

        if ($unit !== null && $unit !== '' && $unit !== 'all') {
            $kecamatan->where('id_kec', (int) $unit);
        }

        $kecamatan = $kecamatan->orderBy('kecamatan', 'ASC')->get()->getResultArray();

        $tahun = range($tahunAwal, $tahunAkhir);
        $data  = [];

        foreach ($kecamatan as $kec) {
            $data[$kec['id_kec']] = [
                'nama_kecamatan' => $kec['kecamatan'],
                'data'           => [],
            ];

            foreach ($jenis as $j) {
                foreach ($tahun as $th) {
                    $data[$kec['id_kec']]['data'][$j['jenis_pelanggan']][$th] = [
                        'tahun'          => $th,
                        'jumlah_bayar'   => 0,
                        'jumlah_piutang' => 0,
                    ];
                }
            }
        }

        $rows = $this->db->table('t_tagihan t')
            ->select("
                p.id_kec,
                j.jenis_pelanggan,
                YEAR(t.bulan_tagihan) AS tahun,
                IFNULL(SUM(t.jlh_tagihan + t.denda), 0) AS tagihan,
                IFNULL(SUM(t.bayar), 0) AS bayar
            ")
            ->join('m_pelanggan p', 'p.id_pelanggan = t.id_pelanggan')
            ->join('m_jenis_pelanggan j', 'j.id_jenis = t.id_jenis')
            ->where('YEAR(t.bulan_tagihan) >=', $tahunAwal)
            ->where('YEAR(t.bulan_tagihan) <=', $tahunAkhir)
            ->groupBy('p.id_kec, j.jenis_pelanggan, YEAR(t.bulan_tagihan)')
            ->orderBy('p.id_kec, j.jenis_pelanggan, YEAR(t.bulan_tagihan)', 'ASC')
            ->get()
            ->getResultArray();

        foreach ($rows as $row) {
            $idKec = (int) $row['id_kec'];
            $nama  = $row['jenis_pelanggan'];
            $th    = (int) $row['tahun'];

            if (! isset($data[$idKec]['data'][$nama][$th])) {
                continue;
            }

            $tagihan = (float) $row['tagihan'];
            $bayar   = (float) $row['bayar'];

            $data[$idKec]['data'][$nama][$th]['jumlah_bayar']   = $bayar;
            $data[$idKec]['data'][$nama][$th]['jumlah_piutang'] = max(0, $tagihan - $bayar);
        }

        // Ratakan menjadi satu baris per (kecamatan, tahun) dengan kolom
        // per jenis pelanggan.
        $hasil = [];

        foreach ($data as $kec) {
            $perTahun = [];

            foreach ($kec['data'] as $namaJenis => $perJenis) {
                foreach ($perJenis as $th => $nilai) {
                    if (! isset($perTahun[$th])) {
                        $perTahun[$th] = [
                            'nama_kecamatan' => $kec['nama_kecamatan'],
                            'tahun'          => $th,
                            'kategori'       => [],
                        ];
                    }

                    $perTahun[$th]['kategori'][$namaJenis] = [
                        'jumlah_bayar'   => $nilai['jumlah_bayar'],
                        'jumlah_piutang' => $nilai['jumlah_piutang'],
                    ];
                }
            }

            ksort($perTahun);
            $hasil = array_merge($hasil, array_values($perTahun));
        }

        return $hasil;
    }

    // =================================================================
    // HTML PDF
    // =================================================================

    private function htmlRekapPerKecamatan(int $tahunAwal, int $tahunAkhir, string $periode): string
    {
        $data = $this->rekapPerKecamatan($tahunAwal, $tahunAkhir);

        $html = '<div style="margin-top: 10px;">';
        $html .= "<h3 align='center' style='margin:0;'>REKAPITULASI PIUTANG</h3>";
        $html .= "<p align='center' style='margin:0 0 10px;'><b>PERIODE : " . esc($periode) . "</b></p>";

        $html .= '<div class="table"><table>';
        $html .= '<tr>';
        $html .= '<th width="20%">Kecamatan</th>';
        $html .= '<th width="10%">Tahun</th>';
        $html .= '<th>Piutang</th>';
        $html .= '<th>Telah Dibayar</th>';
        $html .= '<th>Sisa Piutang</th>';
        $html .= '</tr>';

        $totalSemuaPiutang = 0;
        $totalSemuaBayar   = 0;
        $totalSemuaSisa    = 0;

        foreach ($data as $kecamatan => $baris) {
            $subPiutang = 0;
            $subBayar   = 0;
            $subSisa    = 0;
            $awal       = true;

            foreach ($baris as $row) {
                $piutang = $row['jumlah_bayar'] + $row['jumlah_piutang'];

                $html .= '<tr>';
                if ($awal) {
                    $html .= '<td rowspan="' . (count($baris) + 1) . '">' . esc($kecamatan) . '</td>';
                    $awal = false;
                }
                $html .= '<td class="text-center">' . (int) $row['tahun'] . '</td>';
                $html .= '<td class="text-right">Rp. ' . number_format($piutang, 0, ',', '.') . '</td>';
                $html .= '<td class="text-right">Rp. ' . number_format($row['jumlah_bayar'], 0, ',', '.') . '</td>';
                $html .= '<td class="text-right">Rp. ' . number_format($row['jumlah_piutang'], 0, ',', '.') . '</td>';
                $html .= '</tr>';

                $subPiutang += $piutang;
                $subBayar   += $row['jumlah_bayar'];
                $subSisa    += $row['jumlah_piutang'];
            }

            $html .= '<tr style="background-color:#ffb5b5;">';
            $html .= '<td class="text-center"><b>Total</b></td>';
            $html .= '<td class="text-right"><b>Rp. ' . number_format($subPiutang, 0, ',', '.') . '</b></td>';
            $html .= '<td class="text-right"><b>Rp. ' . number_format($subBayar, 0, ',', '.') . '</b></td>';
            $html .= '<td class="text-right"><b>Rp. ' . number_format($subSisa, 0, ',', '.') . '</b></td>';
            $html .= '</tr>';

            $totalSemuaPiutang += $subPiutang;
            $totalSemuaBayar   += $subBayar;
            $totalSemuaSisa    += $subSisa;
        }

        $html .= '<tr>';
        $html .= '<td colspan="2" class="text-center"><b>Total Keseluruhan</b></td>';
        $html .= '<td class="text-right"><b>Rp. ' . number_format($totalSemuaPiutang, 0, ',', '.') . '</b></td>';
        $html .= '<td class="text-right"><b>Rp. ' . number_format($totalSemuaBayar, 0, ',', '.') . '</b></td>';
        $html .= '<td class="text-right"><b>Rp. ' . number_format($totalSemuaSisa, 0, ',', '.') . '</b></td>';
        $html .= '</tr>';

        $html .= '</table></div>';

        return $html;
    }

    private function htmlRekapPerJenisPelanggan(int $tahunAwal, int $tahunAkhir, $unit, string $periode): string
    {
        $data = $this->rekapPerJenisPelanggan($tahunAwal, $tahunAkhir, $unit);

        $namaUnit = 'SEMUA UNIT';

        if ($unit !== null && $unit !== '' && $unit !== 'all') {
            $row = $this->db->table('t_kecamatan')
                ->select('kecamatan')
                ->where('id_kec', (int) $unit)
                ->get()
                ->getRow();

            $namaUnit = $row ? strtoupper($row->kecamatan) : 'UNIT ' . (int) $unit;
        }

        $html = '<div style="margin-top: 10px;">';
        $html .= "<h3 align='center' style='margin:0;'>REKAPITULASI PIUTANG PER JENIS PELANGGAN</h3>";
        $html .= "<p align='center' style='margin:0;'><b>UNIT : " . esc($namaUnit) . "</b></p>";
        $html .= "<p align='center' style='margin:0 0 10px;'><b>PERIODE : " . esc($periode) . "</b></p>";

        if (empty($data)) {
            return $html . '<p align="center">Tidak Ada Data</p></div>';
        }

        $kategoriKeys = array_keys($data[0]['kategori']) ?: [];

        $html .= '<style>.table, .table th, .table td { font-size: 9px !important; }</style>';
        $html .= '<div class="table"><table>';
        $html .= '<tr>';
        $html .= '<th rowspan="2">Kecamatan</th>';
        $html .= '<th rowspan="2">Tahun</th>';
        $html .= '<th colspan="' . count($kategoriKeys) . '">Jenis Pelanggan</th>';
        $html .= '<th rowspan="2">Jumlah</th>';
        $html .= '</tr><tr class="text-center">';

        foreach ($kategoriKeys as $k) {
            $html .= '<th>' . esc($k) . '</th>';
        }

        $html .= '</tr>';

        $kecamatanAktif = '';
        $totalKategori  = [];
        $totalPerKec    = [];
        $totalSemua     = 0;

        foreach ($data as $index => $row) {
            $namaKec  = $row['nama_kecamatan'];
            $subtotal = 0;

            if ($kecamatanAktif !== $namaKec) {
                $jumlahBaris = count(array_filter($data, static fn ($item) => $item['nama_kecamatan'] === $namaKec));
                $html .= '<tr><td rowspan="' . ($jumlahBaris + 1) . '">' . esc($namaKec) . '</td>';
                $kecamatanAktif = $namaKec;
            } else {
                $html .= '<tr>';
            }

            $html .= '<td class="text-center">' . (int) $row['tahun'] . '</td>';

            foreach ($kategoriKeys as $k) {
                $nilai = (float) ($row['kategori'][$k]['jumlah_piutang'] ?? 0);
                $html .= '<td class="text-right">' . number_format($nilai, 0, ',', '.') . '</td>';

                $totalKategori[$k]      = ($totalKategori[$k] ?? 0) + $nilai;
                $totalPerKec[$namaKec][$k] = ($totalPerKec[$namaKec][$k] ?? 0) + $nilai;
                $subtotal += $nilai;
            }

            $html .= '<td class="text-right"><b>' . number_format($subtotal, 0, ',', '.') . '</b></td></tr>';
            $totalSemua += $subtotal;

            if (! isset($data[$index + 1]) || $data[$index + 1]['nama_kecamatan'] !== $namaKec) {
                $subKec = 0;
                $html .= '<tr style="background-color:#ffb5b5;"><td class="text-center"><b>Total</b></td>';

                foreach ($kategoriKeys as $k) {
                    $nilai = $totalPerKec[$namaKec][$k] ?? 0;
                    $html .= '<td class="text-right"><b>' . number_format($nilai, 0, ',', '.') . '</b></td>';
                    $subKec += $nilai;
                }

                $html .= '<td class="text-right"><b>' . number_format($subKec, 0, ',', '.') . '</b></td></tr>';
            }
        }

        $html .= '<tr><td colspan="2" class="text-center"><b>Total Keseluruhan</b></td>';

        foreach ($kategoriKeys as $k) {
            $html .= '<td class="text-right"><b>' . number_format($totalKategori[$k] ?? 0, 0, ',', '.') . '</b></td>';
        }

        $html .= '<td class="text-right"><b>' . number_format($totalSemua, 0, ',', '.') . '</b></td></tr>';
        $html .= '</table></div>';

        return $html;
    }

    // =================================================================
    // BANTUAN
    // =================================================================

    /**
     * @return array{0:int|null, 1:int|null} [awal, akhir], null kalau tidak valid
     */
    private function rentangTahun($tahunAwal, $tahunAkhir): array
    {
        $awal  = is_numeric($tahunAwal) ? (int) $tahunAwal : null;
        $akhir = is_numeric($tahunAkhir) ? (int) $tahunAkhir : null;

        if ($awal === null || $akhir === null) {
            return [null, null];
        }

        if ($awal > $akhir) {
            [$awal, $akhir] = [$akhir, $awal];
        }

        return [$awal, $akhir];
    }
}
