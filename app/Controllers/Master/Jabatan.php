<?php

namespace App\Controllers\Master;

use App\Controllers\BaseController;
use Hermawan\DataTables\DataTable;

class Jabatan extends BaseController
{
    var $title;
    public function __construct()
    {
        $this->title = "Jabatan";
    }

    public function index()
    {
        $data['title'] = $this->title;
        return view('/master/jabatan/main', $data);
    }

    public function data() {}

    public function new()
    {
        $data['title'] = $this->title;
        $html = ['html' => view('/master/jabatan/modal/new', $data)];
        echo json_encode($html);
    }
}
