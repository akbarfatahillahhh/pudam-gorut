<?php

namespace App\Controllers\Logistik\BarangMasuk;

use App\Controllers\BaseController;
use Hermawan\DataTables\DataTable;

class Penerimaan extends BaseController
{
    var $title;
    public function __construct()
    {
        $this->title = 'Penerimaan';
    }

    public function index()
    {
        $data['title'] = $this->title;
        return view('/logistik/barang_masuk/entry_faktur/main', $data);
    }

    public function new()
    {
        $data['title'] = $this->title;
        $html = ['html' => view('/logistik/barang_masuk/entry_faktur/modal/new', $data)];
        echo json_encode($html);
    }

    public function data()
    {
        $tglAwal = $this->request->getVar('tglAwal');
        $tglAkhir = $this->request->getVar('tglAkhir');


        $table = $this->db
            ->table('logistik_penerimaan_tampung lpt')
            ->select('
                    lpt.id,
                    lpt.no_faktur,
                    lpt.stok_diterima as QTY,
                    lpt.harga_satuan AS HARGA,
                    lpt.jumlah_harga AS JUMLAH_HARGA,
                    lp.tanggal,
                    lb.barang_nama AS BARANG,
                    lb.satuan AS SATUAN,
                    COUNT(lb.id) AS TOTAL_BARANG,
                    (
                        SELECT SUM(jumlah_harga)
                        FROM logistik_penerimaan_tampung
                        WHERE no_faktur = lpt.no_faktur
                    ) AS TOTAL_HARGA
                ')
            ->join('logistik_penerimaan lp', 'lpt.no_faktur = lp.no_faktur', 'left')
            ->join('logistik_barang lb', 'lpt.barang_id = lb.id', 'left');


        if (!empty($tglAwal) && !empty($tglAkhir)) {
            $table->where('lp.tanggal >=', $tglAwal)
                ->where('lp.tanggal <=', $tglAkhir);
        }

        $table->groupBy('
                lpt.id, 
                lpt.no_faktur, 
                lp.tanggal, 
                lb.barang_nama, 
                lb.satuan, 
                lpt.stok_diterima, 
                lpt.harga_satuan, 
                lpt.jumlah_harga
            ');

        $result = $table->get()->getResultArray();

        echo json_encode($result);
    }

    public function master()
    {
        $row = $this->request->getVar('rows');
        $tanggal_faktur = $this->request->getVar('tanggal_faktur');
        $no_faktur = $this->request->getVar('no_faktur');
        $penerima = $this->request->getVar('penyedia');
        $userId = session()->get('id_session');

        $logistik_penerimaan_tampung = $this->db->table('logistik_penerimaan_tampung');
        $logistik_penerimaan = $this->db->table('logistik_penerimaan');
        $logistik_barang = $this->db->table('logistik_barang');

        foreach ($row as $key => $value) {
            if (!empty($value['barang']) && !empty($value['stokAkhir']) && !empty($value['qty']) && !empty($value['harga'])) {
                $jumlah_harga = $value['harga'] * $value['qty'];
                $jumlah_stok_akhir = $value['qty'] + $value['stokAkhir'];
                $stokDiterima = $value['qty'];
                $stok_awal = $value['stokAkhir'];
                $barang = $value['barang'];
                $harga_satuan = $value['harga'];

                $insertlpt = $logistik_penerimaan_tampung->insert([
                    'no_faktur' => $no_faktur,
                    'barang_id' => $barang,
                    'stok_awal' => $stok_awal,
                    'stok_diterima' => $stokDiterima,
                    'harga_satuan' => $harga_satuan,
                    'jumlah_stok_akhir' => $jumlah_stok_akhir,
                    'jumlah_harga' => $jumlah_harga,
                ]);

                if ($insertlpt) {
                    $getBarang = $logistik_barang->where('id', $barang)->get()->getRow();
                    if ($getBarang) {
                        $totalStok = (int) $getBarang->stok_akhir + (int) $stokDiterima;
                        $logistik_barang->where('id', $barang)->update(['stok_akhir' => $totalStok]);
                    } else {
                        echo json_encode(['status' => 'error', 'message' => 'Data Barang Tidak Ditemukan']);
                    }
                } else {
                    echo json_encode(['status' => 'error', 'message' => 'Gagal Menambahkan barang']);
                }
            }
        }
        $cek = $logistik_penerimaan->where('no_faktur', $no_faktur)->get()->getRow();
        if (empty($cek)) {
            $insert = $logistik_penerimaan->insert([
                'no_faktur' => $no_faktur,
                'tanggal' => $tanggal_faktur,
                'penyedia' => $penerima,
                'userId' => $userId,
                'created_at' => date('Y-m-d H:i:s')
            ]);
            if ($insert) {
                echo json_encode(['status' => 'success', 'message' => 'Data Berhasil Disimpan']);
            } else {
                echo json_encode(['status' => 'success', 'message' => 'Data Gagal Disimpan']);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'No. Faktur Sudah Ada']);
        }
    }
    public function info()
    {
        $id = $this->request->getVar('id');
        $data = $this->db
            ->table('logistik_penerimaan_tampung ltp')
            ->select('
                ltp.no_faktur, ltp.stok_awal,
                ltp.jumlah_stok_akhir, ltp.jumlah_harga, ltp.stok_diterima, ltp.harga_satuan,
                lb.barang_nama
            ')
            ->where('ltp.no_faktur', $id)
            ->join('logistik_barang lb', 'ltp.barang_id = lb.id', 'left')
            ->get()
            ->getResult();

        $dataFaktur = $this->db
            ->table('logistik_penerimaan')
            ->select(
                '
      (SELECT SUM(ltp2.jumlah_harga)
                FROM logistik_penerimaan_tampung ltp2
                WHERE ltp2.no_faktur = logistik_penerimaan.no_faktur) AS TOTAL_HARGA,

        logistik_penerimaan.no_faktur,
        logistik_penerimaan.tanggal,
        logistik_penerimaan.penyedia,
        ',
            )
            ->where('logistik_penerimaan.no_faktur', $id)
            ->join('logistik_penerimaan_tampung', 'logistik_penerimaan.no_faktur = logistik_penerimaan_tampung.no_faktur')
            ->get()
            ->getRow();

        if ($data) {
            $info = [
                'title' => $this->title,
                'data' => $data,
                'faktur' => $dataFaktur,
            ];
        } else {
            $info = [
                'title' => $this->title,
                'data' => null,
                'faktur' => null,
            ];
        }
        $view = ['html' => view('logistik/barang_masuk/entry_faktur/modal/info', $info)];
        echo json_encode($view);
    }
    public function delete()
    {
        $id = $this->request->getVar('id');
        $no_faktur = $this->request->getVar('no_faktur');

        $cek = $this->db->table('logistik_penerimaan_tampung')->where('no_faktur', $no_faktur)->countAllResults();

        if ($cek == 1) {
            $this->db->table('logistik_penerimaan')->where('no_faktur', $no_faktur)->delete();
            $this->db->table('logistik_penerimaan_tampung')->where('id', $id)->delete();
        } else if ($id == "null" && !empty($no_faktur)) {
            $this->db->table('logistik_penerimaan')->where('no_faktur', $no_faktur)->delete();
            $this->db->table('logistik_penerimaan_tampung')->where('no_faktur', $no_faktur)->delete();
        } else if (!empty($id)) {
            $this->db->table('logistik_penerimaan_tampung')->where('id', $id)->delete();
        }
        echo json_encode(['status' => 'success', 'message' => 'Data deleted successfully']);
    }

    public function export_pdf($no_faktur)
    {
        $row = $this->db->table('logistik_penerimaan')
            ->select('logistik_penerimaan.no_faktur, logistik_penerimaan.tanggal, logistik_penerimaan.penyedia')
            ->select('users.nama_lengkap')
            ->join('users', 'logistik_penerimaan.userid = users.id_session', 'left')
            ->where('logistik_penerimaan.no_faktur', $no_faktur)
            ->get()->getRow();

        $html = '';
        $html .= '<div style="margin-top: 10px;">';
        $html .= "<h3 align='center' style='margin-bottom: 0px; margin-top: 0px;'>FAKTUR PEMBELIAN</h3>";
        $html .= '<br>';

        $html .= "<table style='margin-bottom: 5px;'>";
        $html .= "<tr>";
        $html .= "<th class='text-left'>NO FAKTUR</th>";
        $html .= "<th class='text-center' width='3%'>:</th>";
        $html .= "<th class='text-left'>" . $no_faktur . "</th>";
        $html .= "</tr>";
        $html .= "<tr>";
        $html .= "<th class='text-left'>TANGGAL FAKTUR</th>";
        $html .= "<th class='text-center' width='3%'>:</th>";
        $html .= "<th class='text-left'>" . date('d/m/Y', strtotime($row->tanggal)) . "</th>";
        $html .= "</tr>";
        $html .= "<tr>";
        $html .= "<th class='text-left'>PENYEDIA/SUPPLIER</th>";
        $html .= "<th class='text-center' width='3%'>:</th>";
        $html .= "<th class='text-left'>" . strtoupper($row->penyedia) . "</th>";
        $html .= "</tr>";
        $html .= "<tr>";
        $html .= "<th class='text-left'>PENERIMA</th>";
        $html .= "<th class='text-center' width='3%'>:</th>";
        $html .= "<th class='text-left'>" . strtoupper($row->nama_lengkap ?? '-') . "</th>";
        $html .= "</tr>";
        $html .= "</table>";

        $builder = $this->db->table('logistik_penerimaan_tampung ltp');
        $builder->select('ltp.no_faktur, ltp.stok_awal, ltp.jumlah_stok_akhir, ltp.jumlah_harga, ltp.stok_diterima, ltp.harga_satuan, lb.barang_nama');
        $builder->where('ltp.no_faktur', $no_faktur);
        $builder->join('logistik_barang lb', 'ltp.barang_id = lb.id');
        $result = $builder->get()->getResult();

        $html .= '<div class="table">';
        $html .= '<table>';
        $html .= '<tr>';
        $html .= '<th width="5%">No</th>';
        $html .= '<th>Nama Barang</th>';
        $html .= '<th>Stok Akhir</th>';
        $html .= '<th>Qty</th>';
        $html .= '<th>Total Stok</th>';
        $html .= '<th>Harga Satuan</th>';
        $html .= '<th>Total Harga</th>';
        $html .= '</tr>';

        if ($result) {
            $totalStokAwal = 0;
            $totalStokDiterima = 0;
            $totalStokAkhir = 0;
            $totalHarga = 0;
            foreach ($result as $key => $value) {
                $html .= '<tr>';
                $html .= '<td class="text-center">' . ($key + 1) . '</td>';
                $html .= '<td>' . $value->barang_nama . '</td>';
                $html .= '<td class="text-center">' . number_format($value->stok_awal, 0, '.', '.') . '</td>';
                $html .= '<td class="text-center">' . number_format($value->stok_diterima, 0, '.', '.') . '</td>';
                $html .= '<td class="text-center">' . number_format($value->jumlah_stok_akhir, 0, '.', '.') . '</td>';
                $html .= '<td class="text-right">Rp. ' . number_format($value->harga_satuan, 0, ',', '.') . '</td>';
                $html .= '<td class="text-right">Rp. ' . number_format($value->jumlah_harga, 0, ',', '.') . '</td>';
                $html .= '</tr>';

                $totalStokAwal += $value->stok_awal;
                $totalStokDiterima += $value->stok_diterima;
                $totalStokAkhir += $value->jumlah_stok_akhir;
                $totalHarga += $value->jumlah_harga;
            }

            $html .= '<tr>';
            $html .= '<td class="text-center" colspan="2"><b>Total</b></td>';
            $html .= '<td class="text-center"><b>' . number_format($totalStokAwal, 0, '.', '.') . '</b></td>';
            $html .= '<td class="text-center"><b>' . number_format($totalStokDiterima, 0, '.', '.') . '</b></td>';
            $html .= '<td class="text-center"><b>' . number_format($totalStokAkhir, 0, '.', '.') . '</b></td>';
            $html .= '<td></td>';
            $html .= '<td class="text-right"><b>Rp. ' . number_format($totalHarga, 0, ',', '.') . '</b></td>';
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
            'orientation' => 'L',
            'default_font_size' => 10,
            'default_font' => 'arial',
            'tempDir' => __DIR__ . '/temp',
        ]);

        $mpdf->WriteHTML($page);
        $mpdf->SetTitle("DETAIL FAKTUR PEMBELIAN");
        $this->response->setHeader('Content-Type', 'application/pdf');
        $mpdf->Output("PDF", 'I');
    }
}
