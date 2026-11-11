<?php

namespace App\Controllers\Master;

use App\Controllers\BaseController;

class SetJenisPelanggan extends BaseController
{
    var $title;

    public function __construct()
    {
        $this->title = "Jenis Pelanggan";
    }

    public function index()
    {
        return view('/master/bs/jenispelanggan/main', ['title' => $this->title]);
    }

    public function data()
    {
        $query = $this->db->table('m_jasa');
        $query->select('m_jasa.*, m_jenis_pelanggan.jenis_pelanggan, m_jenis_pelanggan.id_jenis');
        $query->join('m_jenis_pelanggan', 'm_jasa.id_jasa = m_jenis_pelanggan.id_jasa');

        $data = $query->get()->getResultArray();

        // Output JSON
        return $this->response->setJSON($data);
    }

    public function new()
    {
        $qurey = $this->db->table('m_jasa')->select('m_jasa.id_jasa, m_jasa.kelompok_jasa')->get()->getResultArray();
        $data['kelompok_jasa'] = $qurey;
        $data['title'] = $this->title;
        // dd($data);
        $html = ['html' => view('/master/bs/jenispelanggan/modal/new', $data)];
        echo json_encode($html);
    }
    public function save()
    {
        $data = $this->request->getPost();
        $this->db->table('m_jenis_pelanggan')->insert($data);
        echo json_encode(['message' => 'Data saved successfully']);
    }

    public function delete()
    {
        $id = $this->request->getPost('id');
        $this->db->table('m_jenis_pelanggan')->where('id_jenis', $id)->delete();
        echo json_encode(['message' => 'Data deleted successfully']);
    }

    public function edit()
    {
        $id = $this->request->getVar('id');
        $data = $this->db->table('m_jenis_pelanggan')->where('id_jenis', $id)->get()->getRowArray();
        $data['kelompok_jasa'] = $this->db->table('m_jasa')->select('m_jasa.id_jasa, m_jasa.kelompok_jasa')->get()->getResultArray();

        $html = ['html' => view('/master/bs/jenispelanggan/modal/edit', ['data' => $data, 'title' => $this->title])];
        echo json_encode($html);
    }

    public function update()
    {
        $data = $this->request->getPost();
        $this->db->table('m_jenis_pelanggan')->where('id_jenis', $data['id_jenis'])->update($data);
        echo json_encode(['message' => 'Data updated successfully']);
    }

    public function modal_export_pdf()
    {
        $msg = ['html' => view('master/bs/jenispelanggan/modal/pdf')];
        echo json_encode($msg);
    }

    public function export_pdf()
    {
        $html = '';
        $html .= '<div style="margin-top: 10px;">';
        $html .= "<h3 align='center' style='margin-bottom: 0px; margin-top: 0px;'>DATA JENIS PELANGGAN</h3>";
        $html .= "<p align='center' style='margin-bottom: 0px; margin-top: 0px;'><b>PERIODE : " . date('d/m/Y') . "</b></p>";

        $builder = $this->db->table('m_jasa');
        $builder->select('m_jasa.*, m_jenis_pelanggan.jenis_pelanggan, m_jenis_pelanggan.id_jenis');
        $builder->join('m_jenis_pelanggan', 'm_jasa.id_jasa = m_jenis_pelanggan.id_jasa');

        $result = $builder->get()->getResultArray();

        $html .= '<br>';
        $html .= '<div class="table">';
        $html .= '<table>';
        $html .= '<tr>';
        $html .= '<th rowspan="2" width="3%">No</th>';
        $html .= '<th rowspan="2" width="14%">Kelompok & Jenis Pelanggan</th>';
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
            $currentKelompok = '';
            foreach ($result as $key => $value) {
                if ($currentKelompok !== $value['kelompok_jasa']) {
                    $html .= '<tr style="border-bottom: 1px solid black; background-color: #333; text-align: center;">';
                    $html .= '<td class="text-center" style="color: #fff"><b>' . ($key + 1) . '</b></td>';
                    $html .= '<td class="text-center" style="color: #fff"><b>' . $value['kelompok_jasa'] . '</b></td>';
                    $html .= '<td class="text-center" style="color: #fff"><b>Rp. ' . number_format($value['pemasangan'], 0, ',', '.') . '</b></td>';
                    $html .= '<td class="text-center" style="color: #fff"><b>Rp. ' . number_format($value['sambung_baru'], 0, ',', '.') . '</b></td>';
                    $html .= '<td class="text-center" style="color: #fff"><b>Rp. ' . number_format($value['ukur_gambar'], 0, ',', '.') . '</b></td>';
                    $html .= '<td class="text-center" style="color: #fff"><b>Rp. ' . number_format($value['jaminan'], 0, ',', '.') . '</b></td>';
                    $html .= '<td class="text-center" style="color: #fff"><b>Rp. ' . number_format($value['tarif_bawah_10'], 0, ',', '.') . '</b></td>';
                    $html .= '<td class="text-center" style="color: #fff"><b>Rp. ' . number_format($value['tarif_atas_10'], 0, ',', '.') . '</b></td>';
                    $html .= '</tr>';
                    $currentKelompok = $value['kelompok_jasa'];
                }
                $html .= '<tr>';
                $html .= '<td class="text-center">+</td>';
                $html .= '<td class="" colspan="7"><b>' . $value['jenis_pelanggan'] . '</b></td>';
                $html .= '</tr>';
            }
        } else {
            $html .= '<tr>';
            $html .= '<td colspan="9" class="text-center">Tidak Ada Data</td>';
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
