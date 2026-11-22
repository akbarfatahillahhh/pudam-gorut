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

        $builder->orderBy('m_pelanggan.no_sambungan', 'ASC');
        $builder->orderBy('m_pelanggan.id_kec', 'ASC');

        return $builder;
    }

    /**
     * PMK (pemakaian m3).
     *
     * Diturunkan dari tagihan, bukan selisih meter: 7.914 baris tahun
     * 2026 punya pemakaian_air_lalu = 0 padahal pemakaian_air-nya
     * ribuan (sisa input piutang lama), jadi selisihnya meledak.
     */
    private function getPemakaian($row): int
    {
        $tarifAtas = (float) ($row->tarif_diatas_10 ?? 0);
        $tagAtas   = (float) ($row->tag_diatas_10 ?? 0);

        if ($tarifAtas > 0 && $tagAtas > 0) {
            return (int) round(10 + $tagAtas / $tarifAtas);
        }

        return 10;
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
