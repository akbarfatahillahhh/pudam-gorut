<?php

namespace App\Controllers\Master;

use App\Controllers\BaseController;
use Hermawan\DataTables\DataTable;

class SetKelompokTarif extends BaseController
{
    var $title;
    public function __construct()
    {
        $this->title = "Kelompok Tarif";
    }
    public function index()
    {
        return view('/master/bs/kelompoktarif/set-kelompok-tarif', ['title' => $this->title]);
    }

    public function data()
    {
        $query = $this->db->table('m_jasa')
            ->select('*')
            ->get()
            ->getResultArray();
        echo json_encode($query);
    }

    public function new()
    {
        $data['title'] = $this->title;
        $html = ['html' => view('/master/bs/kelompoktarif/modal/new', $data)];
        echo json_encode($html);
    }

    public function save()
    {
        $data = $this->request->getPost();
        $this->db->table('m_jasa')->insert($data);
        echo json_encode(['message' => 'Data saved successfully']);
    }

    public function delete()
    {
        $id = $this->request->getPost('id');
        $this->db->table('m_jasa')->where('id_jasa', $id)->delete();
        echo json_encode(['message' => 'Data deleted successfully']);
    }

    public function edit()
    {
        $id = $this->request->getVar('id');
        $data = $this->db->table('m_jasa')->where('id_jasa', $id)->get()->getRowArray();
        $html = ['html' => view('/master/bs/kelompoktarif/modal/edit', ['data' => $data, 'title' => $this->title])];
        echo json_encode($html);
    }

    public function update()
    {
        $data = $this->request->getPost();
        $this->db->table('m_jasa')->where('id_jasa', $data['id_jasa'])->update($data);
        echo json_encode(['message' => 'Data updated successfully']);
    }

    public function modal_export_pdf()
    {
        $msg = ['html' => view('master/bs/kelompoktarif/modal/pdf')];
        echo json_encode($msg);
    }

    public function export_pdf()
    {
        $html = '';
        $html .= '<div style="margin-top: 10px;">';
        $html .= "<h3 align='center' style='margin-bottom: 0px; margin-top: 0px;'>DATA KELOMPOK TARIF</h3>";
        $html .= "<p align='center' style='margin-bottom: 0px; margin-top: 0px;'><b>PERIODE : " . date('d/m/Y') . "</b></p>";

        $builder = $this->db->table('m_jasa');
        $result = $builder->get()->getResultArray();


        $html .= '<br>';
        $html .= '<div class="table">';
        $html .= '<table>';
        $html .= '<tr>';
        $html .= '<th rowspan="2" width="3%">No</th>';
        $html .= '<th rowspan="2" width="14%">Kelompok</th>';
        $html .= '<th colspan="4">Biaya</th>';
        $html .= '<th colspan="2">Biaya Tarif</th>';
        $html .= '</tr>';

        $html .= '<tr>';
        $html .= '<th>Pemasangan</th>';
        $html .= '<th>Sambungan Baru</th>';
        $html .= '<th>Ukur Gambar</th>';
        $html .= '<th>Jaminan Langganan</th>';
        $html .= '<th>(  0 - 10M3 )</th>';
        $html .= '<th>(  > 10M3 )</th>';
        $html .= '</tr>';

        if ($result) {
            foreach ($result as $key => $value) {
                $html .= '<tr>';
                $html .= '<td class="text-center">' . ($key + 1) . '</td>';
                $html .= '<td class="text-center">' . $value['kelompok_jasa'] . '</td>';
                $html .= '<td class="text-center">Rp. ' . number_format($value['pemasangan']) . '</td>';
                $html .= '<td class="text-center">Rp. ' . number_format($value['sambung_baru']) . '</td>';
                $html .= '<td class="text-center">Rp. ' . number_format($value['ukur_gambar']) . '</td>';
                $html .= '<td class="text-center">Rp. ' . number_format($value['jaminan']) . '</td>';
                $html .= '<td class="text-center">Rp. ' . number_format($value['tarif_bawah_10']) . '</td>';
                $html .= '<td class="text-center">Rp. ' . number_format($value['tarif_atas_10']) . '</td>';
                $html .= '</tr>';
            }
        } else {
            $html .= '<tr>';
            $html .= '<td colspan="8" class="text-center">Tidak Ada Data</td>';
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
            'orientation' => 'L',
            'default_font_size' => 10,
            'default_font' => 'arial',
            'tempDir' => __DIR__ . '/temp',
        ]);

        $mpdf->WriteHTML($page);
        $mpdf->SetTitle("KELOMPOK TARIF");
        $this->response->setHeader('Content-Type', 'application/pdf');
        $mpdf->Output("PDF", 'I');
    }
}
