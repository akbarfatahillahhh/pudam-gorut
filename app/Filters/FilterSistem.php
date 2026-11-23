<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Membatasi akses per sistem (billing / kas) dan per modul admin.
 *
 * Dipasang sebagai filter global (lihat App\Config\Filters: filterSistem).
 * Path yang tidak terikat sistem (mis. /Home/get_sidebar, /isSession,
 * asset) dibiarkan lewat — autentikasi tetap ditangani FilterAdmin.
 *
 * Filter ini juga mengingat sistem yang sedang dibuka di session, supaya
 * halaman non-modul tetap memakai sidebar sistem yang benar.
 */
class FilterSistem implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        if (empty(session()->username)) {
            // Biar FilterAdmin yang mengurus redirect ke /login.
            return;
        }

        $path   = $request->getUri()->getPath();
        $sistem = sistem_dari_path($path);

        if (!sistem_boleh_path($path)) {
            session()->setFlashdata('error', 'Anda tidak punya akses ke modul tersebut.');

            // Tujuan redirect harus sistem yang memang boleh dibuka user.
            // Kalau memakai sistem_aktif() apa adanya, user tanpa akses
            // E-Kas yang membuka /ekas akan dipantulkan ke /ekas lagi
            // (redirect loop).
            $tujuan = sistem_aktif();

            if (!sistem_boleh($tujuan)) {
                $tujuan = sistem_akses_user()[0] ?? null;
            }

            if ($tujuan === null) {
                // Tidak punya akses ke sistem mana pun: jangan biarkan
                // session hidup tanpa halaman tujuan.
                session()->destroy();

                return redirect()->to('/login');
            }

            return redirect()->to(sistem_home_url($tujuan));
        }

        // Ingat sistem terakhir yang dibuka user. Modul admin yang tidak
        // terikat sistem (/master/akun) mempertahankan sistem yang sudah
        // tersimpan, jadi sidebar tidak pindah ke billing.
        if ($sistem !== null && session('sistem_aktif') !== $sistem) {
            session()->set('sistem_aktif', $sistem);
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Tidak ada yang perlu diubah pada response.
    }
}
