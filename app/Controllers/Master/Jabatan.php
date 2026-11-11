<?php

namespace App\Controllers\Master;

use App\Controllers\BaseController;
use Hermawan\DataTables\DataTable;

class Jabatan extends BaseController
{
    var $title;
    public function __construct()
    {
        $this->title = 'Jabatan';
    }

    public function index()
    {
        $data['title'] = $this->title;
        return view('master/jabatan/main', $data);
    }

    public function data()
    {
        $query = $this->db->table('jabatan');
        return DataTable::of($query)
            ->add('action', function ($row) {
                return "
                <div class='dropdown'>
                    <button class='btn btn-sm' type='button' data-bs-toggle='dropdown' aria-expanded='false'>
                        <i class='fa fa-ellipsis-v text-black'></i>
                    </button>
                    <ul class='dropdown-menu border border-dark py-0'>
                        <li>
                            <a class='dropdown-item text-black' href='#' onclick='edit({$row->ID})'>
                                <i class='fa-solid fa-pen-to-square text-warning pe-1'></i> Edit
                            </a>
                        </li>
                        <li class='visually-hidden'>
                            <a class='dropdown-item text-black' href='#' onclick='hapus({$row->ID})'>
                                <i class='fa-solid fa-trash text-danger pe-1'></i> Hapus
                            </a>
                        </li>
                    </ul>
                </div>";
            })
            ->toJson(true);
    }

    public function new()
    {
        $data['title'] = $this->title;
        $html = ['html' => view('/master/jabatan/modal/new', $data)];
        echo json_encode($html);
    }

    public function edit()
    {
        $id = $this->request->getVar('id');

        $query = $this->db->table('jabatan')->where('ID', $id)->get()->getRow();

        $data['title'] = $this->title;
        if ($query) {
            $data['ID'] = $query->ID;
            $data['JABATAN'] = $query->JABATAN;
        }
        $html = ['html' => view('/master/jabatan/modal/edit', $data)];
        echo json_encode($html);
    }

    public function master()
    {
        $data = $this->request->getPost();

        $query = $this->db->table('jabatan');
        if (isset($data['ID']) && empty($data['JABATAN'])) {
            $query->where('ID', $data['ID'])->delete();
        } elseif (isset($data['ID']) && isset($data['JABATAN'])) {
            $query->where('ID', $data['ID'])->update($data);
        } else {
            $query->insert($data);
        }
        echo json_encode(['status' => 'success', 'msg' => 'Data berhasil disimpan']);
    }

    public function modal_export_pdf()
    {
        $msg = ['html' => view('master/jabatan/modal/pdf')];
        echo json_encode($msg);
    }

    public function export_pdf()
    {
        $html = '';
        $html .= '<div style="margin-top: 10px;">';
        $html .= "<h3 align='center' style='margin-bottom: 0px; margin-top: 0px;'>DATA JABATAN</h3>";
        $html .= "<p align='center' style='margin-bottom: 0px; margin-top: 0px;'><b>PERIODE : " . date('d/m/Y') . "</b></p>";

        $builder = $this->db->table('jabatan');
        $result = $builder->get()->getResultArray();


        $html .= '<br>';
        $html .= '<div class="table">';
        $html .= '<table>';
        $html .= '<tr>';
        $html .= '<th width="5%">No</th>';
        $html .= '<th>Jabatan</th>';
        $html .= '</tr>';

        if ($result) {
            foreach ($result as $key => $value) {
                $html .= '<tr>';
                $html .= '<td class="text-center">' . ($key + 1) . '</td>';
                $html .= '<td>' . $value['JABATAN'] . '</td>';
                $html .= '</tr>';
            }
        } else {
            $html .= '<tr>';
            $html .= '<td colspan="2" class="text-center">Tidak Ada Data</td>';
            $html .= '</tr>';
        }

        $html .= '</table>';
        $html .= '</div>';

        $html .= '</br>';

        $data = ['html' => $html];
        $page = view('pdf/potrait', $data);

        $mpdf = new \Mpdf\Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'orientation' => 'P',
            'default_font_size' => 10,
            'default_font' => 'arial',
            'tempDir' => __DIR__ . '/temp',
        ]);

        $mpdf->WriteHTML($page);
        $mpdf->SetTitle("JABATAN");
        $this->response->setHeader('Content-Type', 'application/pdf');
        $mpdf->Output("PDF", 'I');
    }
}
