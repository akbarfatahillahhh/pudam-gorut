<?php

namespace App\Controllers\Billing;

use App\Controllers\BaseController;
use Hermawan\DataTables\DataTable;

/**
 * Catat Meter: daftar hasil pencatatan meter petugas + verifikasi.
 *
 * Padanan tab "CATAT METER" di module/home/billing_sistem.php aplikasi
 * lama (control_meter.php + data/ss_catat_meter.php + modalCatatMeter.php
 * + tagihan/aksi_verifikasi_meter.php).
 *
 * Alur kerjanya:
 *   1. Petugas (mobile) mencatat angka meter -> baris baru di
 *      t_catat_meter dengan status_rekening = 'N' dan foto meter/segel/
 *      rumah.
 *   2. Admin memverifikasi: sistem menerbitkan baris t_tagihan dari data
 *      catat meter, lalu menandai status_rekening = 'Y'.
 *
 * Catatan: penerbitan rekening di sini memakai rumus dan nomor rekening
 * yang sama dengan Billing\Tagihan::simpan_pemakaian(), termasuk
 * pengecualian bea admin Hankam (id_jenis 13 = 35.000).
 */
class CatatMeter extends BaseController
{
    var $title;

    public function __construct()
    {
        $this->title = 'Catat Meter';
    }

    public function index()
    {
        return view('billing/catat_meter/main', ['title' => $this->title]);
    }

    /**
     * Daftar petugas pencatat (level mobileloket).
     */
    public function petugas()
    {
        $rows = $this->db->table('users')
            ->select('id_session, nama_lengkap')
            ->whereIn('level', ['mobileloket', 'loket', 'fastpay'])
            ->where('blokir', 'N')
            ->orderBy('nama_lengkap', 'ASC')
            ->get()
            ->getResultArray();

        $list = [['id' => 'all', 'text' => 'SEMUA PETUGAS']];

        foreach ($rows as $row) {
            $list[] = ['id' => $row['id_session'], 'text' => strtoupper($row['nama_lengkap'])];
        }

        return $this->response->setJSON($list);
    }

    /**
     * Data tabel catat meter per periode.
     */
    public function datatable()
    {
        $bulan     = (int) $this->request->getVar('bulan');
        $tahun     = (int) $this->request->getVar('tahun');
        $petugas   = (string) ($this->request->getVar('petugas') ?? 'all');
        $verifikasi = (string) ($this->request->getVar('verifikasi') ?? 'all');

        $builder = $this->db->table('t_catat_meter c')
            ->select('
                c.id,
                c.id_pelanggan,
                c.tgl_catat,
                c.angka_meter,
                c.foto_meter,
                c.foto_segel,
                c.foto_rumah,
                c.kondisi_meter,
                c.kondisi_segel,
                c.status_rekening,
                c.ada_meter,
                c.verifikator,
                p.no_sambungan,
                p.nama_pelanggan,
                j.kd_jenis,
                j.jenis_pelanggan,
                k.kecamatan,
                kl.kelurahan,
                u.nama_lengkap AS nama_petugas
            ')
            ->join('m_pelanggan p', 'p.id_pelanggan = c.id_pelanggan', 'left')
            ->join('m_jenis_pelanggan j', 'j.id_jenis = p.id_jenis', 'left')
            ->join('t_kecamatan k', 'k.id_kec = p.id_kec', 'left')
            ->join('t_kelurahan kl', 'kl.id_kelurahan = p.id_kelurahan', 'left')
            ->join('users u', 'u.id_session = c.id_session', 'left')
            ->where('MONTH(c.tgl_catat)', $bulan, false)
            ->where('YEAR(c.tgl_catat)', $tahun, false);

        if ($petugas !== 'all' && $petugas !== '') {
            $builder->where('c.id_session', $petugas);
        }

        if ($verifikasi === 'Y' || $verifikasi === 'N') {
            $builder->where('c.status_rekening', $verifikasi);
        }

        // Kunci unik untuk paginasi stabil.
        $builder->orderBy('c.status_rekening', 'ASC');
        $builder->orderBy('c.tgl_catat', 'DESC');
        $builder->orderBy('c.id', 'ASC');

        return DataTable::of($builder)
            ->add('periode', function ($row) {
                return $row->tgl_catat ? date('F Y', strtotime($row->tgl_catat)) : '-';
            })
            ->add('status', function ($row) {
                return $row->status_rekening === 'Y'
                    ? '<span class="badge bg-success">Terverifikasi</span>'
                    : '<span class="badge bg-danger">Belum Diverifikasi</span>';
            })
            ->add('aksi', function ($row) {
                $id = (int) $row->id;

                if ($row->status_rekening === 'Y') {
                    return '<button type="button" class="btn btn-sm btn-outline-info" '
                        . 'onclick="cekRekening(' . (int) $row->id_pelanggan . ')">'
                        . '<i class="fa fa-search"></i> Cek Rekening</button>';
                }

                return '<button type="button" class="btn btn-sm btn-outline-primary" '
                    . 'onclick="bukaVerifikasi(' . $id . ')">'
                    . '<i class="fa fa-check"></i> Verifikasi</button> '
                    . '<button type="button" class="btn btn-sm btn-outline-danger" '
                    . 'onclick="hapusCatat(' . $id . ')">'
                    . '<i class="fa fa-trash"></i></button>';
            })
            ->toJson(true);
    }

    /**
     * Detail satu baris catat meter untuk modal verifikasi.
     */
    public function detail()
    {
        $id = (int) $this->request->getVar('id');

        $row = $this->db->table('t_catat_meter c')
            ->select('
                c.*,
                p.no_sambungan,
                p.nama_pelanggan,
                p.id_jasa,
                p.id_jenis,
                p.id_kec,
                p.id_kelurahan,
                p.alamat,
                m_jasa.tarif_bawah_10,
                m_jasa.tarif_atas_10,
                m_jasa.bea_admin,
                m_jenis_pelanggan.jenis_pelanggan,
                m_jenis_pelanggan.kd_jenis,
                k.kecamatan,
                kl.kelurahan,
                u.nama_lengkap AS nama_petugas
            ')
            ->join('m_pelanggan p', 'p.id_pelanggan = c.id_pelanggan', 'left')
            ->join('m_jasa', 'm_jasa.id_jasa = p.id_jasa', 'left')
            ->join('m_jenis_pelanggan', 'm_jenis_pelanggan.id_jenis = p.id_jenis', 'left')
            ->join('t_kecamatan k', 'k.id_kec = p.id_kec', 'left')
            ->join('t_kelurahan kl', 'kl.id_kelurahan = p.id_kelurahan', 'left')
            ->join('users u', 'u.id_session = c.id_session', 'left')
            ->where('c.id', $id)
            ->get()
            ->getRowArray();

        if (! $row) {
            return $this->response->setJSON(['status' => 'error', 'msg' => 'Data catat meter tidak ditemukan.']);
        }

        if ($row['status_rekening'] === 'Y') {
            return $this->response->setJSON(['status' => 'error', 'msg' => 'Catat meter ini sudah diverifikasi.']);
        }

        // Meter lalu: angka meter terakhir sebelum tanggal catat ini.
        $meterLalu = $this->db->table('t_catat_meter')
            ->select('angka_meter')
            ->where('id_pelanggan', $row['id_pelanggan'])
            ->where('tgl_catat <', $row['tgl_catat'])
            ->orderBy('tgl_catat', 'DESC')
            ->orderBy('id', 'DESC')
            ->limit(1)
            ->get()
            ->getRowArray();

        $row['meter_lalu'] = (int) ($meterLalu['angka_meter'] ?? 0);
        $row['kubikasi']   = max(0, (int) $row['angka_meter'] - $row['meter_lalu']);

        // Pengecualian bea admin Hankam, sama seperti Tagihan::simpan_pemakaian().
        $row['bea_admin_efektif'] = (int) $row['id_jenis'] === 13
            ? 35000
            : (float) $row['bea_admin'];

        // Sudah ada rekening untuk periode ini?
        $ts = strtotime($row['tgl_catat']);
        $row['sudah_ada_rekening'] = $this->db->table('t_tagihan')
            ->where('id_pelanggan', $row['id_pelanggan'])
            ->where('MONTH(bulan_tagihan)', (int) date('n', $ts), false)
            ->where('YEAR(bulan_tagihan)', (int) date('Y', $ts), false)
            ->countAllResults() > 0;

        return $this->response->setJSON(['status' => 'success', 'data' => $row]);
    }

    /**
     * Verifikasi: terbitkan rekening dari catat meter, lalu tandai selesai.
     */
    public function verifikasi()
    {
        $id = (int) $this->request->getVar('id');

        $row = $this->db->table('t_catat_meter c')
            ->select('
                c.id, c.id_pelanggan, c.tgl_catat, c.angka_meter, c.status_rekening,
                p.id_jasa, p.id_jenis, p.id_kab, p.id_kec, p.id_kelurahan,
                p.nama_pelanggan,
                m_jasa.tarif_bawah_10, m_jasa.tarif_atas_10, m_jasa.bea_admin
            ')
            ->join('m_pelanggan p', 'p.id_pelanggan = c.id_pelanggan', 'left')
            ->join('m_jasa', 'm_jasa.id_jasa = p.id_jasa', 'left')
            ->where('c.id', $id)
            ->get()
            ->getRowArray();

        if (! $row) {
            return $this->response->setJSON(['status' => 'error', 'msg' => 'Data tidak ditemukan.']);
        }

        if ($row['status_rekening'] === 'Y') {
            return $this->response->setJSON(['status' => 'error', 'msg' => 'Sudah diverifikasi sebelumnya.']);
        }

        // Cegah dua permintaan bersamaan mengubah baris yang sama:
        // kunci barisnya dulu di dalam transaksi.
        $this->db->transStart();

        $kunci = $this->db->query(
            'SELECT status_rekening FROM t_catat_meter WHERE id = ? FOR UPDATE',
            [$id]
        )->getRowArray();

        if (! $kunci || $kunci['status_rekening'] === 'Y') {
            $this->db->transRollback();

            return $this->response->setJSON([
                'status' => 'error',
                'msg'    => 'Catat meter ini sudah diverifikasi oleh proses lain.',
            ]);
        }

        $ts    = strtotime($row['tgl_catat']);
        $bulan = (int) date('n', $ts);
        $tahun = (int) date('Y', $ts);

        $sudahAda = $this->db->table('t_tagihan')
            ->where('id_pelanggan', $row['id_pelanggan'])
            ->where('MONTH(bulan_tagihan)', $bulan, false)
            ->where('YEAR(bulan_tagihan)', $tahun, false)
            ->countAllResults() > 0;

        if ($sudahAda) {
            return $this->response->setJSON([
                'status' => 'error',
                'msg'    => 'Rekening ' . $row['nama_pelanggan'] . ' untuk periode ini sudah ada.',
            ]);
        }

        $meterLalu = $this->db->table('t_catat_meter')
            ->select('angka_meter')
            ->where('id_pelanggan', $row['id_pelanggan'])
            ->where('tgl_catat <', $row['tgl_catat'])
            ->orderBy('tgl_catat', 'DESC')
            ->orderBy('id', 'DESC')
            ->limit(1)
            ->get()
            ->getRowArray();

        $meterIni  = (int) $row['angka_meter'];
        $meterLalu = (int) ($meterLalu['angka_meter'] ?? 0);

        if ($meterIni < $meterLalu) {
            return $this->response->setJSON([
                'status' => 'error',
                'msg'    => 'Angka meter (' . $meterIni . ') lebih kecil dari meter lalu (' . $meterLalu . ').',
            ]);
        }

        $tarifBawah = (float) $row['tarif_bawah_10'];
        $tarifAtas  = (float) $row['tarif_atas_10'];
        $beaAdmin   = (int) $row['id_jenis'] === 13 ? 35000 : (float) $row['bea_admin'];

        $pemakaian = $meterIni - $meterLalu;
        $tagBawah  = 10 * $tarifBawah;
        $tagAtas   = $pemakaian > 10 ? ($pemakaian - 10) * $tarifAtas : 0;
        $jumlah    = $tagBawah + $tagAtas + $beaAdmin;

        $noRek = $this->nomorRekening($bulan, $tahun);

        $ok = $this->db->table('t_tagihan')->insert([
            'no_rek'             => $noRek,
            'id_pelanggan'       => $row['id_pelanggan'],
            'id_kab'             => $row['id_kab'],
            'id_kec'             => $row['id_kec'],
            'id_kelurahan'       => $row['id_kelurahan'],
            'id_jasa'            => $row['id_jasa'],
            'id_jenis'           => $row['id_jenis'],
            'tarif_dibawah_10'   => $tarifBawah,
            'tarif_diatas_10'    => $tarifAtas,
            'bulan_tagihan'      => date('Y-m-d', $ts),
            'pemakaian_air'      => $meterIni,
            'pemakaian_air_lalu' => $meterLalu,
            'tag_dibawah_10'     => $tagBawah,
            'tag_diatas_10'      => $tagAtas,
            'by_admin'           => $beaAdmin,
            'jlh_tagihan'        => $jumlah,
            'denda'              => 0,
            'bayar'              => 0,
            'status'             => '0',
            'aktif'              => '1',
            'id_user'            => session('nama_lengkap'),
        ]);

        if ($ok) {
            $this->db->table('t_catat_meter')
                ->where('id', $id)
                ->update([
                    'status_rekening' => 'Y',
                    'verifikator'     => session('id_session'),
                ]);
        }

        $this->db->transComplete();

        if (! $ok || $this->db->transStatus() === false) {
            return $this->response->setJSON(['status' => 'error', 'msg' => 'Verifikasi gagal, tidak ada yang berubah.']);
        }

        return $this->response->setJSON([
            'status'  => 'success',
            'msg'     => 'Rekening ' . $noRek . ' diterbitkan.',
            'no_rek'  => $noRek,
            'tagihan' => $jumlah,
        ]);
    }

    /**
     * Hapus satu baris catat meter (hanya yang belum diverifikasi).
     */
    public function hapus()
    {
        $id = (int) $this->request->getVar('id');

        $row = $this->db->table('t_catat_meter')
            ->select('status_rekening')
            ->where('id', $id)
            ->get()
            ->getRowArray();

        if (! $row) {
            return $this->response->setJSON(['status' => 'error', 'msg' => 'Data tidak ditemukan.']);
        }

        if ($row['status_rekening'] === 'Y') {
            return $this->response->setJSON([
                'status' => 'error',
                'msg'    => 'Catat meter yang sudah diverifikasi tidak bisa dihapus.',
            ]);
        }

        $ok = $this->db->table('t_catat_meter')->where('id', $id)->delete();

        return $this->response->setJSON([
            'status' => $ok ? 'success' : 'error',
            'msg'    => $ok ? 'Data catat meter dihapus.' : 'Gagal menghapus.',
        ]);
    }

    /**
     * Rekening satu pelanggan (untuk tombol "Cek Rekening").
     */
    public function rekening()
    {
        $idPelanggan = (int) $this->request->getVar('id_pelanggan');

        $rows = $this->db->table('t_tagihan')
            ->select('no_rek, bulan_tagihan, jlh_tagihan, status, tgl_bayar')
            ->where('id_pelanggan', $idPelanggan)
            ->orderBy('bulan_tagihan', 'DESC')
            ->limit(12)
            ->get()
            ->getResultArray();

        return $this->response->setJSON([
            'status' => 'success',
            'html'   => view('billing/catat_meter/modal/rekening', ['rows' => $rows]),
        ]);
    }

    // =================================================================
    // BANTUAN
    // =================================================================

    /**
     * Nomor rekening urut per periode, sama dengan Billing\Tagihan.
     */
    private function nomorRekening(int $bulan, int $tahun): string
    {
        $row = $this->db->table('t_tagihan')
            ->select('MAX(CAST(no_rek AS UNSIGNED)) AS terakhir', false)
            ->where('MONTH(bulan_tagihan)', $bulan, false)
            ->where('YEAR(bulan_tagihan)', $tahun, false)
            ->get()
            ->getRowArray();

        return str_pad((string) ((int) ($row['terakhir'] ?? 0) + 1), 4, '0', STR_PAD_LEFT);
    }
}
