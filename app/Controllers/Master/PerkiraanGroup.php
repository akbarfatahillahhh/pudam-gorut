<?php

namespace App\Controllers\Master;

use App\Controllers\BaseController;
use Hermawan\DataTables\DataTable;

/**
 * Grup Perkiraan (tabel `akun_perkiraan_group`).
 *
 * Satu grup menaungi banyak `akun_perkiraan` lewat
 * akun_perkiraan.ID_GROP_PERKIRAAN, dan dipakai juga sebagai PERKIRAAN_ID
 * di `akun_kas_keluar`. Karena itu grup yang sudah dipakai tidak boleh
 * dihapus (lihat hapus()).
 */
class PerkiraanGroup extends BaseController
{
    var $title;

    public function __construct()
    {
        $this->title = 'Grup Perkiraan';
    }

    public function index()
    {
        $data['title'] = $this->title;

        return view('master/perkiraan_group/main', $data);
    }

    public function data()
    {
        $builder = $this->db->table('akun_perkiraan_group')
            ->select('
                akun_perkiraan_group.ID,
                akun_perkiraan_group.GROUP_PERKIRAAN,
                akun_perkiraan_group.NO_PERKIRAAN,
                akun_perkiraan_group.NORMAL,
                akun_perkiraan_group.STATUS
            ');

        return DataTable::of($builder)
            ->add('NORMAL', function ($row) {
                return $row->NORMAL === 'Debet'
                    ? '<span class="badge bg-primary">Debet</span>'
                    : '<span class="badge bg-warning text-dark">Kredit</span>';
            })
            ->add('STATUS', function ($row) {
                return $row->STATUS === 'Biaya'
                    ? '<span class="badge bg-danger">Biaya</span>'
                    : '<span class="badge bg-success">Pendapatan</span>';
            })
            ->add('aksi', function ($row) {
                return "
                <div class='dropdown'>
                    <button class='btn btn-sm' type='button' data-bs-toggle='dropdown' aria-expanded='false'>
                        <i class='fa fa-ellipsis-v text-black'></i>
                    </button>
                    <ul class='dropdown-menu border border-dark py-0'>
                        <li>
                            <a class='dropdown-item text-black' href='#' onclick='edit({$row->ID})'>
                                <i class='fa-solid fa-pen-to-square text-warning pe-1'></i> Edit
                            </a>
                        </li>
                        <li>
                            <a class='dropdown-item text-black' href='#' onclick='hapus({$row->ID})'>
                                <i class='fa-solid fa-trash text-danger pe-1'></i> Hapus
                            </a>
                        </li>
                    </ul>
                </div>";
            })
            ->toJson(true);
    }

    public function new()
    {
        $data['title'] = $this->title;
        $html = ['html' => view('master/perkiraan_group/modal/new', $data)];

        return $this->response->setJSON($html);
    }

    public function edit()
    {
        $id = $this->request->getVar('id');

        $query = $this->db->table('akun_perkiraan_group')->where('ID', $id)->get()->getRow();

        if (! $query) {
            return $this->response->setJSON([
                'status' => 'error',
                'msg'    => 'Grup perkiraan tidak ditemukan',
            ]);
        }

        $data = [
            'title'        => $this->title,
            'ID'           => $query->ID,
            'GROUP_PERKIRAAN' => $query->GROUP_PERKIRAAN,
            'NO_PERKIRAAN' => $query->NO_PERKIRAAN,
            'NORMAL'       => $query->NORMAL,
            'STATUS'       => $query->STATUS,
        ];

        return $this->response->setJSON([
            'html' => view('master/perkiraan_group/modal/edit', $data),
        ]);
    }

    /**
     * Simpan (insert kalau tanpa ID, update kalau ada ID).
     */
    public function master()
    {
        $post = $this->request->getPost();

        $data = [
            'GROUP_PERKIRAAN' => trim((string) ($post['GROUP_PERKIRAAN'] ?? '')),
            'NO_PERKIRAAN'    => trim((string) ($post['NO_PERKIRAAN'] ?? '')),
            'NORMAL'          => (string) ($post['NORMAL'] ?? ''),
            'STATUS'          => (string) ($post['STATUS'] ?? ''),
        ];

        $id = isset($post['ID']) && $post['ID'] !== '' ? (int) $post['ID'] : null;

        $validasi = $this->validasi($data, $id);
        if ($validasi !== null) {
            return $this->response->setJSON(['status' => 'error', 'msg' => $validasi]);
        }

        $table = $this->db->table('akun_perkiraan_group');

        if ($id === null) {
            $ok = $table->insert($data);
        } else {
            $ok = $table->where('ID', $id)->update($data);
        }

        return $this->response->setJSON([
            'status' => $ok ? 'success' : 'error',
            'msg'    => $ok ? 'Data berhasil disimpan' : 'Data gagal disimpan',
        ]);
    }

    /**
     * Hapus grup. Ditolak kalau masih punya akun_perkiraan di bawahnya.
     *
     * Catatan: akun_kas_keluar.PERKIRAAN_ID menyimpan akun_perkiraan.ID,
     * bukan ID grup (lihat EntryKas_Keluar), jadi tabel itu tidak perlu
     * dicek di sini — kalau perkiraannya terpakai, grupnya sudah terhalang
     * oleh pengecekan akun_perkiraan di atas.
     */
    public function hapus()
    {
        $id = (int) $this->request->getPost('ID');

        if ($id <= 0) {
            return $this->response->setJSON(['status' => 'error', 'msg' => 'ID grup tidak valid']);
        }

        $dipakaiPerkiraan = $this->db->table('akun_perkiraan')
            ->where('ID_GROP_PERKIRAAN', $id)
            ->countAllResults();

        if ($dipakaiPerkiraan > 0) {
            return $this->response->setJSON([
                'status' => 'error',
                'msg'    => "Grup tidak bisa dihapus: masih dipakai oleh {$dipakaiPerkiraan} perkiraan.",
            ]);
        }

        $ok = $this->db->table('akun_perkiraan_group')->where('ID', $id)->delete();

        return $this->response->setJSON([
            'status' => $ok ? 'success' : 'error',
            'msg'    => $ok ? 'Data berhasil dihapus' : 'Data gagal dihapus',
        ]);
    }

    /**
     * @return string|null pesan error, atau null kalau valid
     */
    private function validasi(array $data, ?int $id): ?string
    {
        if ($data['GROUP_PERKIRAAN'] === '') {
            return 'Nama grup perkiraan wajib diisi.';
        }

        if ($data['NO_PERKIRAAN'] === '') {
            return 'Nomor perkiraan wajib diisi.';
        }

        if (! in_array($data['NORMAL'], ['Debet', 'Kredit'], true)) {
            return 'Normal harus Debet atau Kredit.';
        }

        if (! in_array($data['STATUS'], ['Biaya', 'Pendapatan'], true)) {
            return 'Status harus Biaya atau Pendapatan.';
        }

        // Nomor perkiraan grup dipakai sebagai kode akun: jangan dobel.
        $cek = $this->db->table('akun_perkiraan_group')
            ->where('NO_PERKIRAAN', $data['NO_PERKIRAAN']);

        if ($id !== null) {
            $cek->where('ID !=', $id);
        }

        if ($cek->countAllResults() > 0) {
            return 'Nomor perkiraan ' . $data['NO_PERKIRAAN'] . ' sudah dipakai grup lain.';
        }

        return null;
    }
}
