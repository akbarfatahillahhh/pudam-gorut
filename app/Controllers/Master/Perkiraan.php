<?php

namespace App\Controllers\Master;

use App\Controllers\BaseController;
use Hermawan\DataTables\DataTable;

class Perkiraan extends BaseController
{
    var $title;
    public function __construct()
    {
        $this->title = "Perkiraan";
    }

    public function index()
    {
        return view('/master/perkiraan/main');
    }

    public function data()
    {
        $query = $this->db->table('akun_perkiraan')
            ->select('
                akun_perkiraan.ID,
                akun_perkiraan.NOMOR,
                akun_perkiraan.NAMA_PERKIRAAN,
                akun_perkiraan.ID_GROP_PERKIRAAN,
                akun_perkiraan.STATUS,
                akun_perkiraan.NORMAL,

                akun_perkiraan_group.GROUP_PERKIRAAN
            ')
            ->join('akun_perkiraan_group', 'akun_perkiraan_group.ID = akun_perkiraan.ID_GROP_PERKIRAAN')
            ->orderBy('akun_perkiraan.ID_GROP_PERKIRAAN', 'DESC')
            ->get()
            ->getResultArray();

        echo json_encode($query);
    }

    public function new()
    {
        $data['title'] = $this->title;
        $html = ['html' => view('/master/perkiraan/modal/new', $data)];
        echo json_encode($html);
    }
}
