<?php

namespace App\Controllers;

/**
 * Slip Gaji (penggajian) sistem E-Kas.
 *
 * Port dari aplikasi lama (app/Controllers/SlipGaji.php). Perbedaan utama
 * dengan versi lama ada di catatan masing-masing method.
 *
 * Tabel yang dipakai (tanpa prefix database):
 *   gaji_m_pegawai  : master pegawai penggajian (punya id_jabatan + jabatan denormal)
 *   gaji_m_jabatan  : master jabatan + tunjangan jabatan
 *   gaji_t_tunjangan: master jenis tunjangan (id, id_gaji, jenis_tunjangan, jumlah)
 *   gaji_t_potongan : master jenis potongan (id, id_gaji, jenis_potongan, jumlah)
 *   gaji_t_pegawai  : baris gaji per pegawai per bulan
 *   gaji_pegawai    : status bulan penggajian (Y = final, P = draft)
 */
class SlipGaji extends BaseController
{
    var $title;

    /** @var array<int,string> */
    private $bulanNama = [
        1  => 'Januari',
        2  => 'Februari',
        3  => 'Maret',
        4  => 'April',
        5  => 'Mei',
        6  => 'Juni',
        7  => 'Juli',
        8  => 'Agustus',
        9  => 'September',
        10 => 'Oktober',
        11 => 'November',
        12 => 'Desember',
    ];

    public function __construct()
    {
        $this->title = 'Slip Gaji';
    }

    public function index()
    {
        return view('slip_gaji/main', [
            'title'     => $this->title,
            'bulanNama' => $this->bulanNama,
        ]);
    }

    /**
     * Sub-halaman (gaji / tunjangan / potongan) untuk dimuat lewat AJAX.
     *
     * Versi lama menerima nama view apa saja dari query string. Sekarang
     * dibatasi ke daftar putih supaya tidak bisa dipakai membaca view lain.
     */
    public function page()
    {
        $rute = (string) $this->request->getVar('rute');

        if (!in_array($rute, ['gaji', 'tunjangan', 'potongan'], true)) {
            return $this->response->setStatusCode(404)->setJSON([
                'status' => false,
                'msg'    => 'Halaman tidak ditemukan',
            ]);
        }

        return $this->response->setJSON([
            'html' => view('slip_gaji/page/' . $rute, ['bulanNama' => $this->bulanNama]),
        ]);
    }

    /**
     * Data grid penggajian satu bulan.
     *
     * Versi lama memakai array_merge() sehingga JSON-nya bercampur antara
     * baris pegawai (key numerik) dan master tunjangan/potongan (key nama).
     * Sekarang bentuknya eksplisit: data, tunjangan, potongan, ref_gaji, status.
     * Join gaji_pegawai juga tidak lagi per baris pegawai: status diambil
     * sekali per bulan supaya tidak ada baris ganda.
     */
    public function getMasterGaji()
    {
        $dates = $this->periode(
            $this->request->getGet('bulan'),
            $this->request->getGet('tahun')
        );

        $rows = $this->db->table('gaji_m_pegawai')
            ->select('gaji_m_pegawai.id, gaji_m_pegawai.nama_pegawai, gaji_m_pegawai.jk, gaji_m_pegawai.gol')
            // gaji_m_pegawai juga punya kolom `jabatan` (denormal). Pakai alias
            // supaya tidak ambigu dan tetap menampilkan jabatan dari master.
            ->select('jabatan.jabatan AS jabatan, jabatan.tunjangan AS tunjangan_jabatan')
            ->select('gaji_t_pegawai.total_tunjangan, gaji_t_pegawai.total_potongan, gaji_t_pegawai.qty_tunjangan, gaji_t_pegawai.id_tunjangan, gaji_t_pegawai.id_potongan')
            ->join('gaji_m_jabatan jabatan', 'jabatan.id = gaji_m_pegawai.id_jabatan', 'left')
            ->join(
                'gaji_t_pegawai',
                'gaji_t_pegawai.id_pegawai = gaji_m_pegawai.id AND gaji_t_pegawai.bulan = ' . $this->db->escape($dates),
                'left'
            )
            ->orderBy('gaji_m_pegawai.id', 'ASC')
            ->get()
            ->getResult();

        $master = $this->db->table('gaji_pegawai')
            ->where('bulan', $dates)
            ->orderBy('id', 'DESC')
            ->get()
            ->getRow();

        return $this->response->setJSON([
            'status'    => true,
            'bulan'     => $dates,
            'ref_gaji'  => $master->ref_gaji ?? null,
            'final'     => ($master->status ?? null) === 'Y',
            'data'      => $rows,
            'tunjangan' => $this->db->table('gaji_t_tunjangan')->orderBy('id', 'ASC')->get()->getResult(),
            'potongan'  => $this->db->table('gaji_t_potongan')->orderBy('id', 'ASC')->get()->getResult(),
        ]);
    }

    public function getDataTunjangan()
    {
        $data = $this->db->table('gaji_t_tunjangan')
            ->orderBy('id', 'ASC')
            ->get()
            ->getResult();

        return $this->response->setJSON(['status' => true, 'data' => $data]);
    }

    public function getDatapotongan()
    {
        $data = $this->db->table('gaji_t_potongan')
            ->orderBy('id', 'ASC')
            ->get()
            ->getResult();

        return $this->response->setJSON(['status' => true, 'data' => $data]);
    }

    /**
     * Simpan master tunjangan.
     *
     * Versi lama tidak mengisi kolom `id_gaji` yang NOT NULL tanpa default,
     * sehingga INSERT gagal kalau sql_mode memakai STRICT_TRANS_TABLES.
     * Sekarang id_gaji diisi 0 (sama dengan data yang sudah ada).
     */
    public function saveTunjangan()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(405)->setJSON(['status' => false, 'msg' => 'Metode tidak diizinkan']);
        }

        $id     = (int) $this->request->getPost('id');
        $jenis  = trim((string) $this->request->getPost('jenis_tunjangan'));
        $jumlah = (int) $this->request->getPost('jumlah');

        if ($jenis === '') {
            return $this->response->setJSON(['status' => false, 'msg' => 'Jenis tunjangan wajib diisi']);
        }
        if ($jumlah < 0) {
            return $this->response->setJSON(['status' => false, 'msg' => 'Jumlah tidak boleh negatif']);
        }

        $data = [
            'jenis_tunjangan' => $jenis,
            'jumlah'          => $jumlah,
        ];

        if ($id > 0) {
            $this->db->table('gaji_t_tunjangan')->where('id', $id)->update($data);
        } else {
            $data['id_gaji'] = 0;
            $this->db->table('gaji_t_tunjangan')->insert($data);
        }

        return $this->response->setJSON([
            'status' => true,
            'msg'    => 'Data tunjangan berhasil disimpan',
        ]);
    }

    /**
     * Simpan master potongan.
     *
     * Versi lama memakai id field yang sama untuk form tunjangan dan potongan
     * (`#id`, `#jumlah`) sehingga bentrok kalau dua form ada di satu halaman.
     * Di sini view memakai id berbeda dan name tetap sama.
     */
    public function savepotongan()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(405)->setJSON(['status' => false, 'msg' => 'Metode tidak diizinkan']);
        }

        $id     = (int) $this->request->getPost('id');
        $jenis  = trim((string) $this->request->getPost('jenis_potongan'));
        $jumlah = (int) $this->request->getPost('jumlah');

        if ($jenis === '') {
            return $this->response->setJSON(['status' => false, 'msg' => 'Jenis potongan wajib diisi']);
        }
        if ($jumlah < 0) {
            return $this->response->setJSON(['status' => false, 'msg' => 'Jumlah tidak boleh negatif']);
        }

        $data = [
            'jenis_potongan' => $jenis,
            'jumlah'         => $jumlah,
        ];

        if ($id > 0) {
            $this->db->table('gaji_t_potongan')->where('id', $id)->update($data);
        } else {
            $data['id_gaji'] = 0;
            $this->db->table('gaji_t_potongan')->insert($data);
        }

        return $this->response->setJSON([
            'status' => true,
            'msg'    => 'Data potongan berhasil disimpan',
        ]);
    }

    public function deleteTunjangan()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(405)->setJSON(['status' => false, 'msg' => 'Metode tidak diizinkan']);
        }

        $id = (int) $this->request->getPost('id');

        if ($id <= 0) {
            return $this->response->setJSON(['status' => false, 'msg' => 'Data tidak ditemukan']);
        }

        $deleted = $this->db->table('gaji_t_tunjangan')->where('id', $id)->delete();

        return $this->response->setJSON(
            $deleted
                ? ['status' => true, 'msg' => 'Data tunjangan berhasil dihapus']
                : ['status' => false, 'msg' => 'Gagal menghapus data tunjangan']
        );
    }

    public function deletepotongan()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(405)->setJSON(['status' => false, 'msg' => 'Metode tidak diizinkan']);
        }

        $id = (int) $this->request->getPost('id');

        if ($id <= 0) {
            return $this->response->setJSON(['status' => false, 'msg' => 'Data tidak ditemukan']);
        }

        $deleted = $this->db->table('gaji_t_potongan')->where('id', $id)->delete();

        return $this->response->setJSON(
            $deleted
                ? ['status' => true, 'msg' => 'Data potongan berhasil dihapus']
                : ['status' => false, 'msg' => 'Gagal menghapus data potongan']
        );
    }

    /**
     * Simpan gaji satu bulan.
     *
     * Payload `data` berisi satu baris per pegawai:
     *   id_pegawai, id_tunjangan, qty_tunjangan, id_potongan, total_potongan
     * `gaji_t_pegawai` tidak punya kolom qty potongan, jadi potongan dikirim
     * sebagai nominal (sama seperti aplikasi lama). Tunjangan dikirim sebagai
     * qty dan nominalnya dihitung ulang di server dari master.
     *
     * Perbaikan dibanding versi lama:
     *  - upsert memakai primary key `gaji_t_pegawai.id`; versi lama memakai
     *    updateBatch(..., 'id_pegawai') yang menimpa baris bulan lain.
     *  - ref_gaji bulan yang sudah ada dipakai ulang (versi lama membuat ref
     *    baru tiap simpan).
     *  - bulan berstatus final (Y) tidak bisa disimpan ulang.
     *  - semua penulisan dibungkus transaksi.
     */
    public function saveData()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(405)->setJSON(['status' => false, 'msg' => 'Metode tidak diizinkan']);
        }

        $bulanPost = (int) $this->request->getPost('bulan');
        $tahunPost = (int) $this->request->getPost('tahun');

        if ($bulanPost < 1 || $bulanPost > 12 || $tahunPost < 2000 || $tahunPost > 2100) {
            return $this->response->setJSON(['status' => false, 'msg' => 'Bulan/tahun tidak valid']);
        }

        $dates  = sprintf('%04d-%02d-01', $tahunPost, $bulanPost);
        $status = strtoupper((string) $this->request->getPost('status'));
        $status = in_array($status, ['Y', 'P'], true) ? $status : 'P';

        $data = $this->request->getPost('data');

        if (!is_array($data) || empty($data)) {
            return $this->response->setJSON(['status' => false, 'msg' => 'Tidak ada data pegawai yang dikirim']);
        }

        $master = $this->db->table('gaji_pegawai')
            ->where('bulan', $dates)
            ->orderBy('id', 'DESC')
            ->get()
            ->getRow();

        if ($master && $master->status === 'Y') {
            return $this->response->setJSON([
                'status' => false,
                'msg'    => 'Gaji bulan ini sudah final dan tidak dapat diubah',
            ]);
        }

        $ref_gaji = $master->ref_gaji ?? ('GJ-' . date('ymd', strtotime($dates)) . '-' . rand(1000, 9999));

        $tunjanganMaster = [];
        foreach ($this->db->table('gaji_t_tunjangan')->get()->getResultArray() as $row) {
            $tunjanganMaster[(int) $row['id']] = (int) $row['jumlah'];
        }

        $potonganMaster = [];
        foreach ($this->db->table('gaji_t_potongan')->get()->getResultArray() as $row) {
            $potonganMaster[(int) $row['id']] = (int) $row['jumlah'];
        }

        $pegawaiIds = array_map('intval', array_column(
            $this->db->table('gaji_m_pegawai')->select('id')->get()->getResultArray(),
            'id'
        ));

        $existing = [];
        foreach ($this->db->table('gaji_t_pegawai')->select('id, id_pegawai')->where('bulan', $dates)->get()->getResultArray() as $row) {
            $existing[(int) $row['id_pegawai']] = (int) $row['id'];
        }

        $rows = [];
        foreach ($data as $item) {
            if (!is_array($item)) {
                continue;
            }

            $idPegawai = (int) ($item['id_pegawai'] ?? 0);

            if ($idPegawai <= 0 || !in_array($idPegawai, $pegawaiIds, true)) {
                continue;
            }

            $idTunjangan  = (int) ($item['id_tunjangan'] ?? 0);
            $qtyTunjangan = (int) ($item['qty_tunjangan'] ?? 0);

            if ($idTunjangan <= 0 || !isset($tunjanganMaster[$idTunjangan]) || $qtyTunjangan <= 0) {
                $idTunjangan  = null;
                $qtyTunjangan = 0;
                $totalTunjangan = 0;
            } else {
                $totalTunjangan = $qtyTunjangan * $tunjanganMaster[$idTunjangan];
            }

            $idPotongan     = (int) ($item['id_potongan'] ?? 0);
            $totalPotongan  = (int) ($item['total_potongan'] ?? 0);

            if ($idPotongan <= 0 || !isset($potonganMaster[$idPotongan]) || $totalPotongan <= 0) {
                $idPotongan    = null;
                $totalPotongan = 0;
            }

            $rows[] = [
                'ref_gaji'        => $ref_gaji,
                'bulan'           => $dates,
                'id_pegawai'      => $idPegawai,
                'id_tunjangan'    => $idTunjangan,
                'qty_tunjangan'   => $qtyTunjangan,
                'total_tunjangan' => $totalTunjangan,
                'id_potongan'     => $idPotongan,
                'total_potongan'  => $totalPotongan,
            ];
        }

        if (empty($rows)) {
            return $this->response->setJSON(['status' => false, 'msg' => 'Tidak ada pegawai valid pada data yang dikirim']);
        }

        $this->db->transBegin();

        try {
            if ($master) {
                $this->db->table('gaji_pegawai')
                    ->where('id', $master->id)
                    ->update(['ref_gaji' => $ref_gaji, 'bulan' => $dates, 'status' => $status]);
            } else {
                $this->db->table('gaji_pegawai')->insert([
                    'ref_gaji' => $ref_gaji,
                    'bulan'    => $dates,
                    'status'   => $status,
                ]);
            }

            foreach ($rows as $row) {
                if (isset($existing[$row['id_pegawai']])) {
                    $this->db->table('gaji_t_pegawai')
                        ->where('id', $existing[$row['id_pegawai']])
                        ->update($row);
                } else {
                    $this->db->table('gaji_t_pegawai')->insert($row);
                }
            }

            if ($this->db->transStatus() === false) {
                throw new \RuntimeException('transaksi gaji gagal');
            }

            $this->db->transCommit();
        } catch (\Throwable $e) {
            $this->db->transRollback();
            log_message('error', 'Gagal menyimpan slip gaji ' . $dates . ': ' . $e->getMessage());

            return $this->response->setJSON([
                'status' => false,
                'msg'    => 'Gagal menyimpan data gaji',
            ]);
        }

        return $this->response->setJSON([
            'status'   => true,
            'msg'      => 'Data gaji berhasil disimpan',
            'ref_gaji' => $ref_gaji,
        ]);
    }

    /**
     * Cetak rekap gaji bulan terpilih ke PDF.
     *
     * Versi lama merangkai HTML langsung dan memakai tabel matriks per jenis
     * tunjangan; di sini memakai view + wrapper pdf/landscape seperti cetakan
     * lain di aplikasi ini.
     */
    public function print()
    {
        $bulanPost = (int) $this->request->getGet('bulan');
        $tahunPost = (int) $this->request->getGet('tahun');

        if ($bulanPost < 1 || $bulanPost > 12 || $tahunPost < 2000 || $tahunPost > 2100) {
            return $this->response->setStatusCode(400)->setBody('Bulan/tahun tidak valid');
        }

        $dates = sprintf('%04d-%02d-01', $tahunPost, $bulanPost);

        $result = $this->db->table('gaji_t_pegawai')
            ->select('gaji_t_pegawai.*')
            ->select('pegawai.nama_pegawai, pegawai.jk, pegawai.gol')
            ->select('jabatan.jabatan AS nama_jabatan, jabatan.tunjangan AS tunjangan_jabatan')
            ->select('tunjangan.jenis_tunjangan, potongan.jenis_potongan')
            ->join('gaji_m_pegawai pegawai', 'pegawai.id = gaji_t_pegawai.id_pegawai', 'left')
            ->join('gaji_m_jabatan jabatan', 'jabatan.id = pegawai.id_jabatan', 'left')
            ->join('gaji_t_tunjangan tunjangan', 'tunjangan.id = gaji_t_pegawai.id_tunjangan', 'left')
            ->join('gaji_t_potongan potongan', 'potongan.id = gaji_t_pegawai.id_potongan', 'left')
            ->where('gaji_t_pegawai.bulan', $dates)
            ->orderBy('pegawai.id', 'ASC')
            ->get()
            ->getResultArray();

        $master = $this->db->table('gaji_pegawai')
            ->where('bulan', $dates)
            ->orderBy('id', 'DESC')
            ->get()
            ->getRow();

        $judul = 'DAFTAR GAJI KARYAWAN<br>BULAN ' . strtoupper($this->bulanNama[$bulanPost]) . ' ' . $tahunPost;

        $html = view('slip_gaji/pdf/rekap_gaji', [
            'judul'     => $judul,
            'result'    => $result,
            'status'    => $master->status ?? null,
            'tunjangan' => $this->db->table('gaji_t_tunjangan')->orderBy('id', 'ASC')->get()->getResultArray(),
            'potongan'  => $this->db->table('gaji_t_potongan')->orderBy('id', 'ASC')->get()->getResultArray(),
        ]);

        $pdf = view('pdf/landscape', ['html' => $html]);

        $mpdf = new \Mpdf\Mpdf([
            'mode'        => 'utf-8',
            'format'      => 'A4',
            'orientation' => 'L',
            'tempDir'     => WRITEPATH . 'mpdf_temp',
        ]);
        $mpdf->setFooter('SLIP GAJI');
        $mpdf->WriteHTML($pdf);
        $mpdf->SetTitle('Slip Gaji ' . $this->bulanNama[$bulanPost] . ' ' . $tahunPost);
        $this->response->setHeader('Content-Type', 'application/pdf');
        $mpdf->Output('Slip Gaji ' . $this->bulanNama[$bulanPost] . ' ' . $tahunPost . '.pdf', 'I');
    }

    /**
     * Normalisasi periode menjadi tanggal 1 bulan berjalan.
     * Nilai di luar rentang diganti bulan/tahun sekarang (dipakai endpoint baca).
     */
    private function periode($bulan, $tahun): string
    {
        $bulan = (int) $bulan;
        $tahun = (int) $tahun;

        if ($bulan < 1 || $bulan > 12) {
            $bulan = (int) date('m');
        }
        if ($tahun < 2000 || $tahun > 2100) {
            $tahun = (int) date('Y');
        }

        return sprintf('%04d-%02d-01', $tahun, $bulan);
    }
}
