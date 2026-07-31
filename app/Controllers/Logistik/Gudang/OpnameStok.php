<?php

namespace App\Controllers\Logistik\Gudang;

use App\Controllers\BaseController;
use Hermawan\DataTables\DataTable;

class OpnameStok extends BaseController
{
    var $title;
    public function __construct()
    {
        $this->title = 'Stok Opname';
    }

    public function index()
    {
        $data['title'] = $this->title;
        return view('/logistik/gudang/opname_stok/main', $data);
    }

    public function show_modal()
    {
        $modal = $this->request->getVar('modal');

        $html = ['html' => view('logistik/gudang/opname_stok/modal/' . $modal, ['title' => $this->title])];

        echo json_encode($html);
    }

    public function data()
    {

        $tableBarang = $this->db->table('logistik_barang');


        return DataTable::of($tableBarang)
            ->add('aksi', function ($row) {
                return '
            <button type="button" class="kunci-' . $row->barang_kode . ' btn btn-sm btn-success" onclick="kunci(\'' .
                    $row->barang_kode .
                    '\')">
                <i class="fas fa-unlock"></i>
            </button>
            <button type="button" class="unkunci-' . $row->barang_kode . ' btn sr-only btn-sm btn-warning" onclick="unkunci(\'' .
                    $row->barang_kode .
                    '\')">
                <i class="fas fa-lock"></i>
            </button>
            ';
            })->add('qty', function ($row) {
                return '<input type="number" class="form-control form-control-sm qty-input" data-barang-kode="' . $row->barang_kode . '" name="qty[' . $row->barang_kode . ']" value="0">';
            })

            ->toJson(true);
    }

    public function simpanOpname()
    {
        if ($this->request->isAJAX()) {

            $qty = $this->request->getVar('qty');
            $KODE_OPNAME_STOK = $this->request->getVar('KODE_OPNAME_STOK');
            $TANGGAL_OPNAME_STOK = $this->request->getVar('TANGGAL_OPNAME_STOK');
            $PENANGGUNG_JAWAB = $this->request->getVar('PENANGGUNG_JAWAB');
            $KETERANGAN = $this->request->getVar('keterangan');
            $status = 'all';
            $mapping = [];
            foreach ($qty as $key => $value) {
                $barang = $this->db->table('logistik_barang');
                $result = $barang->where('barang_kode', $key)->get()->getRowArray();

                $mapping[] = [
                    'barang_kode' => $key,
                    'qty' => $value,
                    'stok_sebelum' => $result['stok_akhir']
                ];


                $stok_akhir = $result['stok_akhir'] + $value;

                //$updete = $barang->where('barang_kode', $key)->update([
                //  'stok_akhir' => $stok_akhir
                //]);
            }


            //if($updete){
            $logistik_opname = $this->db->table('logistik_opnamestok');
            $tambahOpname =   $logistik_opname->insert([
                'tanggal' => $TANGGAL_OPNAME_STOK,
                'sessionId' => session('id_session'),
                'referensi' => $KODE_OPNAME_STOK,
                'keterangan' => $KETERANGAN,
                'kat' => $status,
            ]);
            if ($tambahOpname) {
                $json = json_encode($mapping);

                $logistik_opname_tampung = $this->db->table('logistik_opname_tampung');
                $logistik_opname_tampung->insert([
                    'referensi' => $KODE_OPNAME_STOK,
                    'stok_verifikasi' => $json,
                ]);
            }
            //                }
            echo json_encode(['status' => 'success', 'msg' => 'Data berhasil disimpan']);
        }
    }

    public function dataOpname()
    {

        $tableStokOpname = $this->db->table('logistik_opnamestok')->where('kat', 'all');

        return DataTable::of($tableStokOpname)
            ->add('aksi', function ($row) {
                return '
            <div class="dropdown">
                  <button class="btn btn-sm" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fa fa-ellipsis-v text-black"></i>
                    </button>
                <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                    <li>
                        <a class="dropdown-item text-black" href="#" onclick="detailsOpnameStok(\'' . $row->referensi . '\')">
                            <i class="fa-solid fa-circle-info text-primary"></i> Details
                        </a>
                    </li>
                </ul>
            </div>
            ';
            })
            ->toJson(true);
    }

    public function detailsOpnameStok()
    {
        $referensi = $this->request->getVar('referensi');
        $logistik_opname_tampung = $this->db->table('logistik_opname_tampung');
        $logistik_opname_tampung->select('logistik_opname_tampung.*');
        $logistik_opname_tampung->select('logistik_opnamestok.*');
        $logistik_opname_tampung->select('users.nama_lengkap');
        $logistik_opname_tampung->join('logistik_opnamestok', 'logistik_opnamestok.referensi = logistik_opname_tampung.referensi');
        $logistik_opname_tampung->join('users', 'users.id_session = logistik_opnamestok.sessionId', 'left');
        $resultHasil = $logistik_opname_tampung->where('logistik_opname_tampung.referensi', $referensi)->get()->getRowArray();


        $data = json_decode($resultHasil['stok_verifikasi'], true);

        foreach ($data as $key => $value) {
            $barang = $this->db->table('logistik_barang');
            $result = $barang->where('barang_kode', $value['barang_kode'])->get()->getRowArray();
            $data[$key]['barang_nama'] = $result['barang_nama'];
            $data[$key]['satuan'] = $result['satuan'];
            $data[$key]['stok_diterima'] = $value['qty'];
        }

        $html = [
            'html' => view('logistik/gudang/opname_stok/modal/details-opname-stok', [
                'data' => $data,
                'referensi' => $referensi,
                'penanggung_jawab' => $resultHasil['nama_lengkap'],
                'keterangan' => $resultHasil['keterangan'],
                'tanggal' => $resultHasil['tanggal']
            ])
        ];

        echo json_encode($html);
    }

    public function dataBarang()
    {
        $barang = $this->db->table('logistik_barang');

        return DataTable::of($barang)->add('aksi', function ($row) {
            return '<button type="button" id="btnPilihBarang" class="btn btn-sm btn-warning" data-kode="' . htmlspecialchars($row->barang_kode, ENT_QUOTES, 'UTF-8') . '"
                onclick="pilihBarang(this, \'' . htmlspecialchars($row->barang_kode, ENT_QUOTES, 'UTF-8') . '\')">
                <i class="fa-solid fa-circle-plus"></i>
                Pilih
            </button>';
        })->toJson(true);
    }

    public function pilihBarang()
    {
        if ($this->request->isAJAX()) {

            $kode = $this->request->getVar('kode');

            $barang = $this->db->table('logistik_barang');
            $result = $barang->where('barang_kode', $kode)->get()->getResult();

            $data = ['data' => $result];
            echo json_encode($data);
        } else {
        }
    }

    public function simpanOpnameBarang()
    {
        if ($this->request->isAJAX()) {
            $qty = $this->request->getVar('qty');
            $no_opname = $this->request->getVar('no_opname');
            $ket_opname = $this->request->getVar('ket_opname');
            $tanggal = $this->request->getVar('tanggal');

            $mapping = [];

            $logistik_opnamestok = $this->db->table('logistik_opnamestok');
            $insertMaster = $logistik_opnamestok->insert([
                'referensi' => $no_opname,
                'tanggal' => $tanggal,
                'keterangan' => $ket_opname,
                'kat' => 'brg',
                'sessionId' => session()->get('id_session'),
                'created_at' => date('Y-m-d H:i:s')
            ]);
            if ($insertMaster) {
                foreach ($qty as $key => $value) {
                    $logistik_barang = $this->db->table('logistik_barang');
                    $stok_akhir = $logistik_barang->where('barang_kode', $key)->get()->getRowArray();

                    $mapping[] = [
                        'barang_kode' => $key,
                        'qty' => $value,
                        'stok_sebelum' => $stok_akhir['stok_akhir']
                    ];
                    $stok_terbaru = $value;
                    $updateStok = $logistik_barang->where('barang_kode', $key)->update(['stok_akhir' => $stok_terbaru]);
                }
                if ($updateStok) {
                    $logistik_opname_tampung = $this->db->table('logistik_opname_tampung');
                    $insertDetail = $logistik_opname_tampung->insert([
                        'referensi' => $no_opname,
                        'stok_verifikasi' => json_encode($mapping),
                        'created_at' => date('Y-m-d H:i:s')
                    ]);

                    if ($insertDetail) {
                        echo json_encode(['status' => 'success', 'message' => 'Data Berhasil Disimpan']);
                    }
                }
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to save data']);
            }
        }
    }

    public function dataOpnameBarang()
    {

        $tableStokOpname = $this->db->table('logistik_opnamestok')
            ->where('kat', 'brg')
            ->orderBy('created_at', 'DESC');

        return DataTable::of($tableStokOpname)
            ->add('aksi', function ($row) {
                return '
                  <button class="btn btn-sm btn-dark" type="button" onclick="detailsOpnameStok(\'' . $row->referensi . '\')">
                        Detail
                    </button>
            ';
            })
            ->toJson(true);
    }

    public function export_pdf($no_stok_opname)
    {
        $row = $this->db->table('logistik_opnamestok los')
            ->select('los.tanggal, los.referensi')
            ->select('users.nama_lengkap')
            ->join('users', 'users.id_session=los.sessionId')
            ->where('los.referensi', $no_stok_opname)
            ->get()->getRow();

        $html = '';
        $html .= '<div style="margin-top: 10px;">';
        $html .= "<h3 align='center' style='margin-bottom: 0px; margin-top: 0px;'>DETAIL STOK OPNAME</h3>";
        $html .= '<br>';

        $html .= "<table style='margin-bottom: 5px;'>";
        $html .= "<tr>";
        $html .= "<th class='text-left'>NO STOK OPNAME</th>";
        $html .= "<th class='text-center' width='3%'>:</th>";
        $html .= "<th class='text-left'>" . $no_stok_opname . "</th>";
        $html .= "</tr>";
        $html .= "<tr>";
        $html .= "<th class='text-left'>TGL. STOK OPNAME</th>";
        $html .= "<th class='text-center' width='3%'>:</th>";
        $html .= "<th class='text-left'>" . date('d/m/Y', strtotime($row->tanggal)) . "</th>";
        $html .= "</tr>";
        $html .= "<tr>";
        $html .= "<th class='text-left'>PENANGGUNG JAWAB</th>";
        $html .= "<th class='text-center' width='3%'>:</th>";
        $html .= "<th class='text-left'>" . strtoupper($row->nama_lengkap) . "</th>";
        $html .= "</tr>";
        $html .= "</table>";

        $html .= '<div class="table">';
        $html .= '<table>';
        $html .= '<tr>';
        $html .= '<th width="5%">No</th>';
        $html .= '<th>Kode Barang</th>';
        $html .= '<th>Nama Barang</th>';
        $html .= '<th>Stok Sebelumnya</th>';
        $html .= '<th>Stok Dicatat</th>';
        $html .= '</tr>';


        $builder = $this->db->table('logistik_opname_tampung');
        $builder->select('logistik_opname_tampung.stok_verifikasi');
        $builder->where('logistik_opname_tampung.referensi', $no_stok_opname);
        $result = $builder->get()->getRowArray();
        $data = json_decode($result['stok_verifikasi'], true);


        $totalStokSebelum = 0;
        $totalStokDicatat = 0;
        foreach ($data as $key => $value) {
            $nama_barang = $this->db->table('logistik_barang')
                ->where('barang_kode', $value['barang_kode'])
                ->get()->getRowArray()['barang_nama'];

            $html .= '<tr>';
            $html .= '<td class="text-center">' . ($key + 1) . '</td>';
            $html .= '<td class="text-center">' . $value['barang_kode'] . '</td>';
            $html .= '<td>' . $nama_barang . '</td>';
            $html .= '<td class="text-center">' . number_format($value['stok_sebelum'], 0, '.', '.') . '</td>';
            $html .= '<td class="text-center">' . number_format($value['qty'], 0, '.', '.') . '</td>';
            $html .= '</tr>';

            $totalStokSebelum += $value['stok_sebelum'];
            $totalStokDicatat += $value['qty'];
        }

        $html .= '<tr>';
        $html .= '<td class="text-center" colspan="3">Total</td>';
        $html .= '<td class="text-center">' . number_format($totalStokSebelum, 0, '.', '.') . '</td>';
        $html .= '<td class="text-center">' . number_format($totalStokDicatat, 0, '.', '.') . '</td>';
        $html .= '</tr>';

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
