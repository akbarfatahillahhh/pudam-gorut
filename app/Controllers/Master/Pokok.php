<?php

namespace App\Controllers\Master;

use App\Controllers\BaseController;
use Hermawan\DataTables\DataTable;

class Pokok extends BaseController
{
    var $title;
    public function __construct()
    {
        $this->title = 'Tugas Pokok';
    }

    public function index()
    {
        $data['title'] = $this->title;
        return view('/master/pokok/main', $data);
    }

    public function data()
    {
        $jabatan = $this->request->getVar('jabatan');
        $search = $this->request->getVar('search');


        $query = $this->db->table('tugas_pokok');
        $query->select('tugas_pokok.ID, tugas_pokok.JABATAN_ID, tugas_pokok.TUGAS_POKOK, jabatan.JABATAN');
        $query->join('jabatan', 'jabatan.ID = tugas_pokok.JABATAN_ID', 'left');
        $query->orderBy('JABATAN_ID', 'desc');

        if ($jabatan != '') {
            $query->where('tugas_pokok.JABATAN_ID', $jabatan);
        }

        if ($search != '') {
            $query->like('tugas_pokok.TUGAS_POKOK', $search);
        }

        $result = $query->get()->getResultArray();

        return $this->response->setJSON($result);
    }

    public function edit()
    {
        $id = $this->request->getVar('id');

        $data['title'] = $this->title;

        $query = $this->db
            ->table('tugas_pokok')
            ->select(
                '
        tugas_pokok.ID,
        tugas_pokok.JABATAN_ID,
        tugas_pokok.TUGAS_POKOK,
        
        jabatan.JABATAN,
        ',
            )
            ->join('jabatan', 'jabatan.ID = tugas_pokok.JABATAN_ID', 'left')
            ->where('tugas_pokok.ID', $id)
            ->get()
            ->getRow();

        if ($query) {
            $data['id'] = $query->ID;
            $data['JABATAN_ID'] = $query->JABATAN_ID;
            $data['JABATAN'] = $query->JABATAN;
            $data['TUGAS_POKOK'] = $query->TUGAS_POKOK;
        }

        $html = ['html' => view('/master/pokok/modal/edit', $data)];
        echo json_encode($html);
    }
    public function new()
    {
        $data['title'] = $this->title;
        $data['KODE'] = $this->db->table('pegawai')->selectMax('KODE')->get()->getRow()->KODE + 1;
        $html = ['html' => view('/master/pokok/modal/new', $data)];
        echo json_encode($html);
    }

    public function master()
    {
        $data = $this->request->getPost();
        // var_dump($data);
        $query = $this->db->table('tugas_pokok');

        if (isset($data['id'])) {
            $query->where('ID', $data['id']);
            $query->update($data);
        } else {
            $query->insert($data);
        }
        echo json_encode(['status' => 'success', 'msg' => 'Data berhasil disimpan']);
    }
}
