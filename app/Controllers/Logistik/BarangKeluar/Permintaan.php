<?php

namespace App\Controllers\Logistik\BarangKeluar;

use App\Controllers\BaseController;
use Hermawan\DataTables\DataTable;

class Permintaan extends BaseController
{
    var $title;
    public function __construct()
    {
        $this->title = 'Permintaan';
    }

    public function index()
    {
        $data['title'] = $this->title;
        return view('/logistik/barang_keluar/entry_permintaan/main', $data);
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
            ->table('logistik_permintaan lp')
            ->select(
                'lp.no_permintaan,
                lp.tanggal,
                lp.penerima'
            );

        return DataTable::of($query)
            ->add('tanggal', function ($row) {
                return date('d/m/Y', strtotime($row->tanggal));
            })
            ->add('aksi', function ($row) {
                return "<button type='button' class='btn btn-sm btn-dark' onclick='edit(\"" . $row->no_permintaan . "\")'>
                            Detail
                        </button>";
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
        $logistik_permintaan_cek = $this->db->table('logistik_permintaan_cek');

        foreach ($row as $key => $value) {
            $jumlah_stok_akhir = (int) $value['stokAkhir'] - (int) $value['qty'];

            if ($value['qty'] != null || $value['qty'] != '' || $value['qty'] > 0) {
                $tambahDataLogistik = $logistik_permintaan_tampung->insert([
                    'no_permintaan' => $no_permintaan,
                    'barang_id' => $value['barang'],
                    'stok_awal' => $value['stokAkhir'],
                    'stok_diberikan' => $value['qty'],
                    'jumlah_stok_akhir' => $jumlah_stok_akhir,
                ]);

                if ($tambahDataLogistik) {
                    $insertCek = $logistik_permintaan_cek->insert([
                        'no_permintaan' => $no_permintaan,
                        'barang_id' => $value['barang'],
                        'verifikasi_stok' => '0',
                        'created_at' => date('Y-m-d H:i:s')
                    ]);
                }
            }
        }

        $tambahDataLogistikPermintaan = $logistik_permintaan->insert([
            'no_permintaan' => $no_permintaan,
            'tanggal' => $tanggal_permintaan,
            'penerima' => $penerima,
            'userid' => session()->get('id_session') ?? 0,
        ]);
        echo json_encode(['status' => 'success', 'message' => 'Data Berhasil Disimpan']);
    }

    public function hapusItems()
    {
        $id = $this->request->getVar('id');
    }
    public function ambilData()
    {
        $id = $this->request->getVar('id');
        $data = $this->db->table('logistik_barang')->select('*')->where('id', $id)->get()->getRow();

        if ($data) {
            echo json_encode($data);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Data Tidak Ditemukan']);
        }
    }

    public function edit()
    {
        $no_permintaan = $this->request->getVar('id');

        $logistik_permintaan = $this->db->table('logistik_permintaan')->where('no_permintaan', $no_permintaan)->get()->getRowArray();

        $logistik_permintaan_tampung = $this->db->table('logistik_permintaan_tampung lpt')
            ->select('lpt.barang_id, lpt.stok_awal as stok_akhir, lpt.stok_diberikan as stok_diminta')
            ->select('lpc.verifikasi_stok as stok_diberikan, lpc.status')
            ->select('lb.barang_nama, lb.satuan')
            ->join('logistik_permintaan_cek lpc', 'lpt.no_permintaan = lpc.no_permintaan AND lpt.barang_id = lpc.barang_id')
            ->join('logistik_barang lb', 'lpt.barang_id = lb.id')
            ->where('lpt.no_permintaan', $no_permintaan)
            ->orderBy('lpc.status', 'ASC')
            ->get()->getResultArray();


        $data = [
            'title' => $this->title,
            'id' => $no_permintaan,
            'logistik_permintaan' => $logistik_permintaan,
            'logistik_permintaan_tampung' => $logistik_permintaan_tampung,
        ];

        $html = ['html' => view('/logistik/barang_keluar/entry_permintaan/modal/edit', $data)];
        echo json_encode($html);
    }

    public function penerima()
    {
        if ($this->request->isAJAX()) {
            $search = $this->request->getVar('search');

            $table = $this->db->table('pegawai')->select('NAMA, NAMA as text');
            if ($search != '') {
                $hasil = $table->like('NAMA', $search)->orLike('NIK', $search)->get();
            } else {
                $hasil = $table->get();
            }
            if ($hasil->getNumRows() > 0) {
                $list = [];
                $key = 0;

                foreach ($hasil->getResultArray() as $row) {
                    $list[$key]['id'] = $row['NAMA'];
                    $list[$key]['text'] = $row['NAMA'];
                    $key++;
                }
                echo json_encode($list);
            }
        }
    }

    function export_pdf($no_permintaan)
    {
        $html = '';
        $html .= '<div style="margin-top: 10px;">';
        $html .= "<h3 align='center' style='margin-bottom: 0px; margin-top: 0px;'>LAPORAN DETAIL PERMINTAAN</h3>";

        $logistik_permintaan = $this->db->table('logistik_permintaan')->where('no_permintaan', $no_permintaan)->get()->getRowArray();

        $html .= '<br>';


        $html .= "<table width='100%'>";
        $html .= "<tr>";
        $html .= "<td width='12%'><b>NO. PERMINTAAN</b></td>";
        $html .= "<td width='1%'><b>:</b></td>";
        $html .= "<td><b>" . $logistik_permintaan['no_permintaan'] . "</b></td>";
        $html .= "</tr>";
        $html .= "<tr>";
        $html .= "<td width='12%'><b>TANGGAL</b></td>";
        $html .= "<td width='1%'><b>:</b></td>";
        $html .= "<td><b>" . date('d/m/Y', strtotime($logistik_permintaan['tanggal'])) . "</b></td>";
        $html .= "</tr>";
        $html .= "<tr>";
        $html .= "<td width='12%'><b>PENERIMA</b></td>";
        $html .= "<td width='1%'><b>:</b></td>";
        $html .= "<td><b>" . $logistik_permintaan['penerima'] . "</b></td>";
        $html .= "</tr>";
        $html .= "</table>";

        $html .= '<br>';
        $html .= '<div class="table">';
        $html .= '<table>';

        $html .= '<tr>';
        $html .= '<th width="5%">No</th>';
        $html .= '<th>Barang</th>';
        $html .= '<th>Satuan</th>';
        $html .= '<th>Stok Akhir</th>';
        $html .= '<th>Jml. Permintaan</th>';
        $html .= '<th>Jml. Diberikan</th>';
        $html .= '<th>Status</th>';
        $html .= '</tr>';

        $logistik_permintaan_tampung = $this->db->table('logistik_permintaan_tampung lpt')
            ->select('lpt.barang_id, lpt.stok_awal as stok_akhir, lpt.stok_diberikan as stok_diminta')
            ->select('lpc.verifikasi_stok as stok_diberikan, lpc.status')
            ->select('lb.barang_nama, lb.satuan')
            ->join('logistik_permintaan_cek lpc', 'lpt.no_permintaan = lpc.no_permintaan AND lpt.barang_id = lpc.barang_id')
            ->join('logistik_barang lb', 'lpt.barang_id = lb.id')
            ->where('lpt.no_permintaan', $no_permintaan)
            ->orderBy('lpc.status', 'ASC')
            ->get()->getResultArray();

        $totalStokDiminta = 0;
        $totalStokDiberikan = 0;
        foreach ($logistik_permintaan_tampung as $key => $value) {
            $html .= '<tr>';
            $html .= '<td class="text-center">' . ($key + 1) . '</td>';
            $html .= '<td>' . $value['barang_nama'] . '</td>';
            $html .= '<td class="text-center">' . $value['satuan'] . '</td>';
            $html .= '<td class="text-center">' . number_format($value['stok_akhir'], 0, '.', '.') . '</td>';
            $html .= '<td class="text-center">' . number_format($value['stok_diminta'], 0, '.', '.') . '</td>';
            $html .= '<td class="text-center">' . number_format($value['stok_diberikan'], 0, '.', '.') . '</td>';

            if ($value['status'] == 'Y') {
                $status = '<b style="color: green">DISETUJUI</b>';
            } else if ($value['status'] == 'N') {
                $status = '<b style="color: red">DITOLAK</b>';
            } else {
                $status = '<b style="color: orange">PENDING</b>';
            }

            $html .= '<td class="text-center">' . $status . '</td>';
            $html .= '</tr>';

            $totalStokDiminta += $value['stok_diminta'];
            $totalStokDiberikan += $value['stok_diberikan'];
        }

        $html .= '<tr>';
        $html .= '<td class="text-center" colspan="4">Total Keselurahan</td>';
        $html .= '<td class="text-center"><b>' . number_format($totalStokDiminta, 0, '.', '.') . '</b></td>';
        $html .= '<td class="text-center"><b>' . number_format($totalStokDiberikan, 0, '.', '.') . '</b></td>';
        $html .= '<td style="background-color: black"></td>';
        $html .= '</tr>';

        $html .= '</table>';
        $html .= '</div>';

        $html .= "</div>";

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
        $mpdf->SetTitle("LAPORAN DETAIL PENERMIAAN");
        $this->response->setHeader('Content-Type', 'application/pdf');
        $mpdf->Output("PDF", 'I');
    }
}
