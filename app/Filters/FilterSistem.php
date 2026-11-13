<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Membatasi akses per sistem (billing / kas / admin).
 *
 * Dipasang sebagai filter global (lihat App\Config\Filters: filterSistem).
 * Path yang tidak terikat sistem (mis. /Home/get_sidebar, /isSession,
 * asset) dibiarkan lewat — autentikasi tetap ditangani FilterAdmin.
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

        if ($sistem === null) {
            return;
        }

        if (sistem_boleh($sistem)) {
            return;
        }

        // Tidak berhak: lempar ke dashboard sistem yang boleh dibuka.
        $tujuan = sistem_akses_user()[0] ?? SISTEM_BILLING;

        session()->setFlashdata('error', 'Anda tidak punya akses ke modul tersebut.');

        return redirect()->to(sistem_home_url($tujuan));
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Tidak ada yang perlu diubah pada response.
    }
}
