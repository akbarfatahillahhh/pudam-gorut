<?php

namespace App\Controllers\Master;

use App\Controllers\BaseController;
use Hermawan\DataTables\DataTable;

/**
 * Hak Akses Modul: mengatur sistem (Billing / E-Kas) dan modul di dalamnya
 * untuk tiap user.
 *
 * Dua lapis:
 *   - t_akses_sistem : sistem mana yang boleh dibuka user
 *   - t_akses_modul  : modul mana di dalam sistem itu (lihat
 *                      sistem_akses_modul(); tidak ada baris = semua modul)
 *
 * Daftar modul yang bisa diatur berasal dari sistem_modul(), yang membaca
 * kunci 'modul' pada menu di sistem_menu(). Grup menu bertanda admin tidak
 * ikut: Data Master / Administrasi tetap hanya untuk level admin.
 */
class AksesModul extends BaseController
{
    var $title;

    public function __construct()
    {
        $this->title = 'Hak Akses Modul';
    }

    public function index()
    {
        $data['title'] = $this->title;

        return view('master/akses/main', $data);
    }

    public function data()
    {
        $builder = $this->db->table('users')
            ->select('users.id_session, users.nama_lengkap, users.username, users.level, users.blokir')
            ->orderBy('users.level', 'ASC')
            ->orderBy('users.nama_lengkap', 'ASC');

        // Peta akses diambil sekali, lalu dipakai di closure. 97 user,
        // jadi lebih murah daripada subquery per baris.
        $sistemMap = [];
        $modulMap  = [];

        foreach ($this->db->table('t_akses_sistem')->select('id_session, sistem')->get()->getResultArray() as $r) {
            $sistemMap[$r['id_session']][] = $r['sistem'];
        }

        foreach ($this->db->table('t_akses_modul')->select('id_session, sistem, modul')->get()->getResultArray() as $r) {
            $modulMap[$r['id_session']][$r['sistem']][] = $r['modul'];
        }

        $daftarSistem = sistem_daftar();

        return DataTable::of($builder)
            ->add('sistem', function ($row) use ($sistemMap, $daftarSistem) {
                if ($row->level === 'admin') {
                    return '<span class="badge bg-dark fs-1 rounded">Semua sistem (admin)</span>';
                }

                $kode = $sistemMap[$row->id_session] ?? [];
                $kode = array_values(array_filter($kode, static function ($s) use ($daftarSistem) {
                    return isset($daftarSistem[$s]);
                }));

                if (empty($kode)) {
                    // Tanpa baris: fallback level (lihat sistem_akses_user()).
                    $kode = sistem_akses_default($row->level);

                    return '<span class="badge bg-secondary fs-1 rounded">Bawaan: '
                        . implode(' + ', array_map(static fn ($s) => esc($daftarSistem[$s]['label'] ?? $s), $kode))
                        . '</span>';
                }

                $badge = '';
                foreach ($kode as $s) {
                    $badge .= '<span class="badge bg-success fs-1 rounded me-1">'
                        . esc($daftarSistem[$s]['label'] ?? $s) . '</span>';
                }

                return $badge;
            })
            ->add('modul', function ($row) use ($modulMap, $daftarSistem) {
                if ($row->level === 'admin') {
                    return '<span class="text-muted fs-2">tidak terbatas</span>';
                }

                $per = $modulMap[$row->id_session] ?? [];

                if (empty($per)) {
                    return '<span class="text-muted fs-2">Semua modul</span>';
                }

                $daftarModul = sistem_modul();
                $html        = '';

                foreach ($per as $sistem => $modul) {
                    if (! isset($daftarSistem[$sistem])) {
                        continue;
                    }

                    $total = 0;
                    foreach (sistem_modul_per_sistem($sistem) as $items) {
                        $total += count($items);
                    }

                    // Buang modul yang sudah tidak terdaftar (menu dihapus
                    // / kode diganti) supaya hitungannya jujur.
                    $dibatasi = array_values(array_filter($modul, static function ($m) use ($daftarModul, $sistem) {
                        return isset($daftarModul[$m]) && $daftarModul[$m]['sistem'] === $sistem;
                    }));

                    $html .= '<div class="fs-2">'
                        . '<span class="badge bg-primary rounded">' . esc($daftarSistem[$sistem]['label'] ?? $sistem) . '</span> '
                        . '<span class="fw-semibold">' . count($dibatasi) . ' dari ' . $total . ' modul</span>'
                        . '</div>';
                }

                return $html === '' ? '<span class="text-muted fs-2">Semua modul</span>' : $html;
            })
            ->add('akses', function ($row) {
                if ($row->level === 'admin') {
                    return '<span class="text-muted fs-2">—</span>';
                }

                return '<button type="button" class="btn btn-sm btn-outline-primary" '
                    . 'onclick="aturAkses(\'' . esc($row->id_session, 'attr') . '\')">'
                    . '<i class="fa fa-user-shield"></i> Atur</button>';
            })
            ->toJson(true);
    }

    /**
     * Form akses satu user.
     */
    public function edit()
    {
        $idSession = (string) $this->request->getPost('id_session');

        $user = $this->db->table('users')
            ->select('id_session, nama_lengkap, username, level')
            ->where('id_session', $idSession)
            ->get()
            ->getRowArray();

        if (! $user) {
            return $this->response->setJSON(['status' => 'error', 'msg' => 'User tidak ditemukan.']);
        }

        $sistem = $this->db->table('t_akses_sistem')
            ->select('sistem')
            ->where('id_session', $idSession)
            ->get()
            ->getResultArray();
        $sistem = array_column($sistem, 'sistem');

        $modul = $this->db->table('t_akses_modul')
            ->select('sistem, modul')
            ->where('id_session', $idSession)
            ->get()
            ->getResultArray();

        $modulPerSistem = [];
        foreach ($modul as $r) {
            $modulPerSistem[$r['sistem']][] = $r['modul'];
        }

        $data = [
            'title'         => $this->title,
            'user'          => $user,
            'sistem'        => $sistem,
            'modul'         => $modulPerSistem,
            'daftar_sistem' => sistem_daftar(),
            'modul_sistem'  => [],
        ];

        foreach (array_keys(sistem_daftar()) as $kode) {
            $data['modul_sistem'][$kode] = sistem_modul_per_sistem($kode);
        }

        return $this->response->setJSON([
            'html' => view('master/akses/modal/edit', $data),
        ]);
    }

    /**
     * Simpan akses sistem + modul satu user.
     */
    public function simpan()
    {
        $idSession = (string) $this->request->getPost('id_session');

        if ($idSession === '') {
            return $this->response->setJSON(['status' => 'error', 'msg' => 'User tidak valid.']);
        }

        $user = $this->db->table('users')
            ->select('id_session, level')
            ->where('id_session', $idSession)
            ->get()
            ->getRowArray();

        if (! $user) {
            return $this->response->setJSON(['status' => 'error', 'msg' => 'User tidak ditemukan.']);
        }

        if ($user['level'] === 'admin') {
            return $this->response->setJSON([
                'status' => 'error',
                'msg'    => 'Admin selalu punya akses ke semua sistem dan modul.',
            ]);
        }

        $daftarSistem = sistem_daftar();
        $daftarModul  = sistem_modul();

        $sistem = $this->request->getPost('sistem');
        $sistem = is_array($sistem) ? $sistem : [];
        $sistem = array_values(array_unique(array_filter($sistem, static function ($k) use ($daftarSistem) {
            return is_string($k) && isset($daftarSistem[$k]);
        })));

        $modulPost = $this->request->getPost('modul');
        $modulPost = is_array($modulPost) ? $modulPost : [];

        // Nol sistem TIDAK berarti "tanpa akses": sistem_akses_user()
        // menganggap user tanpa baris sebagai "belum diatur" dan memberi
        // akses bawaan level-nya. Jadi mencabut semua sistem di sini justru
        // memperluas akses. Untuk mematikan user, pakai blokir akun.
        if (empty($sistem)) {
            return $this->response->setJSON([
                'status' => 'error',
                'msg'    => 'Pilih minimal satu sistem. Untuk mematikan akses user sepenuhnya, blokir akunnya di menu User/Akun.',
            ]);
        }

        // Validasi: sistem yang dicentang wajib punya minimal satu modul.
        // Tanpa ini, "tidak ada baris" berarti "semua modul" dan admin
        // yang bermaksud mencabut semua modul justru memberi akses penuh.
        $modulBersih = [];

        foreach ($sistem as $kodeSistem) {
            // sistem_modul_per_sistem() sudah membuang dashboard ('selalu'),
            // jadi daftar ini memang modul yang bisa dicabut.
            $semuaModul = [];
            foreach (sistem_modul_per_sistem($kodeSistem) as $items) {
                foreach ($items as $it) {
                    $semuaModul[] = $it['modul'];
                }
            }

            $dipilih = array_values(array_unique(array_filter(
                (array) ($modulPost[$kodeSistem] ?? []),
                static function ($m) use ($daftarModul, $kodeSistem, $semuaModul) {
                    return is_string($m)
                        && isset($daftarModul[$m])
                        && $daftarModul[$m]['sistem'] === $kodeSistem
                        && in_array($m, $semuaModul, true);
                }
            )));

            if (empty($dipilih)) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'msg'    => 'Pilih minimal satu modul untuk sistem ' . ($daftarSistem[$kodeSistem]['label'] ?? $kodeSistem)
                        . ', atau lepas centang sistem itu.',
                ]);
            }

            // Semua modul dicentang = tidak dibatasi. Barisnya tidak disimpan
            // supaya modul baru yang ditambahkan nanti otomatis ikut.
            if (empty(array_diff($semuaModul, $dipilih))) {
                continue;
            }

            $modulBersih[$kodeSistem] = $dipilih;
        }

        $this->db->transStart();

        $this->db->table('t_akses_sistem')->where('id_session', $idSession)->delete();

        if (! empty($sistem)) {
            $rows = [];
            foreach ($sistem as $kode) {
                $rows[] = ['id_session' => $idSession, 'sistem' => $kode];
            }
            $this->db->table('t_akses_sistem')->insertBatch($rows);
        }

        $this->db->table('t_akses_modul')->where('id_session', $idSession)->delete();

        $rows = [];
        foreach ($modulBersih as $kodeSistem => $modul) {
            foreach ($modul as $m) {
                $rows[] = ['id_session' => $idSession, 'sistem' => $kodeSistem, 'modul' => $m];
            }
        }

        if (! empty($rows)) {
            $this->db->table('t_akses_modul')->insertBatch($rows);
        }

        $this->db->transComplete();

        if ($this->db->transStatus() === false) {
            return $this->response->setJSON([
                'status' => 'error',
                'msg'    => 'Akses gagal disimpan, tidak ada yang berubah.',
            ]);
        }

        return $this->response->setJSON([
            'status' => 'success',
            'msg'    => 'Hak akses berhasil disimpan.',
        ]);
    }
}
