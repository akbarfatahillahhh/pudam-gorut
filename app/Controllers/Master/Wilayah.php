<?php

namespace App\Controllers\Master;

use App\Controllers\BaseController;
use Hermawan\DataTables\DataTable;

class Wilayah extends BaseController
{
    var $title;
    var $subtitle;
    public function __construct()
    {
        $this->title = "Wilayah";
    }

    public function kab()
    {
        $data['title'] = $this->title;
        return view('/master/wilayah/kabupaten/main', $data);
    }
    public function kec()
    {
        $data['title'] = $this->title;
        return view('/master/wilayah/kecamatan/main', $data);
    }
    public function kel()
    {
        $data['title'] = $this->title;
        return view('/master/wilayah/kelurahan/main', $data);
    }

    public function kabupaten()
    {
        $query = $this->db->table('t_kabupaten')->select('t_kabupaten.kabupaten, t_kabupaten.id_kab');

      return DataTable::of($query)
            ->add('aksi', function ($data) {
                
                return '<div class="dropdown">
                    <button class="btn btn-sm" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fa fa-ellipsis-v text-black"></i>
                    </button>
                    <ul class="dropdown-menu border border-dark py-0">
                        <li>
                            <a class="dropdown-item text-black" href="#" onclick="edit(' . $data->id_kab . ')">
                                <i class="fa-solid fa-pen-to-square text-warning"></i> Edit
                            </a>
                        </li>
                        <li class="visually-hidden">
                            <a class="dropdown-item text-black" href="#" onclick="hapus(' . $data->id_kab . ')">
                                <i class="fa-solid fa-trash text-danger"></i> Hapus
                            </a>
                        </li>
                    </ul>
                </div>
            ';
            })
            ->toJson(true);
    }

    public function kecamatan(){
            $query = $this->db->table('t_kecamatan tc')->select('
            tb.kabupaten,
            tc.kecamatan,
            tc.id_kec
            ')->join('t_kabupaten tb', 'tb.id_kab = tc.id_kab');

      return DataTable::of($query)
            ->add('aksi', function ($data) {
                
                return '<div class="dropdown">
                    <button class="btn btn-sm" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fa fa-ellipsis-v text-black"></i>
                    </button>
                    <ul class="dropdown-menu border border-dark py-0">
                        <li>
                            <a class="dropdown-item text-black" href="#" onclick="edit(' . $data->id_kec . ')">
                                <i class="fa-solid fa-pen-to-square text-warning"></i> Edit
                            </a>
                        </li>
                        <li class="visually-hidden">
                            <a class="dropdown-item text-black" href="#" onclick="hapus(' . $data->id_kec . ')">
                                <i class="fa-solid fa-trash text-danger"></i> Hapus
                            </a>
                        </li>
                    </ul>
                </div>
            ';
            })
            ->toJson(true);
    }

    public function kelurahan()
{
    $query = $this->db->table('t_kelurahan tl')
        ->select('
            tc.kecamatan,
            tl.kelurahan,
            tl.id_kelurahan,
            tb.kabupaten
        ')
        ->join('t_kecamatan tc', 'tc.id_kec = tl.id_kec')
        ->join('t_kabupaten tb', 'tb.id_kab = tl.id_kab');

    // Return the DataTable JSON response
    return DataTable::of($query)
        ->add('aksi', function ($data) {
            // Generate action buttons
            return '<div class="dropdown">
                <button class="btn btn-sm" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fa fa-ellipsis-v text-black"></i>
                </button>
                <ul class="dropdown-menu border border-dark py-0">
                    <li>
                        <a class="dropdown-item text-black" href="#" onclick="edit(' . $data->id_kelurahan . ')">
                            <i class="fa-solid fa-pen-to-square text-warning"></i> Edit
                        </a>
                    </li>
                    <li class="visually-hidden">
                        <a class="dropdown-item text-black" href="#" onclick="hapus(' . $data->id_kelurahan . ')">
                            <i class="fa-solid fa-trash text-danger"></i> Hapus
                        </a>
                    </li>
                </ul>
            </div>';
        })
        ->toJson(true);
}

    public function kabupatennew()
    {
        $data['title'] = $this->title;

        $msg = ['html' => view('/master/wilayah/kabupaten/modal/new', $data)];

        return $this->response->setJSON($msg);
    }
    public function kecamatannew()
    {
        $data['title'] = $this->title;

        $msg = ['html' => view('/master/wilayah/kecamatan/modal/new', $data)];

        return $this->response->setJSON($msg);
    }
    public function kelurahannew()
    {
        $data['title'] = $this->title;

        $msg = ['html' => view('/master/wilayah/kelurahan/modal/new', $data)];

        return $this->response->setJSON($msg);
    }

    
    public function kabupatenedit()
    {
        $data['id'] = $this->request->getGet('id');
        $data['title'] = $this->title;

        $query = $this->db->table('t_kabupaten')->where('id_kab', $data['id'])->get()->getRow();

        if($query){
            $data['kabupaten'] = $query->kabupaten;
        }
        $msg = ['html' => view('/master/wilayah/kabupaten/modal/edit', $data)];

        return $this->response->setJSON($msg);
    }
    public function kecamatanedit()
    {
        $data['id'] = $this->request->getGet('id');
        $data['title'] = $this->title;

        $query = $this->db->table('t_kecamatan')->where('id_kec', $data['id'])->join('t_kabupaten','t_kabupaten.id_kab = t_kecamatan.id_kab')->get()->getRow();

        if($query){
            $data['kecamatan'] = $query->kecamatan;
            $data['kabupaten'] = $query->kabupaten;
            $data['id_kab'] = $query->id_kab;
        }
        $msg = ['html' => view('/master/wilayah/kecamatan/modal/edit', $data)];

        return $this->response->setJSON($msg);
    }
    public function kelurahanedit()
    {
        $data['id'] = $this->request->getGet('id');
        $data['title'] = $this->title;

        $query = $this->db->table('t_kelurahan')->where('id_kelurahan', $data['id'])
        ->join('t_kabupaten','t_kabupaten.id_kab = t_kelurahan.id_kab')
        ->join('t_kecamatan','t_kecamatan.id_kec = t_kelurahan.id_kec')
        ->get()->getRow();

        if($query){
            $data['id_kab'] = $query->id_kab;
            $data['id_kec'] = $query->id_kec;
            $data['id_kelurahan'] = $query->id_kelurahan;
            $data['kabupaten'] = $query->kabupaten;
            $data['kecamatan'] = $query->kecamatan;
            $data['kelurahan'] = $query->kelurahan;
        }
        $msg = ['html' => view('/master/wilayah/kelurahan/modal/edit', $data)];

        return $this->response->setJSON($msg);
    }

    public function kabupatenmaster(){
        $data = $this->request->getPost();

        if(!empty($data['id_kab'])){
            $this->db->table('t_kabupaten')->where('id_kab', $data['id_kab'])->update($data);
        }else{
            $this->db->table('t_kabupaten')->insert($data);
        }
            echo json_encode(['status' => 'success', 'msg' => 'Data berhasil disimpan']);
    }
    
    public function kecamatanmaster(){
        $data = $this->request->getPost();

        if(!empty($data['id_kec'])){

            $this->db->table('t_kecamatan')->where('id_kec', $data['id_kec'])->update($data);
        }else{
            
            $this->db->table('t_kecamatan')->insert($data);
        }

        echo json_encode(['status' => 'success', 'msg' => 'Data berhasil disimpan']);
    }

    public function kelurahanmaster(){
        $data = $this->request->getPost();

        if(!empty($data['id_kelurahan'])){

            $this->db->table('t_kelurahan')->where('id_kelurahan', $data['id_kelurahan'])->update($data);
        }else{
            
            $this->db->table('t_kelurahan')->insert($data);
        }
        echo json_encode(['status' => 'success', 'msg' => 'Data berhasil disimpan']);
    }
}