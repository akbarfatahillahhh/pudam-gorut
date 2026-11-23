<?php

namespace App\Controllers\Laporan;

use App\Controllers\BaseController;
use Hermawan\DataTables\DataTable;

/**
 * Laporan Daftar Rekening Ditagih (DRD) Air.
 *
 * Empat mode: semua, per petugas, per unit, per kelurahan. Di aplikasi
 * lama empat mode ini empat fungsi yang hampir identik, ditambah empat
 * blok salinan lagi di export_pdf() — total delapan salinan query yang
 * isinya sama. Di sini semuanya memakai satu builder: $filterBuilder().
 *
 * Menambah filter pelanggan (semua / aktif / tidak aktif), yang di
 * aplikasi lama hidup di halaman terpisah (`act=dp_aktif`). Halamannya
 * memang hanya varian DRD dengan `m_pelanggan.status_aktif = '1'`,
 * jadi tidak perlu dua laporan terpisah.
 */
class DrdAir extends BaseController
{
    var $title;

    public function __construct()
    {
        $this->title = 'DRD Air';
    }

    public function index()
    {
        return view('laporan/drd_air/main', ['title' => $this->title]);
    }

    public function loadPage($page)
    {
        $peta = [
            'all'        => 'all',
            'perPetugas' => 'per_petugas',
            'perUnit'    => 'per_unit',
            'perKel'     => 'per_kel',
        ];

        if (! isset($peta[$page])) {
            return $this->response->setJSON([
                'status' => 'error',
                'msg'    => 'Jenis laporan tidak dikenal.',
            ]);
        }

        return $this->response->setJSON([
            'page' => view('/laporan/drd_air/page/' . $peta[$page]),
        ]);
    }

    public function datatable()
    {
        $filter = $this->filterDariRequest();

        return DataTable::of($this->queryDrd($filter))
            ->add('hargaAir', function ($row) {
                return '<i>Rp. ' . number_format((float) $row->tag_dibawah_10 + (float) $row->tag_diatas_10, 0, ',', '.') . '</i>';
            })
            ->add('by_admin', function ($row) {
                return '<i>Rp. ' . number_format((float) $row->by_admin, 0, ',', '.') . '</i>';
            })
            ->add('jlh_tagihan', function ($row) {
                return '<i>Rp. ' . number_format((float) $row->jlh_tagihan, 0, ',', '.') . '</i>';
            })
            ->add('bulan_tagihan', function ($row) {
                return $row->bulan_tagihan ? date('F Y', strtotime($row->bulan_tagihan)) : '-';
            })
            ->add('pemakaian', function ($row) {
                return $this->getPemakaian($row);
            })
            ->add('user', function ($row) {
                return $row->nama_lengkap ?? '-';
            })
            ->toJson(true);
    }

    public function modal_export_pdf()
    {
        $data = $this->filterDariRequest();
        $data['params'] = $this->parameterUrl($data);

        $msg = ['html' => view('laporan/drd_air/modal/pdf', $data)];
        echo json_encode($msg);
    }

    public function export_pdf($jenisLaporan = 'all', $dateStart = null, $dateEnd = null, $statusLaporan = 'all', $petugas = 'all', $unit = 'all', $kel = 'all')
    {
        // Parameter datang dari segment URL (iframe), bukan query string.
        // Tanpa ini filter tanggal kosong dan query menarik seluruh
        // 494 ribu baris t_tagihan.
        $filter = $this->filterDariRequest([
            'jenisLaporan'  => $jenisLaporan,
            'dateStart'     => ($dateStart === 'all' || ! $dateStart) ? '' : $dateStart,
            'dateEnd'       => ($dateEnd === 'all' || ! $dateEnd) ? '' : $dateEnd,
            'statusLaporan' => $statusLaporan,
            'petugas'       => $petugas,
            'unit'          => $unit,
            'kel'           => $kel,
        ]);

        if ($filter['dateStart'] === '' || $filter['dateEnd'] === '') {
            return $this->response->setStatusCode(400)
                ->setBody('Periode (tanggal awal dan akhir) wajib diisi.');
        }

        $ket = $this->keterangan($filter);

        // Batas aman PDF. Satu bulan penuh (±3.600 baris) butuh sekitar
        // 25 detik; dua bulan sudah menembus max_execution_time 30 detik
        // di mPDF. Tolak lebih awal dengan pesan yang jelas, jangan
        // dibiarkan timeout tanpa keterangan.
        $batasBaris = 4500;

        $jumlahBaris = $this->queryDrd($filter)->countAllResults(false);

        if ($jumlahBaris > $batasBaris) {
            $pesan = 'Data terlalu banyak untuk diekspor sekali jalan: '
                . number_format($jumlahBaris, 0, ',', '.') . ' baris (batas '
                . number_format($batasBaris, 0, ',', '.') . ').'
                . ' Persempit periode atau pakai filter unit/kelurahan.';

            if ($this->request->isAJAX()) {
                return $this->response->setStatusCode(413)->setJSON([
                    'status' => 'error',
                    'msg'    => $pesan,
                ]);
            }

            return $this->response->setStatusCode(413)->setBody($pesan);
        }

        $mpdf = new \Mpdf\Mpdf([
            'mode'              => 'utf-8',
            'format'            => 'A4',
            'orientation'       => 'L',
            'default_font_size' => 9,
            'default_font'      => 'arial',
            'tempDir'           => WRITEPATH . 'mpdf_temp',
        ]);

        // Kop perusahaan + CSS ditulis sekali.
        $mpdf->WriteHTML(view('pdf/potrait', ['html' => '']));

        // Baris dibaca & ditulis per potongan. Menampung 3.500+ baris
        // sekaligus menghabiskan memory_limit 128M, dan satu WriteHTML
        // sebesar itu juga melewati pcre.backtrack_limit mPDF.
        $perPotong = 200;
        $offset    = 0;
        $no        = 0;
        $halaman   = 0;
        $adaBaris  = false;

        $kelompok = null;

        if ($filter['jenisLaporan'] === 'perPetugas' && $filter['petugas'] === 'all') {
            $kelompok = 'nama_lengkap';
        } elseif ($filter['jenisLaporan'] === 'perKel' && $filter['kel'] === 'all') {
            $kelompok = 'kelurahan';
        }

        $judulKelompok = null;

        do {
            $rows = $this->queryDrd($filter)
                ->limit($perPotong, $offset)
                ->get()
                ->getResultArray();

            $jumlahPotong = count($rows);

            if ($jumlahPotong > 0) {
                $adaBaris = true;
            }

            $barisCetak = [];

            foreach ($rows as $r) {
                $judul = null;

                if ($kelompok !== null) {
                    $nilai = strtoupper((string) ($r[$kelompok] ?? '-'));

                    if ($nilai !== $judulKelompok) {
                        $judulKelompok = $nilai;
                        $judul = $nilai;
                    }
                }

                // Bandingkan sebagai float, bukan string varchar.
                $tagBawah = (float) $r['tag_dibawah_10'];
                $tagAtas  = (float) $r['tag_diatas_10'];

                $barisCetak[] = [
                    'no'       => ++$no,
                    'judul'    => $judul,
                    'sambungan' => $r['no_sambungan'] ?? '-',
                    'nama'     => $r['nama_pelanggan'] ?? '-',
                    'rek'      => $r['no_rek'] ?? '-',
                    'harga'    => $tagBawah + $tagAtas,
                    'admin'    => (float) $r['by_admin'],
                    'jumlah'   => (float) $r['jlh_tagihan'],
                    'pmk'      => $this->getPemakaian((object) $r),
                    'bulan'    => $r['bulan_tagihan'] ? date('M Y', strtotime($r['bulan_tagihan'])) : '-',
                    'ket'      => $r['jenis_pelanggan'] ?? '-',
                ];
            }

            $mpdf->WriteHTML(view('laporan/drd_air/pdf', [
                'baris_awal' => $halaman === 0,
                'baris'      => $barisCetak,
                'keterangan' => $ket,
            ]));

            $offset += $perPotong;
            $halaman++;
        } while ($offset < 50000 && $jumlahPotong === $perPotong);

        if (! $adaBaris) {
            $mpdf->WriteHTML(view('laporan/drd_air/pdf', [
                'baris_awal' => true,
                'baris'      => [],
                'keterangan' => $ket,
            ]));
        }

        $mpdf->SetTitle('LAPORAN DRD AIR');

        return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setBody($mpdf->Output('', 'S'));
    }

    // =================================================================
    // QUERY
    // =================================================================

    /**
     * Satu-satunya tempat query DRD dibentuk.
     */
    private function queryDrd(array $f)
    {
        $builder = $this->db->table('t_tagihan')
            ->select('
                m_pelanggan.id_pelanggan,
                m_pelanggan.no_sambungan,
                m_pelanggan.nama_pelanggan,
                m_pelanggan.status_aktif,
                m_pelanggan.id_kec,
                m_pelanggan.id_kelurahan,

                t_tagihan.id_pemakaian,
                t_tagihan.no_rek,
                t_tagihan.tag_dibawah_10,
                t_tagihan.tag_diatas_10,
                t_tagihan.tarif_diatas_10,
                t_tagihan.by_admin,
                t_tagihan.jlh_tagihan,
                t_tagihan.bulan_tagihan,
                t_tagihan.pemakaian_air,
                t_tagihan.pemakaian_air_lalu,
                t_tagihan.status,

                m_jenis_pelanggan.jenis_pelanggan,
                t_kelurahan.kelurahan,
                t_kecamatan.kecamatan,

                users.nama_lengkap,
                users.id_session
            ')
            ->join('m_pelanggan', 't_tagihan.id_pelanggan = m_pelanggan.id_pelanggan')
            ->join('m_jenis_pelanggan', 't_tagihan.id_jenis = m_jenis_pelanggan.id_jenis')
            ->join('users', 't_tagihan.id_user = users.id_session', 'left')
            // Wilayah dari m_pelanggan: t_tagihan.id_kec NULL pada
            // 11.474 baris lama.
            ->join('t_kelurahan', 't_kelurahan.id_kelurahan = m_pelanggan.id_kelurahan', 'left')
            ->join('t_kecamatan', 't_kecamatan.id_kec = m_pelanggan.id_kec', 'left');

        if ($f['dateStart'] !== '') {
            $builder->where('t_tagihan.bulan_tagihan >=', $f['dateStart']);
        }

        if ($f['dateEnd'] !== '') {
            $builder->where('t_tagihan.bulan_tagihan <=', $f['dateEnd']);
        }

        if ($f['statusLaporan'] !== 'all') {
            $builder->where('t_tagihan.status', $f['statusLaporan']);
        }

        // Filter pelanggan: inti dari halaman `act=dp_aktif` di bs.
        if ($f['pelanggan'] === 'aktif') {
            $builder->where('m_pelanggan.status_aktif', '1');
        } elseif ($f['pelanggan'] === 'nonaktif') {
            $builder->where('m_pelanggan.status_aktif', '0');
        }

        if ($f['jenisLaporan'] === 'perPetugas' && $f['petugas'] !== 'all') {
            $builder->where('t_tagihan.id_user', $f['petugas']);
        }

        if ($f['jenisLaporan'] === 'perUnit' && $f['unit'] !== 'all') {
            $builder->where('m_pelanggan.id_kec', (int) $f['unit']);
        }

        if ($f['jenisLaporan'] === 'perKel' && $f['kel'] !== 'all') {
            $builder->where('m_pelanggan.id_kelurahan', (int) $f['kel']);
        }

        // Kunci urutan WAJIB unik: export_pdf() membaca data per potongan
        // (LIMIT/OFFSET), dan kalau kuncinya tidak unik baris yang sama
        // bisa muncul dua kali atau terlewat di batas potongan. Satu
        // pelanggan bisa punya 15 baris (satu per bulan) dalam rentang
        // setahun, jadi no_sambungan+id_kec saja tidak cukup.
        $builder->orderBy('m_pelanggan.no_sambungan', 'ASC');
        $builder->orderBy('m_pelanggan.id_kec', 'ASC');
        $builder->orderBy('t_tagihan.bulan_tagihan', 'DESC');
        $builder->orderBy('t_tagihan.id_pemakaian', 'ASC');

        return $builder;
    }

    /**
     * PMK (pemakaian m3).
     *
     * Dua sumber, dipakai berurutan:
     *
     * 1. Kalau tagihan punya tag_diatas_10, kubikasi pasti di atas 10:
     *    10 + (tag_diatas_10 / tarif_diatas_10). Ini juga satu-satunya
     *    cara untuk baris yang pemakaian_air_lalu-nya 0 (7.914 baris
     *    2026) karena selisih meter di situ tidak bermakna.
     * 2. Selain itu pakai selisih meter apa adanya, dibatasi 0..10.
     *    Selisih di bawah 10 memang nyata: dari 21.547 baris dengan
     *    pemakaian_air_lalu > 0, sebarannya 0..10 dan tidak ada satu pun
     *    yang bertentangan dengan tag_diatas_10.
     *
     * Jangan asal mengembalikan 10 saat tag_diatas_10 = 0. Percobaan
     * sebelumnya melakukan itu dan melebihkan total kubikasi setahun
     * sebesar 68.168 m3 (455.777 vs 387.609 yang sebenarnya).
     */
    private function getPemakaian($row): int
    {
        $tarifAtas = (float) ($row->tarif_diatas_10 ?? 0);
        $tagAtas   = (float) ($row->tag_diatas_10 ?? 0);

        if ($tarifAtas > 0 && $tagAtas > 0) {
            return (int) round(10 + $tagAtas / $tarifAtas);
        }

        $meterIni  = (float) ($row->pemakaian_air ?? 0);
        $meterLalu = (float) ($row->pemakaian_air_lalu ?? 0);
        $selisih   = $meterIni - $meterLalu;

        return (int) max(0, min(10, $selisih));
    }

    /**
     * Keterangan pembeda antar mode (dipakai judul PDF).
     */
    private function keterangan(array $f): array
    {
        $label = [
            'all'        => null,
            'perPetugas' => 'PETUGAS',
            'perUnit'    => 'UNIT',
            'perKel'     => 'KELURAHAN/DESA',
        ];

        $nilai = null;

        if ($f['jenisLaporan'] === 'perPetugas' && $f['petugas'] !== 'all') {
            $row = $this->db->table('users')
                ->select('nama_lengkap')
                ->where('id_session', $f['petugas'])
                ->get()
                ->getRowArray();

            $nilai = $row ? strtoupper($row['nama_lengkap']) : 'PETUGAS';
        } elseif ($f['jenisLaporan'] === 'perUnit' && $f['unit'] !== 'all') {
            $row = $this->db->table('t_kecamatan')
                ->select('kecamatan')
                ->where('id_kec', (int) $f['unit'])
                ->get()
                ->getRowArray();

            $nilai = $row ? strtoupper($row['kecamatan']) : 'UNIT';
        } elseif ($f['jenisLaporan'] === 'perKel' && $f['kel'] !== 'all') {
            $row = $this->db->table('t_kelurahan')
                ->select('kelurahan')
                ->where('id_kelurahan', (int) $f['kel'])
                ->get()
                ->getRowArray();

            $nilai = $row ? strtoupper($row['kelurahan']) : 'KELURAHAN';
        }

        $statusPelanggan = [
            'all'      => null,
            'aktif'    => 'PELANGGAN AKTIF',
            'nonaktif' => 'PELANGGAN TIDAK AKTIF',
        ][$f['pelanggan']] ?? null;

        return [
            'judul'  => $label[$f['jenisLaporan']] ?? null,
            'nilai'  => $nilai,
            'status' => $statusPelanggan,
            'periode' => ($f['dateStart'] !== '' && $f['dateEnd'] !== '')
                ? date('d/m/Y', strtotime($f['dateStart'])) . ' - ' . date('d/m/Y', strtotime($f['dateEnd']))
                : 'SEMUA PERIODE',
        ];
    }

    /**
     * @return array{jenisLaporan:string, dateStart:string, dateEnd:string, statusLaporan:string, petugas:string, unit:string, kel:string, pelanggan:string}
     */
    private function filterDariRequest(array $timpa = []): array
    {
        $ambil = fn (string $key, string $default = 'all') => (string) ($this->request->getVar($key) ?? $default);

        $filter = [
            'jenisLaporan'  => $ambil('jenisLaporan'),
            'dateStart'     => $ambil('dateStart', ''),
            'dateEnd'       => $ambil('dateEnd', ''),
            'statusLaporan' => $ambil('statusLaporan'),
            'petugas'       => $ambil('petugas'),
            'unit'          => $ambil('unit'),
            'kel'           => $ambil('kel'),
            'pelanggan'     => $ambil('pelanggan'),
        ];

        // Nilai dari segment URL menang atas query string.
        foreach ($timpa as $kunci => $nilai) {
            if ($nilai !== null && $nilai !== '') {
                $filter[$kunci] = (string) $nilai;
            }
        }

        return $filter;
    }

    private function parameterUrl(array $f): string
    {
        return implode('/', array_map('rawurlencode', [
            $f['jenisLaporan'],
            $f['dateStart'] !== '' ? $f['dateStart'] : 'all',
            $f['dateEnd'] !== '' ? $f['dateEnd'] : 'all',
            $f['statusLaporan'],
            $f['petugas'],
            $f['unit'],
            $f['kel'],
        ])) . '?pelanggan=' . rawurlencode($f['pelanggan']);
    }
}
