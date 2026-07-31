<?php

namespace App\Controllers\Logistik\BarangKeluar;

use App\Controllers\BaseController;
use Hermawan\DataTables\DataTable;

class laporan_permintaan extends BaseController
{
    var $title;
    public function __construct()
    {
        $this->title = 'Permintaan';
    }

    public function index()
    {
        $data['title'] = $this->title;
        return view('/logistik/barang_keluar/laporan_permintaan/main', $data);
    }

    public function laporan()
    {
        $data['title'] = $this->title;
        return view('/logistik/barang_keluar/laporan_permintaan/main', $data);
    }

    public function new()
    {
        $data['title'] = $this->title;
        $html = ['html' => view('/logistik/barang_keluar/entry_permintaan/modal/new', $data)];
        echo json_encode($html);
    }

    public function data()
    {
        $query = $this->db
            ->table('logistik_permintaan_tampung lpt')
            ->select(
                '
            lb.id,
            lb.barang_nama AS BARANG,
            lb.satuan AS SATUAN,
        
            lpt.stok_diberikan as QTY,

            DATE_FORMAT(lp.tanggal,  "%Y-%m-%d") AS TANGGAL_PERMINTAAN ,
            ',
            )
            ->join('logistik_barang lb', 'lpt.barang_id = lb.id', 'left')
            ->join('logistik_permintaan lp', 'lpt.no_permintaan = lp.no_permintaan', 'left');

        return DataTable::of($query)
            ->add('aksi', function ($row) {
                return '
            <button type="button" class="btn btn-sm btn-warning" onclick="edit(' .
                    $row->id .
                    ')">
                <i class="fas fa-edit"></i>
            </button>
            ';
            })
            ->toJson(true);
    }

    public function master()
    {
        $row = $this->request->getVar('rows');
        $tanggal_permintaan = $this->request->getVar('tanggal_permintaan');
        $no_permintaan = $this->request->getVar('no_permintaan');
        $penerima = $this->request->getVar('penerima');
        $logistik_permintaan_tampung = $this->db->table('logistik_permintaan_tampung');
        $logistik_barang = $this->db->table('logistik_barang');
        $logistik_permintaan = $this->db->table('logistik_permintaan');
        foreach ($row as $key => $value) {
            $jumlah_stok_akhir = (int) $value['stokAkhir'] - (int) $value['qty'];

            $tambahDataLogistik = $logistik_permintaan_tampung->insert([
                'no_permintaan' => $no_permintaan,
                'barang_id' => $value['barang'],
                'stok_awal' => $value['stokAkhir'],
                'stok_diberikan' => $value['qty'],
                'jumlah_stok_akhir' => $jumlah_stok_akhir,
            ]);

            if ($tambahDataLogistik) {
                $updateLogistikBarang = $logistik_barang->where('id', $value['barang'])->update([
                    'stok_akhir' => $jumlah_stok_akhir,
                ]);
            }
        }
        if ($updateLogistikBarang) {
            $tambahDataLogistikPermintaan = $logistik_permintaan->insert([
                'no_permintaan' => $no_permintaan,
                'tanggal' => $tanggal_permintaan,
                'penerima' => $penerima,
                'userid' => session()->get('id') ?? 0,
            ]);
        }
        echo json_encode(['status' => 'success', 'message' => 'Data Berhasil Disimpan']);
    }
    public function ambilData()
    {
        $id = $this->request->getVar('id');
        $data = $this->db->table('logistik_barang')->select('*')->where('id', $id)->get()->getRow();

        if ($data) {
            echo json_encode($data);
        }
    }
}
