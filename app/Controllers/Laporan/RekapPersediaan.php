<?php

namespace App\Controllers\Laporan;

use App\Controllers\BaseController;
use Hermawan\DataTables\DataTable;

class RekapPersediaan extends BaseController
{
    var $title;
    public function __construct()
    {
        $this->title = "Rekap Persediaan";
    }

    public function index()
    {
        $data['title'] = $this->title;
        $data['daftarBulan'] = [
            '01' => 'Januari',
            '02' => 'Februari',
            '03' => 'Maret',
            '04' => 'April',
            '05' => 'Mei',
            '06' => 'Juni',
            '07' => 'Juli',
            '08' => 'Agustus',
            '09' => 'September',
            '10' => 'Oktober',
            '11' => 'November',
            '12' => 'Desember'
        ];
        return view('/laporan/rekap_persediaan/main', $data);
    }

    public function loadPage()
    {
        $msg = ['page' =>  view('/laporan/rekap_persediaan/page/main')];
        echo json_encode($msg);
    }

    public function datatable()
    {
        $unit = $this->request->getVar('unit');
        $parameter = $this->request->getVar('parameter');

        $allowedColumns = ['no_sambungan', 'nama_pelanggan'];
        if (!in_array($parameter, $allowedColumns)) {
            return $this->response->setJSON(['error' => 'Parameter tidak valid']);
        }

        $builder = $this->db->table('m_pelanggan p');
        $builder->select("p.no_sambungan, p.nama_pelanggan, j.jenis_pelanggan, k.kecamatan, l.kelurahan");
        $builder->join('m_jenis_pelanggan j', 'j.id_jenis = p.id_jenis');
        $builder->join('t_kecamatan k', 'k.id_kec = p.id_kec');
        $builder->join('t_kelurahan l', 'l.id_kelurahan = p.id_kelurahan');
        $builder->where('k.id_kec', $unit);
        $builder->orderBy("p.$parameter", 'ASC');

        $subQuery = $this->db->table('m_pelanggan')
            ->select($parameter)
            ->groupBy($parameter)
            ->having('COUNT(*) >', 1)
            ->get()
            ->getResultArray();

        $duplicateValues = array_column($subQuery, $parameter);

        if (!empty($duplicateValues)) {
            $builder->whereIn("p.$parameter", $duplicateValues);
        }

        return DataTable::of($builder)->toJson(true);
    }


    public function modal_export_pdf()
    {
        $data['parameter'] = $this->request->getVar('parameter');
        $data['unit'] = $this->request->getVar('unit');


        $msg = ['html' => view('laporan/pelanggan_duplikat/modal/pdf', $data)];
        echo json_encode($msg);
    }

    public function export_pdf($parameter, $unit)
    {
        $html = '<div style="margin-top: 10px;">';
        $html .= "<h3 align='center' style='margin-bottom: 0px; margin-top: 0px;'>LAPORAN DATA PELANGGAN DUPLIKAT</h3>";

        $kecamatan = $this->db->table('t_kecamatan')->select('kecamatan')->where('id_kec', $unit)->get()->getRow()->kecamatan;

        $html .= "<h3 align='center' style='margin-bottom: 0px; margin-top: 0px;'>UNIT : " . strtoupper($kecamatan) . "</h3>";


        $html .= '<br>';
        $html .= '<div class="table">';
        $html .= '<table>';
        $html .= '<tr>';
        $html .= '<th width="5%" rowspan="2">No</th>';
        $html .= '<th rowspan="2">No. Sambung</th>';
        $html .= '<th rowspan="2">Nama Pelanggan</th>';
        $html .= '<th colspan="2">Alamat</th>';
        $html .= '<th rowspan="2">Jenis Pelanggan</th>';
        $html .= '</tr>';
        $html .= '<tr>';
        $html .= '<th>Kecamatan</th>';
        $html .= '<th>Kelurahan</th>';
        $html .= '</tr>';

        $builder = $this->db->table('m_pelanggan p');
        $builder->select("p.no_sambungan, p.nama_pelanggan, j.jenis_pelanggan, k.kecamatan, l.kelurahan");
        $builder->join('m_jenis_pelanggan j', 'j.id_jenis = p.id_jenis');
        $builder->join('t_kecamatan k', 'k.id_kec = p.id_kec');
        $builder->join('t_kelurahan l', 'l.id_kelurahan = p.id_kelurahan');
        $builder->where('k.id_kec', $unit);
        $builder->orderBy("p.$parameter", 'ASC');

        $subQuery = $this->db->table('m_pelanggan')
            ->select($parameter)
            ->groupBy($parameter)
            ->having('COUNT(*) >', 1)
            ->get()
            ->getResultArray();

        $duplicateValues = array_column($subQuery, $parameter);

        if (!empty($duplicateValues)) {
            $builder->whereIn("p.$parameter", $duplicateValues);
        }

        $result = $builder->get()->getResultArray();

        if ($result) {
            foreach ($result as $key => $value) {
                $html .= '<tr>';
                $html .= '<td class="text-center">' . ($key + 1) . '</td>';
                $html .= '<td class="text-center">' . $value['no_sambungan'] . '</td>';
                $html .= '<td>' . $value['nama_pelanggan'] . '</td>';
                $html .= '<td>' . $value['kecamatan'] . '</td>';
                $html .= '<td>' . $value['kelurahan'] . '</td>';
                $html .= '<td>' . $value['jenis_pelanggan'] . '</td>';
                $html .= '</tr>';
            }
        } else {
            $html .= '<tr>';
            $html .= '<td colspan="6" class="text-center">Tidak Ada Data</td>';
            $html .= '</tr>';
        }

        $html .= '</table></div></br>';

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
        $mpdf->SetTitle("LAPORAN PELANGGAN DUPLIKAT");
        $this->response->setHeader('Content-Type', 'application/pdf');
        $mpdf->Output("PDF", 'I');
    }
}
