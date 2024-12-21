<?php

namespace App\Controllers;

use Irsyadulibad\DataTables\DataTables;
use App\Models\ProfilOrganisasi;
use App\Models\MappingAsesor;
use App\Models\EP;
use App\Models\File;

class Home extends BaseController
{

    public function index()
    {
        return view('dashboard');
    }

    public function empty()
    {
        $data = [
            'main_title' => '',
            'sub_title' => 'Dashboard'
        ];

        return view('dashboard', $data);
    }   
}