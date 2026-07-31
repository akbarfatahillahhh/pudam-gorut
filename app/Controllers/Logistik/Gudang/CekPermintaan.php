<?php

namespace App\Controllers\Logistik\Gudang;

use App\Controllers\BaseController;
use Hermawan\DataTables\DataTable;

class CekPermintaan extends BaseController
{
    var $title;
    public function __construct()
    {
        $this->title = 'Cek Permintaan';
    }

    public function index()
    {
        $data['title'] = $this->title;
        return view('/logistik/gudang/cek_permintaan/main', $data);
    }

    public function ambil_permintaan()
    {
        if ($this->request->isAJAX()) {

            $page = (int) $this->request->getVar('page') ?? 1;
            $limit = 4;
            $offset = ($page - 1) * $limit;

            $query = $this->db->table('logistik_permintaan')
                ->select('logistik_permintaan.no_permintaan, logistik_permintaan.penerima, logistik_permintaan.tanggal, logistik_permintaan_cek.status')
                ->join('logistik_permintaan_cek', 'logistik_permintaan.no_permintaan = logistik_permintaan_cek.no_permintaan')
                ->distinct()
                ->orderBy('logistik_permintaan.tanggal', 'DESC');

            if ($this->request->getVar('search')) {
                $query
                    ->like('logistik_permintaan.no_permintaan', $this->request->getVar('search'))
                    ->orLike('logistik_permintaan.penerima', $this->request->getVar('search'))
                    ->orLike('logistik_permintaan.tanggal', $this->request->getVar('search'));
            }
            // die;
            //$result = $query->limit($limit, $offset)->get()->getResultArray();
            $result = $query->get()->getResultArray();

            $total_data = $query->countAllResults(false);

            echo json_encode([
                'status' => 'success',
                'data' => $result,
                'current_page' => $page,
                'total_pages' => ceil($total_data / $limit),
            ]);
        }
    }

    public function ambil_detail_permintaan()
    {
        if ($this->request->isAJAX()) {
            $id = $this->request->getVar('id');
            $query = $this->db->table('logistik_permintaan_tampung')
                ->join('logistik_permintaan', 'logistik_permintaan.no_permintaan = logistik_permintaan_tampung.no_permintaan', 'left')
                ->join('logistik_barang', 'logistik_permintaan_tampung.barang_id = logistik_barang.id', 'left')
                ->join('logistik_permintaan_cek', 'logistik_permintaan_tampung.barang_id = logistik_permintaan_cek.barang_id')
                ->where('logistik_permintaan_tampung.no_permintaan', $id)
                ->where('logistik_permintaan_cek.no_permintaan', $id);
            $result = $query->get()->getResultArray();

            echo json_encode(['status' => 'success', 'data' => $result]);
        }
    }

    public function master()
    {
        if ($this->request->isAJAX()) {
            $data = $this->request->getPost();

            $logistikBarang = $this->db->table('logistik_barang');
            $logistikCek = $this->db->table('logistik_permintaan_cek');

            $result = $logistikBarang->where('id', $data['barang_id'])->get()->getRow();
            $jumlahStokAkhir = $result->stok_akhir - $data['verifikasi_stok'];

            if ($result->stok_akhir < $data['verifikasi_stok']) {
                echo json_encode(['status' => 'error']);
            } else if (isset($data['barang_id']) && $data['barang_id'] != null && isset($data['no_permintaan']) && $data['no_permintaan'] != null) {
                $updateCek = $logistikCek->set(['verifikasi_stok' => $data['verifikasi_stok']])->where('no_permintaan', $data['no_permintaan'])->where('barang_id', $data['barang_id'])->update();
                echo json_encode(['status' => 'success']);
            }
        }
    }

    public function ubah_status()
    {
        if ($this->request->isAJAX()) {
            $status = $this->request->getVar('status');
            $no_permintaan = $this->request->getVar('no_permintaan');

            $logistik_permintaan_cek = $this->db->table('logistik_permintaan_cek');

            $update = $logistik_permintaan_cek->where('no_permintaan', $no_permintaan)->update(['status' => $status]);

            if ($status == 'Y') {
                $logistik_permintaan_update = $this->db->table('logistik_permintaan_cek')
                    ->where('no_permintaan', $no_permintaan)
                    ->get()->getResultArray();

                foreach ($logistik_permintaan_update as $row) {
                    $logistik_barang = $this->db->table('logistik_barang')
                        ->where('id', $row['barang_id'])
                        ->get()->getRow();

                    $updateStokBarang = $this->db->table('logistik_barang')
                        ->set(['stok_akhir' => $logistik_barang->stok_akhir - $row['verifikasi_stok']])
                        ->where('id', $row['barang_id'])
                        ->update();
                }
            }

            if ($update) {
                echo json_encode(['status' => 'success']);
            } else {
                echo json_encode(['status' => 'error']);
            }
        } else {
            return redirect()->to(base_url('gudang/cek-permintaan'));
        }
    }

    public function modal_export_pdf()
    {
        $data['no_permintaan'] = $this->request->getVar('no_permintaan');
        $msg = ['html' => view('logistik/gudang/cek_permintaan/modal/pdf', $data)];
        echo json_encode($msg);
    }

    public function export_pdf($no_permintaan)
    {
        $row = $this->db->table('logistik_permintaan')
            ->select('logistik_permintaan.tanggal, logistik_permintaan.penerima')
            ->where('logistik_permintaan.no_permintaan', $no_permintaan)
            ->get()->getRow();

        $html = '';
        $html .= '<div style="margin-top: 10px;">';
        $html .= "<h3 align='center' style='margin-bottom: 0px; margin-top: 0px;'>DETAIL PERMINTAAN</h3>";
        $html .= '<br>';

        $html .= "<table style='margin-bottom: 5px;'>";
        $html .= "<tr>";
        $html .= "<th class='text-left'>NO PERMINTAAN</th>";
        $html .= "<th class='text-center' width='3%'>:</th>";
        $html .= "<th class='text-left'>" . $no_permintaan . "</th>";
        $html .= "</tr>";
        $html .= "<tr>";
        $html .= "<th class='text-left'>TANGGAL PERMINTAAN</th>";
        $html .= "<th class='text-center' width='3%'>:</th>";
        $html .= "<th class='text-left'>" . date('d/m/Y', strtotime($row->tanggal)) . "</th>";
        $html .= "</tr>";
        $html .= "<tr>";
        $html .= "<th class='text-left'>PENERIMA</th>";
        $html .= "<th class='text-center' width='3%'>:</th>";
        $html .= "<th class='text-left'>" . strtoupper($row->penerima) . "</th>";
        $html .= "</tr>";
        $html .= "</table>";

        $builder = $this->db->table('logistik_permintaan_tampung');
        $builder->select('logistik_barang.barang_nama, logistik_barang.satuan, logistik_barang.stok_akhir');
        $builder->select('logistik_permintaan_tampung.stok_diberikan, logistik_permintaan_cek.verifikasi_stok');
        $builder->join('logistik_permintaan', 'logistik_permintaan.no_permintaan = logistik_permintaan_tampung.no_permintaan', 'left');
        $builder->join('logistik_barang', 'logistik_permintaan_tampung.barang_id = logistik_barang.id', 'left');
        $builder->join('logistik_permintaan_cek', 'logistik_permintaan_tampung.barang_id = logistik_permintaan_cek.barang_id');
        $builder->where('logistik_permintaan_tampung.no_permintaan', $no_permintaan);
        $builder->where('logistik_permintaan_cek.no_permintaan', $no_permintaan);
        $result = $builder->get()->getResultArray();

        $html .= '<div class="table">';
        $html .= '<table>';
        $html .= '<tr>';
        $html .= '<th width="5%">No</th>';
        $html .= '<th>Nama Barang</th>';
        $html .= '<th>Satuan</th>';
        $html .= '<th>Stok Akhir</th>';
        $html .= '<th>Jumlah Diminta</th>';
        $html .= '<th>Jumlah Diberikan</th>';
        $html .= '</tr>';

        if ($result) {
            $totalStokDiminta = 0;
            $totalStokDiberikan = 0;
            foreach ($result as $key => $value) {
                $html .= '<tr>';
                $html .= '<td class="text-center">' . ($key + 1) . '</td>';
                $html .= '<td>' . $value['barang_nama'] . '</td>';
                $html .= '<td class="text-center">' . $value['satuan'] . '</td>';
                $html .= '<td class="text-center">' . number_format($value['stok_akhir']) . '</td>';
                $html .= '<td class="text-center">' . number_format($value['stok_diberikan']) . '</td>';
                $html .= '<td class="text-center">' . number_format($value['verifikasi_stok']) . '</td>';
                $html .= '</tr>';
                $totalStokDiminta += $value['stok_diberikan'];
                $totalStokDiberikan += $value['verifikasi_stok'];
            }

            $html .= '<tr>';
            $html .= '<td class="text-center" colspan="4"><b>Total</b></td>';
            $html .= '<td class="text-center"><b>' . number_format($totalStokDiminta) . '</b></td>';
            $html .= '<td class="text-center"><b>' . number_format($totalStokDiberikan) . '</b></td>';
            $html .= '</tr>';
        } else {
            $html .= '<tr>';
            $html .= '<td colspan="5" class="text-center">Tidak Ada Data</td>';
            $html .= '</tr>';
        }

        $html .= '</table>';
        $html .= '</div>';

        $html .= '</div>';

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
        $mpdf->SetTitle("DETAIL PERMINTAAN");
        $this->response->setHeader('Content-Type', 'application/pdf');
        $mpdf->Output("PDF", 'I');
    }
}
