<?php

namespace App\Controllers\Logistik\Gudang;

use App\Controllers\BaseController;
use Hermawan\DataTables\DataTable;



class Barang extends BaseController
{
    var $title;
    public function __construct()
    {
        $this->title = 'Barang';
    }

    public function index()
    {
        $data['title'] = $this->title;
        return view('/logistik/gudang/barang/main', $data);
    }

    public function data()
    {
        $query = $this->db->table('logistik_barang lb')->select('lb.id, lb.barang_kode, lb.satuan, lb.barang_nama, lb.stok_akhir, lb.harga');
        $query->orderBy('lb.stok_akhir', 'DESC');

        return DataTable::of($query)
            ->add('aksi', function ($data) {
                return "
                <div class='dropdown'>
                    <button class='btn btn-sm' type='button' data-bs-toggle='dropdown' aria-expanded='false'>
                        <i class='fa fa-ellipsis-v text-black'></i>
                    </button>
                    <ul class='dropdown-menu py-0'>
                        <li>
                            <a href='javascript:void(0)' class='dropdown-item text-black' href='#' onclick='edit(" . $data->id . ")'>
                                <i class='fa-solid fa-pen-to-square text-warning'></i> Edit
                            </a>
                        </li>
                        <li>
                            <a  href='javascript:void(0)' class='dropdown-item dropdown-item text-black' onclick=\"del('" . $data->id . "')\">
                                <i class='text-danger fa fa-trash'></i> Hapus
                            </a>
                        </li>    
                    </ul>
                </div>";
            })
            ->add('stok_akhir', function ($data) {
                return number_format($data->stok_akhir);
            })
            ->add('spesifikasi', function ($data) {
                return '-';
            })
            ->toJson(true);
    }

    public function new()
    {
        $data['title'] = $this->title;

        $msg = ['html' => view('logistik/gudang/barang/modal/new', $data)];
        echo json_encode($msg);
    }

    public function edit()
    {

        $id = $this->request->getVar('id');

        $query = $this->db->table('logistik_barang');
        $barang = $query->where('id', $id)->get()->getRow();

        if ($barang) {
            $data = [
                'id' => $barang->id,
                'title' => $this->title,
                'barang_kode' => $barang->barang_kode,
                'barang_nama' => $barang->barang_nama,
                'stok_akhir' => $barang->stok_akhir,
                'satuan' => $barang->satuan,
                'harga' => $barang->harga
            ];
        } else {
            $data = [
                'id' => '',
                'title' => $this->title,
                'barang_kode' => '',
                'barang_nama' => '',
                'stok_akhir' => '',
                'harga' => 0,
                'satuan' => ''
            ];
        }
        $msg = ['html' => view('logistik/gudang/barang/modal/edit', $data)];
        echo json_encode($msg);
    }

    public function master()
    {
        $data = $this->request->getPost();

        $query = $this->db->table('logistik_barang');
        if (isset($data['id']) && isset($data['barang_kode']) && isset($data['barang_nama']) && isset($data['satuan'])) {
            $query->where('id', $data['id']);
            $query->update($data);
        } else if (isset($data['id'])) {
            $query->where('id', $data['id']);
            $query->delete();
        } else {
            $query->insert($data);
        }
        echo json_encode(['status' => 'success', 'msg' => 'Data berhasil disimpan']);
    }

    public function modal_export_pdf()
    {
        $msg = ['html' => view('logistik/gudang/barang/modal/pdf')];
        echo json_encode($msg);
    }

    public function export_pdf()
    {
        $html = '';
        $html .= '<div style="margin-top: 10px;">';
        $html .= "<h3 align='center' style='margin-bottom: 0px; margin-top: 0px;'>DATA BARANG</h3>";
        $html .= "<p align='center' style='margin-bottom: 0px; margin-top: 0px;'><b>PERIODE : " . date('d/m/Y') . "</b></p>";

        $builder = $this->db->table('logistik_barang lb')->select('lb.id, lb.barang_kode, lb.satuan, lb.barang_nama, lb.stok_akhir, lb.harga');
        $builder->orderBy('lb.stok_akhir', 'DESC');
        $result = $builder->get()->getResultArray();

        $html .= '<br>';
        $html .= '<div class="table">';
        $html .= '<table>';
        $html .= '<tr>';
        $html .= '<th width="5%">No</th>';
        $html .= '<th>Kode Barang</th>';
        $html .= '<th>Nama Barang</th>';
        $html .= '<th>Satuan</th>';
        $html .= '<th>Stok Saat Ini</th>';
        $html .= '<th>Harga</th>';
        $html .= '</tr>';

        if ($result) {
            $totalStok = 0;
            foreach ($result as $key => $value) {
                $html .= '<tr>';
                $html .= '<td class="text-center">' . ($key + 1) . '</td>';
                $html .= '<td class="text-center">' . $value['barang_kode'] . '</td>';
                $html .= '<td>' . $value['barang_nama'] . '</td>';
                $html .= '<td class="text-center">' . $value['satuan'] . '</td>';
                $html .= '<td class="text-center">' . number_format($value['stok_akhir']) . '</td>';
                $html .= '<td class="text-center">Rp. ' . number_format($value['harga']) . '</td>';
                $html .= '</tr>';
                $totalStok += $value['stok_akhir'];
            }

            $html .= '<tr>';
            $html .= '<td class="text-center" colspan="4"><b>Total</b></td>';
            $html .= '<td class="text-center"><b>' . number_format($totalStok) . '</b></td>';
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
        $mpdf->SetTitle("BARANG");
        $this->response->setHeader('Content-Type', 'application/pdf');
        $mpdf->Output("PDF", 'I');
    }
}
