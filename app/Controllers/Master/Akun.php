<?php

namespace App\Controllers\Master;

use App\Controllers\BaseController;
use Hermawan\DataTables\DataTable;

class Akun extends BaseController
{
    var $title;
    public function __construct()
    {
        $this->title = "Akun";
    }

    public function index()
    {
        $data['title'] = $this->title;
        return view('/master/akun/main', $data);
    }

    public function datatable()
    {
        $builder = $this->db->table('users');
        $builder->select('
            users.username, 
            users.nama_lengkap,
            users.level,
            users.blokir,
        ');

        DataTable::of($builder)
            ->add('blokir', function ($data) {})
            ->toJson(true);
    }

    public function new()
    {
        $data['title'] = $this->title;
        $html = ['html' => view('/master/akun/modal/new', $data)];
        echo json_encode($html);
    }

    public function edit()
    {
        $data['title'] = $this->title;
        $data['id'] = $this->request->getPost('id');
        $data['data'] = $this->db->table('users')->where('id', $data['id'])->get()->getRowArray();

        $html = ['html' => view('/master/akun/modal/edit', $data)];
        echo json_encode($html);
    }

    public function save()
    {
        $data = $this->request->getPost();
        $this->db->table('users')->insert($data);

        $msg = [
            'status' => 'success',
            'msg' => 'Data berhasil disimpan'
        ];

        echo json_encode($msg);
    }
}
