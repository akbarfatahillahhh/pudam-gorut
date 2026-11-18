<?php

namespace App\Controllers\Laporan;

use App\Controllers\BaseController;

/**
 * Laporan Daftar Hadir Karyawan.
 *
 * Sumber data mengikuti modul Absensi (app/Controllers/Absensi.php):
 *   - pegawai aktif  : users join pegawai (pegawai.STATUS = 'Aktif')
 *   - absen datang   : absensi_masuk
 *   - absen pulang   : absensi_pulang
 *   - batas waktu    : m_setting_absen (batas_masuk / batas_masuk_tehnik)
 *   - unit kerja     : m_titik_absen, dicocokkan lewat LATITUDE/LONGITUDE
 */
class DaftarHadir extends BaseController
{
    var $title;

    public function __construct()
    {
        $this->title = 'Daftar Hadir Karyawan';
    }

    public function index()
    {
        $data = [
            'title'       => $this->title,
            'daftarBulan' => [
                '01' => 'Januari',
                '02' => 'Februari',
                '03' => 'Maret',
                '04' => 'April',
                '05' => 'Mei',
                '06' => 'Juni',
                '07' => 'Juli',
                '08' => 'Agustus',
                '09' => 'September',
                '10' => 'Oktober',
                '11' => 'November',
                '12' => 'Desember',
            ],
        ];

        return view('laporan/daftar_hadir/main', $data);
    }

    /**
     * Data laporan untuk halaman (AJAX).
     * Balasannya sama bentuknya dengan Absensi::getAbsensi().
     */
    public function data()
    {
        if (! $this->request->isAJAX()) {
            return $this->response->setStatusCode(403)->setJSON([
                'status'  => 'error',
                'message' => 'Permintaan harus melalui AJAX',
            ]);
        }

        $bulan = $this->request->getVar('bulan');
        $tahun = $this->request->getVar('tahun');
        $unit  = $this->request->getVar('unit');

        $report = $this->susunLaporan($bulan, $tahun, $unit);

        if (isset($report['error'])) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => $report['error'],
            ]);
        }

        return $this->response->setJSON([
            'status'  => 'sukses',
            'info'    => [
                'judul'   => 'DAFTAR HADIR KARYAWAN',
                'periode' => $report['bulanNama'] . ' ' . $report['tahun'],
                'unit'    => $report['namaUnit'],
            ],
            'thead'   => $this->theadHalaman($report),
            'tbody'   => $this->tbodyHalaman($report),
        ]);
    }

    /**
     * Cetak / pratinjau PDF daftar hadir.
     */
    public function pdf($bulan, $tahun, $unit)
    {
        $report = $this->susunLaporan($bulan, $tahun, $unit);

        if (isset($report['error'])) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound($report['error']);
        }

        $body = view('laporan/daftar_hadir/pdf', ['report' => $report]);
        $html = view('pdf/landscape', ['html' => $body]);

        $mpdf = new \Mpdf\Mpdf([
            'mode'             => 'utf-8',
            'format'           => 'A3',
            'orientation'      => 'L',
            'default_font_size' => 10,
            'default_font'     => 'arial',
            'tempDir'          => WRITEPATH . 'mpdf_temp',
            // Jangan perkecil tabel agar muat satu halaman; biarkan mengalir ke halaman berikutnya.
            'shrink_tables_to_fit' => 0,
        ]);

        $mpdf->SetTitle('DAFTAR HADIR KARYAWAN ' . $report['bulanNama'] . ' ' . $report['tahun']);
        $mpdf->WriteHTML($html);

        $this->response->setHeader('Content-Type', 'application/pdf');
        $mpdf->Output('daftar_hadir_karyawan.pdf', 'I');
    }

    // ------------------------------------------------------------------
    // Penyusunan data
    // ------------------------------------------------------------------

    /**
     * Susun matriks laporan (dipakai halaman dan PDF supaya angkanya sama).
     *
     * @return array{error?: string}
     */
    private function susunLaporan($bulan, $tahun, $unit): array
    {
        $bulan = (int) $bulan;
        $tahun = (int) $tahun;

        if ($bulan < 1 || $bulan > 12) {
            return ['error' => 'Bulan tidak valid'];
        }
        if ($tahun < 2000 || $tahun > 2100) {
            return ['error' => 'Tahun tidak valid'];
        }

        $bulanNama = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];

        $titik = $this->titikAbsen($unit);
        if ($unit !== 'all' && $unit !== '' && $unit !== null && $titik === null) {
            return ['error' => 'Unit kerja tidak ditemukan'];
        }

        $start     = sprintf('%04d-%02d-01', $tahun, $bulan);
        $end       = date('Y-m-t', strtotime($start));
        $hariIni   = date('Y-m-d');
        $jumlahHari = (int) date('t', strtotime($start));

        // Daftar hari + penanda akhir pekan / tanggal yang belum lewat.
        $hari = [];
        $hariBerjalan = 0;
        for ($i = 1; $i <= $jumlahHari; $i++) {
            $tanggal = sprintf('%04d-%02d-%02d', $tahun, $bulan, $i);
            $weekend = in_array((int) date('w', strtotime($tanggal)), [0, 6], true);
            $future  = $tanggal > $hariIni;

            $hari[$i] = [
                'tanggal' => $tanggal,
                'nama'    => ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'][(int) date('w', strtotime($tanggal))],
                'weekend' => $weekend,
                'future'  => $future,
            ];

            if (! $future) {
                $hariBerjalan++;
            }
        }

        $setting = $this->db->table('m_setting_absen')
            ->select('batas_masuk, batas_masuk_tehnik')
            ->get()
            ->getRowArray();

        $batasMasuk  = ! empty($setting['batas_masuk']) ? trim($setting['batas_masuk']) : '08:15:00';
        $batasTehnik = ! empty($setting['batas_masuk_tehnik']) ? trim($setting['batas_masuk_tehnik']) : $batasMasuk;

        $pegawai = $this->daftarPegawai($titik);
        $ids     = array_column($pegawai, 'id_pegawai');

        $masuk  = $this->petaAbsen('absensi_masuk', $ids, $start, $end, 'min');
        $pulang = $this->petaAbsen('absensi_pulang', $ids, $start, $end, 'max');

        $baris = [];
        $no    = 1;

        foreach ($pegawai as $p) {
            $idPegawai = $p['id_pegawai'];
            $cutoff    = ($p['TEHNIK'] === 'Y') ? $batasTehnik : $batasMasuk;

            $cellsDatang = [];
            $cellsPulang = [];
            $totalDatang = 0;
            $totalPulang = 0;
            $totalTelat  = 0;

            foreach ($hari as $i => $h) {
                if ($h['future']) {
                    $cellsDatang[$i] = '';
                    $cellsPulang[$i] = '';
                    continue;
                }

                $jamMasuk = $masuk[$idPegawai][$i] ?? null;
                if ($jamMasuk !== null) {
                    $totalDatang++;
                    if ($jamMasuk > $cutoff) {
                        $cellsDatang[$i] = 'T';
                        $totalTelat++;
                    } else {
                        $cellsDatang[$i] = 'H';
                    }
                } else {
                    $cellsDatang[$i] = '--';
                }

                if (($pulang[$idPegawai][$i] ?? null) !== null) {
                    $totalPulang++;
                    $cellsPulang[$i] = 'H';
                } else {
                    $cellsPulang[$i] = '--';
                }
            }

            // ABSEN = hari yang sudah berjalan - hari yang ada absen datang.
            $totalAbsen = max(0, $hariBerjalan - $totalDatang);

            $baris[] = [
                'no'          => $no++,
                'nama'        => $p['nama_lengkap'],
                'datang'      => $cellsDatang,
                'pulang'      => $cellsPulang,
                'totalDatang' => $totalDatang,
                'totalPulang' => $totalPulang,
                'totalTelat'  => $totalTelat,
                'totalAbsen'  => $totalAbsen,
            ];
        }

        return [
            'bulan'      => $bulan,
            'tahun'      => $tahun,
            'bulanNama'  => $bulanNama[$bulan],
            'namaUnit'   => $titik === null ? 'SEMUA UNIT' : strtoupper($titik['UNIT']),
            'jumlahHari' => $jumlahHari,
            'hari'       => $hari,
            'pegawai'    => $baris,
        ];
    }

    private function titikAbsen($unit): ?array
    {
        if ($unit === null || $unit === '' || $unit === 'all') {
            return null;
        }

        // Halaman Absensi memakai id "LATITUDE-LONGITUDE" (select2
        // GetData/getUnitByTitikKoordinat), sedangkan Laporan memakai
        // m_titik_absen.ID. Terima keduanya agar tombol PDF di modul
        // Absensi bisa memakai laporan ini.
        if (is_string($unit) && strpos($unit, '-') !== false && !is_numeric($unit)) {
            list($latitude, $longitude) = explode('-', $unit, 2);

            $row = $this->db->table('m_titik_absen')
                ->where('LATITUDE', $latitude)
                ->where('LONGITUDE', $longitude)
                ->get()
                ->getRowArray();

            return $row ?: null;
        }

        $row = $this->db->table('m_titik_absen')
            ->where('ID', $unit)
            ->get()
            ->getRowArray();

        return $row ?: null;
    }

    /**
     * Pegawai aktif, disaring per titik absen (LATITUDE/LONGITUDE).
     */
    private function daftarPegawai(?array $titik): array
    {
        $builder = $this->db->table('users')
            ->select('MAX(users.nama_lengkap) AS nama_lengkap, users.id_pegawai')
            ->select('MAX(pegawai.TEHNIK) AS TEHNIK')
            ->join('pegawai', 'pegawai.ID = users.id_pegawai')
            ->where('pegawai.STATUS', 'Aktif');

        if ($titik !== null) {
            $builder->where('pegawai.LATITUDE', $titik['LATITUDE']);
            $builder->where('pegawai.LONGITUDE', $titik['LONGITUDE']);
        }

        $builder->groupBy('users.id_pegawai');
        $builder->orderBy('nama_lengkap', 'ASC');

        return $builder->get()->getResultArray();
    }

    /**
     * Peta absen[pegawai][tanggal] => jam, satu query untuk seluruh periode.
     *
     * @param string $fungsi 'min' untuk jam datang pertama, 'max' untuk pulang terakhir
     */
    private function petaAbsen(string $table, array $ids, string $start, string $end, string $fungsi): array
    {
        if (empty($ids)) {
            return [];
        }

        $rows = $this->db->table($table)
            ->select('PEGAWAI_ID, TANGGAL, JAM')
            ->whereIn('PEGAWAI_ID', $ids)
            ->where('TANGGAL >=', $start)
            ->where('TANGGAL <=', $end)
            ->get()
            ->getResultArray();

        $peta = [];
        foreach ($rows as $row) {
            $jam = trim((string) $row['JAM']);
            if ($jam === '' || $jam === '00:00:00') {
                continue;
            }

            $id      = $row['PEGAWAI_ID'];
            $tanggal = (int) date('j', strtotime($row['TANGGAL']));

            if (! isset($peta[$id][$tanggal])) {
                $peta[$id][$tanggal] = $jam;
                continue;
            }

            $peta[$id][$tanggal] = $fungsi === 'min'
                ? min($peta[$id][$tanggal], $jam)
                : max($peta[$id][$tanggal], $jam);
        }

        return $peta;
    }

    // ------------------------------------------------------------------
    // Render tabel halaman (Bootstrap 5 / Tabler)
    // ------------------------------------------------------------------

    private function theadHalaman(array $report): string
    {
        $thead = '<tr>';
        $thead .= '<th class="text-center align-middle" rowspan="2" width="3%">No.</th>';
        $thead .= '<th class="text-center align-middle" rowspan="2" width="20%">Nama</th>';
        $thead .= '<th class="text-center" colspan="' . ($report['jumlahHari'] * 2) . '">Hari / Tanggal</th>';
        $thead .= '<th class="text-center align-middle" rowspan="2" width="7%">Hadir Datang</th>';
        $thead .= '<th class="text-center align-middle" rowspan="2" width="7%">Hadir Pulang</th>';
        $thead .= '<th class="text-center align-middle" rowspan="2" width="7%">Terlambat</th>';
        $thead .= '<th class="text-center align-middle" rowspan="2" width="6%">Absen</th>';
        $thead .= '</tr><tr>';

        foreach ($report['hari'] as $h) {
            $tglMerah = $h['weekend'] ? 'bg-danger bg-opacity-75' : '';
            $thead .= '<th class="text-center ' . $tglMerah . '">' . $h['nama'] . '</th>';
            $thead .= '<th class="text-center ' . $tglMerah . '">' . (int) date('j', strtotime($h['tanggal'])) . '</th>';
        }

        $thead .= '</tr>';

        return $thead;
    }

    private function tbodyHalaman(array $report): string
    {
        $tbody = '';

        if (empty($report['pegawai'])) {
            $kolom = 2 + ($report['jumlahHari'] * 2) + 4;
            $tbody .= '<tr><td class="text-center fw-bolder" colspan="' . $kolom . '">Tidak ada pegawai aktif pada unit ini</td></tr>';

            return $tbody;
        }

        foreach ($report['pegawai'] as $p) {
            $tbody .= '<tr>';
            $tbody .= '<td class="text-center fw-bolder">' . $p['no'] . '</td>';
            $tbody .= '<td class="text-uppercase fw-bolder">' . esc($p['nama']) . '</td>';

            foreach ($report['hari'] as $i => $h) {
                foreach ([$p['datang'][$i], $p['pulang'][$i]] as $nilai) {
                    $bg = '';
                    if ($nilai === 'H') {
                        $bg = 'bg-success bg-opacity-75';
                    } elseif ($nilai === 'T') {
                        $bg = 'bg-warning bg-opacity-75';
                    } elseif ($nilai === '--') {
                        $bg = $h['weekend'] ? 'bg-danger bg-opacity-75' : '';
                    } else {
                        // Tanggal belum berjalan: biarkan kosong.
                        $bg = '';
                    }

                    $tbody .= '<td class="text-center fw-bolder ' . $bg . '">' . $nilai . '</td>';
                }
            }

            $tbody .= '<td class="text-center text-success fw-bolder">' . $p['totalDatang'] . '</td>';
            $tbody .= '<td class="text-center text-primary fw-bolder">' . $p['totalPulang'] . '</td>';
            $tbody .= '<td class="text-center text-warning fw-bolder">' . $p['totalTelat'] . '</td>';
            $tbody .= '<td class="text-center text-danger fw-bolder">' . $p['totalAbsen'] . '</td>';
            $tbody .= '</tr>';
        }

        return $tbody;
    }
}
