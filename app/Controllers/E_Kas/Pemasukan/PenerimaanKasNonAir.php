<?php

namespace App\Controllers\E_Kas\Pemasukan;

use App\Controllers\BaseController;
use App\Models\PenerimaanKas;

/**
 * Penerimaan Kas Non Air.
 *
 * Penerimaan di luar tagihan air (sambungan baru, denda, jasa giro, dll).
 * Halaman lama (main-masuk-non-air.php) mem-posting ke
 * `kas/simpan_kas_non_air` yang tidak pernah ada di controller Kas, jadi
 * form-nya dibangun ulang di sini memakai kolom `penerimaan_kas`:
 *
 *   id_kec      -> unit penagihan (t_kecamatan)
 *   id_jenis    -> akun perkiraan pendapatan (akun_perkiraan.ID)
 *   id_kelompok -> grup akun perkiraan (akun_perkiraan.ID_GROP_PERKIRAAN)
 *   keterangan  -> uraian bebas
 *
 * Baris air dan non air dibedakan lewat id_kelompok: penerimaan air memakai
 * id_jasa jenis pelanggan (1..7), non air memakai grup akun pendapatan
 * non air / pendapatan lain-lain (16 & 17).
 */
class PenerimaanKasNonAir extends BaseController
{
    var $title;

    /** Grup akun pendapatan non air & pendapatan lain-lain. */
    private const GRUP_PENDAPATAN_NON_AIR = [16, 17];

    public function __construct()
    {
        $this->title = 'Penerimaan Kas Non Air';
    }

    public function index()
    {
        $unit = $this->db->table('t_kecamatan')
            ->select('id_kec, kecamatan')
            ->orderBy('kecamatan', 'ASC')
            ->get()
            ->getResultArray();

        $jenis = $this->db->table('akun_perkiraan')
            ->select('ID, NOMOR, NAMA_PERKIRAAN, ID_GROP_PERKIRAAN')
            ->where('STATUS', 'Pendapatan')
            ->whereIn('ID_GROP_PERKIRAAN', self::GRUP_PENDAPATAN_NON_AIR)
            ->orderBy('NOMOR', 'ASC')
            ->get()
            ->getResultArray();

        // Baris non air dikenali dari id_kelompok = grup akun pendapatan non
        // air (16/17). Baris air memakai id_jasa jenis pelanggan (1..7), jadi
        // tidak akan ikut tampil di sini.
        $riwayat = $this->db->table('penerimaan_kas pk')
            ->select('pk.id, pk.nomor_bukti, pk.tgl_penerimaan, pk.jumlah, pk.keterangan')
            ->select('k.kecamatan')
            ->select('a.NOMOR AS nomor_perkiraan, a.NAMA_PERKIRAAN')
            ->join('t_kecamatan k', 'k.id_kec = pk.id_kec', 'left')
            ->join('akun_perkiraan a', 'a.ID = pk.id_jenis', 'left')
            ->whereIn('pk.id_kelompok', self::GRUP_PENDAPATAN_NON_AIR)
            ->orderBy('pk.tgl_penerimaan', 'DESC')
            ->orderBy('pk.id', 'DESC')
            ->limit(25)
            ->get()
            ->getResultArray();

        return view('e-kas/pemasukan/penerimaan_kas_non_air/main', [
            'title'   => $this->title,
            'unit'    => $unit,
            'jenis'   => $jenis,
            'riwayat' => $riwayat,
        ]);
    }

    public function simpan()
    {
        if (! $this->request->isAJAX()) {
            return $this->response->setStatusCode(403)->setJSON([
                'status'  => false,
                'message' => 'Permintaan harus melalui AJAX',
            ]);
        }

        $tglPenerimaan = (string) $this->request->getPost('tgl_penerimaan');
        $idKec         = (int) $this->request->getPost('id_kec');
        $nomorBukti    = trim((string) $this->request->getPost('nomor_bukti'));

        $idJenis    = (array) ($this->request->getPost('id_jenis') ?? []);
        $jumlah     = (array) ($this->request->getPost('jumlah') ?? []);
        $keterangan = (array) ($this->request->getPost('keterangan') ?? []);

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

        $unitAda = $this->db->table('t_kecamatan')->where('id_kec', $idKec)->countAllResults() > 0;

        if (! $unitAda) {
            return $this->response->setJSON([
                'status'  => false,
                'message' => 'Unit penagihan tidak ditemukan.',
            ]);
        }

        if ($idJenis === [] || count($idJenis) !== count($jumlah) || count($idJenis) !== count($keterangan)) {
            return $this->response->setJSON([
                'status'  => false,
                'message' => 'Data penerimaan tidak lengkap.',
            ]);
        }

        $perkiraan = $this->db->table('akun_perkiraan')
            ->select('ID, ID_GROP_PERKIRAAN')
            ->where('STATUS', 'Pendapatan')
            ->whereIn('ID_GROP_PERKIRAAN', self::GRUP_PENDAPATAN_NON_AIR)
            ->get()
            ->getResultArray();

        $petaPerkiraan = [];

        foreach ($perkiraan as $row) {
            $petaPerkiraan[(int) $row['ID']] = (int) $row['ID_GROP_PERKIRAAN'];
        }

        $data = [];

        foreach ($idJenis as $i => $jenisId) {
            $jenisId = (int) $jenisId;

            if (! isset($petaPerkiraan[$jenisId])) {
                return $this->response->setJSON([
                    'status'  => false,
                    'message' => 'Jenis penerimaan tidak valid. Muat ulang halaman lalu coba lagi.',
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
                'nomor_bukti'    => $nomorBukti,
                'tgl_penerimaan' => $tglPenerimaan,
                'id_kec'         => $idKec,
                'id_kelompok'    => $petaPerkiraan[$jenisId],
                'id_jenis'       => $jenisId,
                'jumlah'         => $nilai,
                'keterangan'     => mb_substr(trim((string) $keterangan[$i]), 0, 150),
            ];
        }

        if ($data === []) {
            return $this->response->setJSON([
                'status'  => false,
                'message' => 'Isi minimal satu baris penerimaan dengan nominal lebih dari nol.',
            ]);
        }

        $model = new PenerimaanKas();

        // Nomor bukti kosong -> dibuat otomatis di dalam transaksi simpan.
        $hasil = $model->simpanBatch($data, $nomorBukti === '' ? 'PN' : null);

        if ($hasil['tersimpan'] === 0) {
            return $this->response->setJSON([
                'status'  => false,
                'message' => 'Gagal menyimpan penerimaan kas, silakan coba lagi.',
            ]);
        }

        return $this->response->setJSON([
            'status'  => true,
            'message' => $hasil['tersimpan'] . ' baris penerimaan non air berhasil disimpan dengan nomor bukti ' . $hasil['nomor_bukti'] . '.',
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
