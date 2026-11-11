<?php

namespace App\Controllers\Master;

use App\Controllers\BaseController;
use Hermawan\DataTables\DataTable;

class Perkiraan extends BaseController
{
    var $title;
    public function __construct()
    {
        $this->title = 'Perkiraan';
    }

    public function index()
    {
        $data['title'] = $this->title;
        return view('/master/perkiraan/main', $data);
    }

    public function data()
    {
        $id = $this->request->getVar('id');
        $search = $this->request->getVar('search');
        $query = $this->db
            ->table('akun_perkiraan')
            ->select(
                '
                    akun_perkiraan.ID,
                    akun_perkiraan.NOMOR,
                    akun_perkiraan.NAMA_PERKIRAAN,
                    akun_perkiraan.ID_GROP_PERKIRAAN,
                    akun_perkiraan.STATUS,
                    akun_perkiraan.NORMAL,

                    akun_perkiraan_group.GROUP_PERKIRAAN
                ',
            );

        if ($id != "") {
            $query->where('akun_perkiraan.ID_GROP_PERKIRAAN', $id);
        }

        if ($search != "") {
            $query->like('akun_perkiraan.NAMA_PERKIRAAN', $search);
        }

        $result = $query->join('akun_perkiraan_group', 'akun_perkiraan_group.ID = akun_perkiraan.ID_GROP_PERKIRAAN')
            ->orderBy('akun_perkiraan.ID_GROP_PERKIRAAN', 'DESC')
            ->get()
            ->getResultArray();

        echo json_encode($result);
    }

    public function new()
    {
        $data['title'] = $this->title;
        $html = ['html' => view('/master/perkiraan/modal/new', $data)];
        echo json_encode($html);
    }
    public function edit()
    {
        $id = $this->request->getVar('id');

        $query = $this->db
            ->table('akun_perkiraan')
            ->select(
                '
                    akun_perkiraan.ID,
                    akun_perkiraan.NOMOR,
                    akun_perkiraan.NAMA_PERKIRAAN,
                    akun_perkiraan.ID_GROP_PERKIRAAN,
                    akun_perkiraan.STATUS,
                    akun_perkiraan.NORMAL,

                    akun_perkiraan_group.GROUP_PERKIRAAN,
                    akun_perkiraan_group.NO_PERKIRAAN
                ',
            )
            ->join('akun_perkiraan_group', 'akun_perkiraan_group.ID = akun_perkiraan.ID_GROP_PERKIRAAN')
            ->orderBy('akun_perkiraan.ID_GROP_PERKIRAAN', 'DESC')
            ->where('akun_perkiraan.ID', $id)
            ->get()
            ->getRow();

        $data['title'] = $this->title;
        if ($query) {
            $data['id_perkiraan'] = $query->ID;
            $data['id_grop_perkiraan'] = $query->ID_GROP_PERKIRAAN;
            $data['grop_perkiraan'] = $query->NO_PERKIRAAN . ' | ' . $query->GROUP_PERKIRAAN;
            $data['nomor'] = $query->NOMOR;
            $data['nama_perkiraan'] = $query->NAMA_PERKIRAAN;
            $data['normal'] = $query->NORMAL;
            $data['status'] = $query->STATUS;
        }
        $html = ['html' => view('/master/perkiraan/modal/edit', $data)];
        echo json_encode($html);
    }
    public function master()
    {
        $data = $this->request->getPost();

        if (isset($data['ID']) && isset($data['NOMOR']) && isset($data['NORMAL']) && isset($data['STATUS'])) {
            $query = $this->db
                ->table('akun_perkiraan')
                ->where('ID', $data['ID'])
                ->update($data);
        } elseif (isset($data['ID'])) {
            $query = $this->db
                ->table('akun_perkiraan')
                ->where('ID', $data['ID'])
                ->delete();
        } else {
            $query = $this->db->table('akun_perkiraan')->insert($data);
        }

        if ($query) {
            $msg = [
                'status' => 'success',
                'msg' => 'Data berhasil disimpan',
            ];
        } else {
            $msg = [
                'status' => 'error',
                'msg' => 'Data gagal disimpan',
            ];
        }
        echo json_encode($msg);
    }

    public function export_pdf()
    {
        $data['title'] = $this->title;
        $html = ['html' => view('/master/perkiraan/modal/export_pdf', $data)];
        echo json_encode($html);
    }
}
