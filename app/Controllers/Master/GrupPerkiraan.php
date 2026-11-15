<?php

namespace App\Controllers\Master;

use App\Controllers\BaseController;
use Hermawan\DataTables\DataTable;

class GrupPerkiraan extends BaseController
{
    var $title;
    public function __construct()
    {
        $this->title = 'Group Perkiraan';
    }

    public function index()
    {
        $data['title'] = $this->title;
        return view('/master/grup_perkiraan/main', $data);
    }

    public function datatable()
    {
        $table = $this->db->table('akun_perkiraan_group');

        return DataTable::of($table)
            ->add('aksi', function ($row) {
                return '<div class="dropdown">
             <button class="btn btn-sm" type="button" data-bs-toggle="dropdown" aria-expanded="false">
             <i class="fas fa-ellipsis-v text-black"></i>
             </button>
             <ul class="dropdown-menu py-0">
             <li>
             <a href="javascript:void(0)" class="dropdown-item text-black" onclick="edit(' . $row->ID . ')"><i class="fas fa-edit text-warning"></i> Edit</a>
             </li>
             <li>
             <a href="javascript:void(0)" class="dropdown-item text-black" onclick="hapus(' . $row->ID . ')"><i class="fas fa-trash text-danger"></i> Hapus</a>
             </li>
             </ul>
             </div>';
            })
            ->toJson(true);
    }

    public function new()
    {
        $data['title'] = $this->title;
        $html = ['html' => view('/master/grup_perkiraan/modal/new', $data)];
        echo json_encode($html);
    }

    public function edit()
    {
        $id = $this->request->getVar('id');

        $query = $this->db
            ->table('akun_perkiraan_group')
            ->select('ID, GROUP_PERKIRAAN, NO_PERKIRAAN, NORMAL, STATUS')
            ->where('ID', $id)
            ->get()
            ->getRow();

        $data['title'] = $this->title;
        if ($query) {
            $data['id'] = $query->ID;
            $data['grup_perkiraan'] = $query->GROUP_PERKIRAAN;
            $data['no_perkiraan'] = $query->NO_PERKIRAAN;
            $data['normal'] = $query->NORMAL;
            $data['status'] = $query->STATUS;
        }
        $html = ['html' => view('/master/grup_perkiraan/modal/edit', $data)];
        echo json_encode($html);
    }
    public function master()
    {
        $data = $this->request->getPost();

        if (isset($data['ID']) && isset($data['NO_PERKIRAAN']) && isset($data['NORMAL']) && isset($data['STATUS'])) {
            $query = $this->db
                ->table('akun_perkiraan_group')
                ->where('ID', $data['ID'])
                ->update($data);
        } elseif (isset($data['ID'])) {
            $query = $this->db
                ->table('akun_perkiraan_group')
                ->where('ID', $data['ID'])
                ->delete();
        } else {
            $query = $this->db->table('akun_perkiraan_group')->insert($data);
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
        $html = ['html' => view('/master/grup_perkiraan/modal/export_pdf', $data)];
        echo json_encode($html);
    }
}
