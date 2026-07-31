<?php

namespace App\Controllers\Master;

use App\Controllers\BaseController;
use Hermawan\DataTables\DataTable;

class Bank extends BaseController
{
    var $title;
    public function __construct()
    {
        $this->title = "Bank";
    }

    public function index()
    {
        $data['title'] = $this->title;
        return view('/master/bank/main', $data);
    }

    public function datatable()
    {
        $table = $this->db->table('master_bank');

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
        $html = ['html' => view('/master/bank/modal/new', $data)];

        echo json_encode($html);
    }
    public function edit()
    {
        $id = $this->request->getVar('id');

        $table = $this->db->table('master_bank')->where('ID', $id)->get()->getRowArray();

        $data['title'] = $this->title;
        $data['ID_BANK'] = $id;
        $data['NAMA_BANK'] = $table['NAMA_BANK'];
        $html = ['html' => view('/master/bank/modal/edit', $data)];

        echo json_encode($html);
    }

    public function save()
    {
        $data = $this->request->getPost();

        $table = $this->db->table('master_bank');

        if (isset($data['ID'])) {
            $table->set($data)->where('ID', $data['ID'])->update();
        } else {
            $table->insert($data);
        }

        echo json_encode(['status' => true]);
    }

    public function delete()
    {
        $id = $this->request->getVar('id');
        $this->db->table('master_bank')->where('ID', $id)->delete();
        echo json_encode(['status' => true]);
    }
}
