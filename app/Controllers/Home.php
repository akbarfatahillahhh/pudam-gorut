<?php

namespace App\Controllers;

use Irsyadulibad\DataTables\DataTables;
use App\Models\ProfilOrganisasi;
use App\Models\MappingAsesor;
use App\Models\EP;
use App\Models\File;

class Home extends BaseController
{

    public function index()
    {
        return view('landing_page');
    }

    public function ekas()
    {
        return view('dashboard_ekas');
    }

    public function billing()
    {
        return view('dashboard_billing');
    }

    public function get_sidebar()
    {
        $id_session = session()->get('id_session');

        $row = $this->db->table('t_user_akses')
            ->where('id_session', $id_session)
            ->get()
            ->getRowArray();

        echo json_encode(['menu' => $row['menu'] ?? '[]']);
    }

    public function page($page = null)
    {
        $msg = ['page' => view('dashboard/' . $page)];
        echo json_encode($msg);
    }
    public function modal_export_pdf()
    {
        $route = $this->request->getVar('route');
        $data['title'] = 'Export PDF';
        if ($route == 'lpp_penagih') {
            $data['title'] = 'Laporan LPP Penagih';
            $awal = $this->request->getVar('awal');
            $akhir = $this->request->getVar('akhir');

            $data['srcIframe'] = base_url(). '/Home/export_pdf_lpp_penagihan'. '/' . $awal . '/' . $akhir;
        } else if ($route == 'lpp_bms_fastpay') {
            $data['title'] = 'Laporan LPP BMS Fastpay';
            $awal = $this->request->getVar('awal');
            $akhir = $this->request->getVar('akhir');
            
            $data['srcIframe'] = base_url(). '/Home/export_pdf_lpp_bms_fastpay'. '/' . $awal . '/' . $akhir;
        } else if ($route == 'aktifitas_pcm') {
            $data['title'] = 'Laporan Aktifitas PCM';

            $bulan = $this->request->getVar('bulan');
            $tahun = $this->request->getVar('tahun');
            $petugas = $this->request->getVar('petugas');
            $lokasi = $this->request->getVar('lokasi');
            $status = $this->request->getVar('status');
            $keyword = $this->request->getVar('keyword');

            $data['srcIframe'] = base_url(). '/Home/export_pdf_laporan_aktifitas_pcm'. '/' . $bulan . '/' . $tahun . '/' . $petugas . '/' . $lokasi . '/' . $status . '/' . $keyword;
        }


        $msg = ['page' => view('dashboard/pdf', $data)];
        echo json_encode($msg);
    }

    public function get_data_for_card()
    {
        $dateStart = date('2024-01') . '-01';
        $dateNow = date('2024-01-20');

        $totalRekening = $this->db->table('t_tagihan')
            ->selectCount('no_rek', 'total_rekening')
            ->where('bulan_tagihan >=', $dateStart)
            ->where('bulan_tagihan <=', $dateNow)
            ->get()
            ->getRowArray()['total_rekening'] ?? 0;

        $totalCatatMeter = $this->db->table('t_catat_meter')
            ->selectCount('id_pelanggan', 'total_catat_meter')
            ->where('tgl_catat >=', $dateStart)
            ->where('tgl_catat <=', $dateNow)
            ->get()
            ->getRowArray()['total_catat_meter'] ?? 0;

        $totalDiTagih = $this->db->table('t_tagihan')
            ->select('SUM(bayar) as total_di_tagih')
            ->where('tgl_bayar >=', $dateStart)
            ->where('tgl_bayar <=', $dateNow)
            ->where('status', 1)
            ->get()
            ->getRowArray()['total_di_tagih'] ?? 0;

        $data = [
            'total_rekening' => $totalRekening,
            'total_catat_meter' => $totalCatatMeter,
            'total_di_tagih' => $totalDiTagih,
        ];

        echo json_encode($data);
    }

    public function data_lpp_penagih()
    {
        $awal = $this->request->getVar('awal');
        $akhir = $this->request->getVar('akhir');

        $builder = $this->db->table('users');
        $builder->select('users.nama_lengkap, t_kecamatan.kecamatan, users.id_session, t_kecamatan.id_kec');
        $builder->join('t_kecamatan', 'users.id_kec = t_kecamatan.id_kec');
        $builder->where([
            'users.blokir' => 'N',
            'users.mobile_akses' => '1',
            'users.level' => 'mobileloket',
        ]);
        $builder->orderBy('users.id_kec', 'DESC');

        $result = $builder->get()->getResult();


        $html = '<table>';
        $totalRekening = 0;
        $totalRekDitagih = 0;
        $totalTagihan = 0;
        foreach ($result as $key => $value) {
            $id_session = $value->id_session;
            $id_kec = $value->id_kec;

            $jumlahTagihan = $this->db->table('t_tagihan');
            $jumlahTagihan->select('SUM(bayar) as total_tagihan');
            $jumlahTagihan->select('COUNT(id_pemakaian) as jumlah_rek');

            $jumlahTagihan->where('status', '1');
            $jumlahTagihan->where('id_user', $id_session);
            $jumlahTagihan->where("DATE_FORMAT(tgl_bayar, '%Y-%m-%d') >=", $awal);
            $jumlahTagihan->where("DATE_FORMAT(tgl_bayar, '%Y-%m-%d') <=", $akhir);
            $jumlahTagihan = $jumlahTagihan->get()->getRow();

            $totalRekening = $this->db->table('m_pelanggan');
            $totalRekening->select('COUNT(DISTINCT id_pelanggan) as total_rekening');
            $totalRekening->where('id_kec', $id_kec);
            $totalRekening->where('status_aktif', '1');
            $totalRekening = $totalRekening->get()->getRow()->total_rekening;


            $html .= '<tr>';
            $html .= '<td class="text-center text-black">' . ($key + 1) . '</td>';
            $html .= '<td class="text-uppercase text-black">' . $value->nama_lengkap . '</td>';
            $html .= '<td class="text-uppercase text-black text-center">' . $value->kecamatan . '</td>';
            $html .= '<td class="text-center text-black">' . number_format($totalRekening, 0, ',', '.') . '</td>';
            $html .= '<td class="text-center text-black">' . number_format($jumlahTagihan->jumlah_rek, 0, ',', '.') . '</td>';
            $html .= '<td class="text-end text-black fst-italic fw-bolder">Rp. ' . number_format($jumlahTagihan->total_tagihan ?? 0, 0, ',', '.') . '</td>';
            $html .= '<td class="text-center text-black"><span class="badge bg-primary fs-1">' . date('d M Y', strtotime($awal)) . '</span> <span class="badge bg-dark fs-1"> S/D </span> <span class="badge bg-success fs-1">' . date('d M Y', strtotime($akhir)) . '</span></td>';
            $html .= "
                    <td class='text-center text-black'>
                        <div class='dropdown'>
                                <button class='btn btn-sm' type='button' data-bs-toggle='dropdown' aria-expanded='false'>
                                    <i class='fa fa-ellipsis-v text-black'></i>
                                </button>
                                <ul class='dropdown-menu py-0'>
                                    <li>
                                       <a href='javascript:void(0)' class='dropdown-item text-black ' onclick='setPassword()'>
                                            <span class='badge text-start bg-primary fs-2 w-100'>
                                                <i class='fa fa-info-circle pe-1'></i>
                                                LPP
                                            </span>
                                        </a>
                                    </li>
                                    <li>
                                       <a href='javascript:void(0)' class='dropdown-item text-black ' onclick='setPassword()'>
                                            <span class='badge text-start bg-success fs-2 w-100'>
                                                <i class='fa fa-info-circle pe-1'></i>
                                                    LPP Berjalan
                                            </span>
                                        </a>
                                    </li>
                                    <li>
                                       <a href='javascript:void(0)' class='dropdown-item text-black ' onclick='setPassword()'>
                                            <span class='badge text-start bg-danger fs-2 w-100'>
                                                <i class='fa fa-info-circle pe-1'></i>
                                                LPP Tunggakan
                                            </span>
                                        </a>
                                    </li>
                                    <li>
                                       <a href='javascript:void(0)' class='dropdown-item text-black ' onclick='setPassword()'>
                                            <span class='badge text-start bg-dark fs-2 w-100'>
                                                <i class='fa fa-info-circle pe-1'></i>
                                                LPP Air Non Air
                                            </span>
                                        </a>
                                    </li>
                                </ul>
                            </div>

                    </td>";
            $html .= '</tr>';
            
            $totalRekening += $totalRekening;
            $totalRekDitagih += $jumlahTagihan->jumlah_rek;
            $totalTagihan += $jumlahTagihan->total_tagihan ?? 0;
        }

        $html .= '<tr>';
        $html .= '<td class="text-center text-black fw-bolder" colspan="3">Total</td>';
        $html .= '<td class="text-center text-black fw-bolder">' . number_format($totalRekening, 0, ',', '.') . '</td>';
        $html .= '<td class="text-center text-black fw-bolder">' . number_format($totalRekDitagih, 0, ',', '.') . '</td>';
        $html .= '<td class="text-end text-black fst-italic fw-bolder">Rp. ' . number_format($totalTagihan, 0, ',', '.') . '</td>'; 
        $html .= '<td class="text-center text-black fw-bolder" colspan="2"></td>';
        $html .= '</tr>';
        $html .= '</table>';

        echo json_encode(['html' => $html]);
    }

    function export_pdf_lpp_penagihan($awal, $akhir) 
    {
        $html = '';
        $html .= '<div style="margin-top: 10px;">';
        $html .= "<h3 align='center' style='margin-bottom: 0px; margin-top: 0px;'>LAPORAN LPP PENAGIHAN</h3>";
        $html .= "<h3 align='center' style='margin-bottom: 0px; margin-top: 0px;'>PERIODE : " . date('d/m/Y', strtotime($awal)) . " - " . date('d/m/Y', strtotime($akhir)) . "</h3>";

        $builder = $this->db->table('users');
        $builder->select('users.nama_lengkap, t_kecamatan.kecamatan, users.id_session, t_kecamatan.id_kec');
        $builder->join('t_kecamatan', 'users.id_kec = t_kecamatan.id_kec');
        $builder->where([
            'users.blokir' => 'N',
            'users.mobile_akses' => '1',
            'users.level' => 'mobileloket',
        ]);
        $builder->orderBy('users.id_kec', 'DESC');

        $result = $builder->get()->getResult();


        $html .= '<br>';
        $html .= '<div class="table">';
        $html .= '<table>';

        $html .= '<tr>';
        $html .= '<th width="5%">No</th>';
        $html .= '<th>Petguas</th>';
        $html .= '<th>Unit</th>';
        $html .= '<th>Total Rek.</th>';
        $html .= '<th>Jumlah Rek. Ditagih</th>';
        $html .= '<th>Total Tagihan</th>';
        $html .= '</tr>';

        $totalRekening = 0;
        $totalRekDitagih = 0;
        $totalTagihan = 0;
        foreach ($result as $key => $value) {
            $id_session = $value->id_session;
            $id_kec = $value->id_kec;

            $jumlahTagihan = $this->db->table('t_tagihan');
            $jumlahTagihan->select('SUM(bayar) as total_tagihan');
            $jumlahTagihan->select('COUNT(id_pemakaian) as jumlah_rek');

            $jumlahTagihan->where('status', '1');
            $jumlahTagihan->where('id_user', $id_session);
            $jumlahTagihan->where("DATE_FORMAT(tgl_bayar, '%Y-%m-%d') >=", $awal);
            $jumlahTagihan->where("DATE_FORMAT(tgl_bayar, '%Y-%m-%d') <=", $akhir);
            $jumlahTagihan = $jumlahTagihan->get()->getRow();

            $totalRekening = $this->db->table('m_pelanggan');
            $totalRekening->select('COUNT(DISTINCT id_pelanggan) as total_rekening');
            $totalRekening->where('id_kec', $id_kec);
            $totalRekening->where('status_aktif', '1');
            $totalRekening = $totalRekening->get()->getRow()->total_rekening;

            $html .= '<tr>';
            $html .= '<td class="text-center">' . ($key + 1) . '</td>';
            $html .= '<td>' . strtoupper($value->nama_lengkap) . '</td>';
            $html .= '<td class="text-center">' . strtoupper($value->kecamatan) . '</td>';
            $html .= '<td class="text-center">' . number_format($totalRekening, 0, '.', '.') . '</td>';
            $html .= '<td class="text-center">' . number_format($jumlahTagihan->jumlah_rek, 0, '.', '.') . '</td>';
            $html .= '<td class="text-center">Rp. ' . number_format($jumlahTagihan->total_tagihan ?? 0, 0, '.', '.') . '</td>';
            $html .= '</tr>';

            $totalRekening += $totalRekening;
            $totalRekDitagih += $jumlahTagihan->jumlah_rek;
            $totalTagihan += $jumlahTagihan->total_tagihan ?? 0;
        }

        $html .= '<tr>';
        $html .= '<td class="text-center" colspan="3">Total</td>';
        $html .= '<td class="text-center"><b>' . number_format($totalRekening, 0, '.', '.') . '</b></td>';
        $html .= '<td class="text-center"><b>' . number_format($totalRekDitagih, 0, '.', '.') . '</b></td>';
        $html .= '<td class="text-right"><b>Rp. ' . number_format($totalTagihan, 0, '.', '.') . '</b></td>';
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
            'orientation' => 'P',
            'default_font_size' => 10,
            'default_font' => 'arial',
            'tempDir' => __DIR__ . '/temp',
        ]);

        $mpdf->WriteHTML($page);
        $mpdf->SetTitle("LAPORAN LPP PENAGIHAN");
        $this->response->setHeader('Content-Type', 'application/pdf');
        $mpdf->Output("PDF", 'I');
    }
    
    public function data_lpp_bms_fastpay()
    {
        $awal = $this->request->getVar('awal');
        $akhir = $this->request->getVar('akhir');

        $builder = $this->db->table('t_kecamatan');
        $builder->select('t_kecamatan.kecamatan, t_kecamatan.id_kec');
        $builder->orderBy('id_kec', 'ASC');

        $result = $builder->get()->getResult();


        $html = '<table>';
        $totalTagihan = 0;
        $totalRekening = 0;
        foreach ($result as $key => $value) {
            $id_session = "2711e4ad770056a1a376cb6d9037e2a6";
            $id_kec = $value->id_kec;

            $jumlahTagihan = $this->db->table('t_tagihan');
            $jumlahTagihan->select('SUM(bayar) as total_tagihan');
            $jumlahTagihan->select('COUNT(id_pemakaian) as jumlah_rek');

            $jumlahTagihan->where('status', '1');
            $jumlahTagihan->where('id_kec', $id_kec);
            $jumlahTagihan->where('id_user', $id_session);
            $jumlahTagihan->where("DATE_FORMAT(tgl_bayar, '%Y-%m-%d') >=", $awal);
            $jumlahTagihan->where("DATE_FORMAT(tgl_bayar, '%Y-%m-%d') <=", $akhir);
            $jumlahTagihan = $jumlahTagihan->get()->getRow();


            $html .= '<tr>';
            $html .= '<td class="text-center text-black">' . ($key + 1) . '</td>';
            $html .= '<td class="text-uppercase text-black">' . $value->kecamatan . '</td>';
            $html .= '<td class="text-center text-black">' . number_format($jumlahTagihan->jumlah_rek, 0, ',', '.') . '</td>';
            $html .= '<td class="text-end text-black fst-italic fw-bolder">Rp. ' . number_format($jumlahTagihan->total_tagihan ?? 0, 0, ',', '.') . '</td>';
            $html .= "
                    <td class='text-center text-black'>
                        <div class='dropdown'>
                                <button class='btn btn-sm' type='button' data-bs-toggle='dropdown' aria-expanded='false'>
                                    <i class='fa fa-ellipsis-v text-black'></i>
                                </button>
                                <ul class='dropdown-menu py-0'>
                                    <li>
                                       <a href='javascript:void(0)' class='dropdown-item text-black ' onclick='setPassword()'>
                                            <span class='badge text-start bg-primary fs-2 w-100'>
                                                <i class='fa fa-info-circle pe-1'></i>
                                                LPP
                                            </span>
                                        </a>
                                    </li>
                                    <li>
                                       <a href='javascript:void(0)' class='dropdown-item text-black ' onclick='setPassword()'>
                                            <span class='badge text-start bg-success fs-2 w-100'>
                                                <i class='fa fa-info-circle pe-1'></i>
                                                    LPP Berjalan
                                            </span>
                                        </a>
                                    </li>
                                    <li>
                                       <a href='javascript:void(0)' class='dropdown-item text-black ' onclick='setPassword()'>
                                            <span class='badge text-start bg-danger fs-2 w-100'>
                                                <i class='fa fa-info-circle pe-1'></i>
                                                LPP Tunggakan
                                            </span>
                                        </a>
                                    </li>
                                    <li>
                                       <a href='javascript:void(0)' class='dropdown-item text-black ' onclick='setPassword()'>
                                            <span class='badge text-start bg-dark fs-2 w-100'>
                                                <i class='fa fa-info-circle pe-1'></i>
                                                LPP Air Non Air
                                            </span>
                                        </a>
                                    </li>
                                </ul>
                            </div>

                    </td>";
            $html .= '</tr>';

            $totalTagihan += $jumlahTagihan->total_tagihan ?? 0;
            $totalRekening += $jumlahTagihan->jumlah_rek;
        }

        $html .= '<tr>';
        $html .= '<td class="text-center text-black fw-bolder" colspan="2">Total</td>';
        $html .= '<td class="text-center text-black fw-bolder">' . number_format($totalRekening, 0, ',', '.') . '</td>';
        $html .= '<td class="text-end text-black fst-italic fw-bolder">Rp. ' . number_format($totalTagihan, 0, ',', '.') . '</td>';
        $html .= '<td></td>';
        $html .= '</tr>';

        $html .= '</table>';

        echo json_encode(['html' => $html]);
    }

    function export_pdf_lpp_bms_fastpay($awal, $akhir) 
    {
        $html = '';
        $html .= '<div style="margin-top: 10px;">';
        $html .= "<h3 align='center' style='margin-bottom: 0px; margin-top: 0px;'>LAPORAN LPP BMS FASTPAY</h3>";
        $html .= "<h3 align='center' style='margin-bottom: 0px; margin-top: 0px;'>PERIODE : " . date('d/m/Y', strtotime($awal)) . " - " . date('d/m/Y', strtotime($akhir)) . "</h3>";

        $builder = $this->db->table('t_kecamatan');
        $builder->select('t_kecamatan.kecamatan, t_kecamatan.id_kec');
        $builder->orderBy('id_kec', 'ASC');

        $result = $builder->get()->getResult();


        $html .= '<br>';
        $html .= '<div class="table">';
        $html .= '<table>';

        $html .= '<tr>';
        $html .= '<th width="5%">No</th>';
        $html .= '<th>Unit</th>';
        $html .= '<th>Jumlah Rekening</th>';
        $html .= '<th>Total Penerimaan</th>';
        $html .= '</tr>';

        $totalTagihan = 0;
        $totalRekening = 0;
        foreach ($result as $key => $value) {
            $id_session = "2711e4ad770056a1a376cb6d9037e2a6";
            $id_kec = $value->id_kec;

            $jumlahTagihan = $this->db->table('t_tagihan');
            $jumlahTagihan->select('SUM(bayar) as total_tagihan');
            $jumlahTagihan->select('COUNT(id_pemakaian) as jumlah_rek');

            $jumlahTagihan->where('status', '1');
            $jumlahTagihan->where('id_kec', $id_kec);
            $jumlahTagihan->where('id_user', $id_session);
            $jumlahTagihan->where("DATE_FORMAT(tgl_bayar, '%Y-%m-%d') >=", $awal);
            $jumlahTagihan->where("DATE_FORMAT(tgl_bayar, '%Y-%m-%d') <=", $akhir);
            $jumlahTagihan = $jumlahTagihan->get()->getRow();

            $html .= '<tr>';
            $html .= '<td class="text-center">' . ($key + 1) . '</td>';
            $html .= '<td class="text-center">' . strtoupper($value->kecamatan) . '</td>';
            $html .= '<td class="text-center">' . number_format($jumlahTagihan->jumlah_rek, 0, '.', '.') . '</td>';
            $html .= '<td class="text-right">Rp. ' . number_format($jumlahTagihan->total_tagihan ?? 0, 0, '.', '.') . '</td>';
            $html .= '</tr>';

            $totalTagihan += $jumlahTagihan->total_tagihan ?? 0;
            $totalRekening += $jumlahTagihan->jumlah_rek;
        }

        $html .= '<tr>';
        $html .= '<td class="text-center" colspan="2">Total</td>';
        $html .= '<td class="text-center"><b>' . number_format($totalRekening, 0, '.', '.') . '</b></td>';
        $html .= '<td class="text-right"><b>Rp. ' . number_format($totalTagihan, 0, '.', '.') . '</b></td>';
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
            'orientation' => 'P',
            'default_font_size' => 10,
            'default_font' => 'arial',
            'tempDir' => __DIR__ . '/temp',
        ]);

        $mpdf->WriteHTML($page);
        $mpdf->SetTitle("LAPORAN LPP PENAGIHAN");
        $this->response->setHeader('Content-Type', 'application/pdf');
        $mpdf->Output("PDF", 'I');
    }

    public function data_catat_meter(){
        $awal = $this->request->getVar('awal');
        $akhir = $this->request->getVar('akhir');
        $petugas = $this->request->getVar('petugas');
        $status = $this->request->getVar('status');
        $preview = $this->request->getVar('preview');

        if ($preview == 'detail') {
            $builder = $this->db->table('view_catat_meter');
            $builder->where('TANGGALCATAT >=', $awal);
            $builder->where('TANGGALCATAT <=', $akhir);
    
            if ($petugas != 'all') {
                $builder->where('PETUGASSESSION', $petugas);
            }
    
            if ($status != 'all') {
                $builder->where('STATUSVERIFIKASI', $status);
            }
    
            $builder->orderBy('KECAMATAN', 'ASC');
            $builder->orderBy('NAMA_PELANGGAN','ASC');
            $builder->orderBy('TANGGALCATAT','DESC');
    
            $result = $builder->get()->getResult();
        } else if ($preview == 'simpel') {
            $builder = $this->db->table('view_catat_meter');
            $builder->select('COUNT(DISTINCT IDCATAT) as JUMLAH_CATAT, NMKECAMATAN, NAMAPETUGAS');
            $builder->where('TANGGALCATAT >=', $awal);
            $builder->where('TANGGALCATAT <=', $akhir);
    
            if ($petugas != 'all') {
                $builder->where('PETUGASSESSION', $petugas);
            }
    
            if ($status != 'all') {
                $builder->where('STATUSVERIFIKASI', $status);
            }
    
            $builder->orderBy('NMKECAMATAN', 'ASC');
            $builder->groupBy('NMKECAMATAN');
            $builder->groupBy('NAMAPETUGAS');
    
            $result = $builder->get()->getResult();
        }

        $petugas = $this->db->table('view_catat_meter');
        $petugas->select('MAX(NAMAPETUGAS) AS NAMAPETUGAS, PETUGASSESSION');
        $petugas->where('TANGGALCATAT >=', $awal);
        $petugas->where('TANGGALCATAT <=', $akhir);
        $petugas->groupBy('PETUGASSESSION');
        $petugas = $petugas->get()->getResult();

        echo json_encode([
            'data' => $result,
            'petugas' => $petugas
        ]);
    }

    public function modal_verifikasi_catat_meter(){
        $id_catat_meter = $this->request->getVar('id_catat_meter');

        $data['data_meter'] = $this->db->table('t_catat_meter')
            ->where('id', $id_catat_meter)
            ->get()->getRowArray();

        $data['data_pelanggan'] = $this->db->table('m_pelanggan')
            ->where('id_pelanggan', $data['data_meter']['id_pelanggan'])
            ->get()->getRowArray();

        $data['data_tagihan'] = $this->db->table('t_tagihan')
            ->where('id_pelanggan', $data['data_meter']['id_pelanggan'])
            ->where('DATE_FORMAT(bulan_tagihan, "%Y-%m")', date('Y-m', strtotime($data['data_meter']['tgl_catat'])))
            ->get()->getRowArray();

        $foto_meter_bulan_lalu = $this->db->table('t_catat_meter')
            ->select('foto_meter, foto_rumah')
            ->where('id_pelanggan', $data['data_meter']['id_pelanggan'])
            ->where('DATE_FORMAT(tgl_catat, "%Y-%m")', date('Y-m', strtotime('-1 month', strtotime($data['data_meter']['tgl_catat']))))
            ->get()->getRowArray();

        $data['foto_meter_bulan_lalu']['foto_rumah'] = ($foto_meter_bulan_lalu) ? $foto_meter_bulan_lalu['foto_rumah'] : 'no_photo.png';
        $data['foto_meter_bulan_lalu']['foto_meter'] = ($foto_meter_bulan_lalu) ? $foto_meter_bulan_lalu['foto_meter'] : 'no_photo.png';


        $data['foto_meter_dua_bulan_lalu'] = $this->db->table('t_catat_meter')
            ->select('foto_meter, foto_rumah')
            ->where('id_pelanggan', $data['data_meter']['id_pelanggan'])
            ->where('DATE_FORMAT(tgl_catat, "%Y-%m")', date('Y-m', strtotime('-2 month', strtotime($data['data_meter']['tgl_catat']))))
            ->get()->getRowArray();

        $data['foto_meter_dua_bulan_lalu']['foto_rumah'] = ($data['foto_meter_dua_bulan_lalu']) ? $data['foto_meter_dua_bulan_lalu']['foto_rumah'] : 'no_photo.png';
        $data['foto_meter_dua_bulan_lalu']['foto_meter'] = ($data['foto_meter_dua_bulan_lalu']) ? $data['foto_meter_dua_bulan_lalu']['foto_meter'] : 'no_photo.png';

        $page['page'] = view('dashboard/verifikasi', $data);

        echo json_encode( $page);
    }

    public function hapus_catat_meter()
    {
        $id_catat_meter = $this->request->getVar('id_catat_meter');

        $t_catat_meter = $this->db->table('t_catat_meter')
            ->where('id', $id_catat_meter)
            ->delete();

        echo json_encode(true);
    }

    public function data_lpp_unit(){}
    public function data_laporan_aktifitas_pcm()
    {
        $bulan = $this->request->getVar('bulan');
        $tahun = $this->request->getVar('tahun');
        $petugas = $this->request->getVar('petugas');
        $lokasi = $this->request->getVar('lokasi');
        $keyword = $this->request->getVar('keyword');
        $status = $this->request->getVar('status');

        $html = '';
        $kelurahan = $this->db->table('m_pelanggan mp')
            ->select('kel.kelurahan, kel.id_kelurahan AS id_kel')
            ->select('kec.kecamatan')
            ->join('t_kelurahan kel', 'mp.id_kelurahan = kel.id_kelurahan')
            ->join('t_kecamatan kec', 'kec.id_kec = kel.id_kec')
            ->where('mp.status_aktif', '1')
            ->where('mp.petugas_meter', $petugas);

        if ($lokasi != 'all') {
            $kelurahan->where('kel.id_kelurahan', $lokasi);
        }
        $kelurahan->groupBy('kel.id_kelurahan')->orderBy('kel.id_kelurahan', 'ASC');
        $kelurahan = $kelurahan->get()->getResultArray();
            

        $no = 1;
        foreach ($kelurahan as $valueKel) {
            $pelanggan = $this->db->table('m_pelanggan mp')
                ->select('mp.id_pelanggan, mp.no_sambungan, mp.nama_pelanggan, mp.status_aktif')
                ->select('kec.kecamatan, kel.kelurahan')
                ->select('mjp.jenis_pelanggan')
                ->join('t_kecamatan kec', 'mp.id_kec = kec.id_kec')
                ->join('t_kelurahan kel', 'mp.id_kelurahan = kel.id_kelurahan')
                ->join('m_jenis_pelanggan mjp', 'mp.id_jenis = mjp.id_jenis')
                ->where('mp.petugas_meter', $petugas)
                ->where('mp.id_kelurahan', $valueKel['id_kel']);

            if ($keyword != '') {
                $pelanggan->like('mp.nama_pelanggan', $keyword);
            }
            
            $pelanggan = $pelanggan->get()->getResultArray();

                $html .= "<tr>";
                $html .= "<td colspan='100%' class='bg-dark text-center text-white'>" . $valueKel['kecamatan'] . " - " . $valueKel['kelurahan'] . "</td>";
                $html .= "</tr>";

                $jumlahPelanggan = 0;
                foreach ($pelanggan as $keyPel => $valuePel) {
                    $dataCatat = $this->db->table('view_catat_meter')
                        ->where('MONTH(TANGGALCATAT)', $bulan)
                        ->where('YEAR(TANGGALCATAT)', $tahun)
                        ->where('PETUGASSESSION', $petugas)
                        ->where('IDPELANGGAN', $valuePel['id_pelanggan'])
                        ->get()
                        ->getRowArray();

                    $rekening = $this->db->table('t_tagihan')
                        ->where('MONTH(bulan_tagihan)', $bulan)
                        ->where('YEAR(bulan_tagihan)', $tahun)
                        ->where('id_pelanggan', $valuePel['id_pelanggan'])
                        ->get()
                        ->getRowArray();

                        $statusPelanggan = ($valuePel['status_aktif'] == 1) ? "<span class=\"badge bg-success fs-1 rounded py-1\">Aktif</span>" : "<span class=\"badge bg-danger fs-1 rounded py-1\">Non Aktif</span>";
                        if ($dataCatat && $rekening) {
                            $tglCatat = ($dataCatat['TANGGALCATAT'] > 0) ? date('d M Y', strtotime($dataCatat['TANGGALCATAT'])) : date('d M Y', strtotime($rekening['bulan_tagihan']));
                            $angkaMeter = ($dataCatat['ANGKA_METER'] > 0) ? $dataCatat['ANGKA_METER'] : $rekening['pemakaian_air'];
                            $statusMeter = "<span class='badge bg-success fs-1'>Tercatat</span>";
                        } else {
                            $tglCatat = "-";
                            $angkaMeter = "-";
                            $statusMeter = "<span class='badge bg-warning text-black fs-1'>Belum dicatat</span>";
                        }
                        
                        $html .= "<tr>";
                        $html .= "<td class='text-center align-middle text-black'>" . ($no++)  . "</td>";
                        $html .= "<td class='text-center align-middle text-black'>" . $valuePel['no_sambungan'] . "</td>";
                        $html .= "<td class='align-middle text-black'>" . $valuePel['nama_pelanggan'] . " " . $statusPelanggan . " </td>";
                        $html .= "<td class='text-center align-middle text-black'>" . $valuePel['kecamatan'] . "</td>";
                        $html .= "<td class='text-center align-middle text-black'>" . $valueKel['kelurahan'] . "</td>";
                        $html .= "<td class='text-center align-middle text-black'>" . $valuePel['jenis_pelanggan'] . "</td>";
                        $html .= "<td class='text-center align-middle text-black'>" . $angkaMeter . "</td>";
                        $html .= "<td class='text-center align-middle text-black'>" . $tglCatat . "</td>";
                        $html .= "<td class='text-center align-middle text-black'>" . $statusMeter . "</td>";
                        $html .= "</tr>"; 

                        $jumlahPelanggan++;
                }
                $html .= "<tr>";
                $html .= "<td class='text-center align-middle text-black fw-bolder' colspan='7'>Total Pelanggan</td>";
                $html .= "<td class='text-center align-middle text-black fw-bolder' colspan='2'>" . number_format($jumlahPelanggan, 0, '.', '.')  . "</td>";
                $html .= "</tr>";
        }

        $dataKelurahan = $this->db->table('m_pelanggan mp')
            ->select('kel.kelurahan, kel.id_kelurahan AS id_kel')
            ->select('kec.kecamatan')
            ->join('t_kelurahan kel', 'mp.id_kelurahan = kel.id_kelurahan')
            ->join('t_kecamatan kec', 'kec.id_kec = kel.id_kec')
            ->where('mp.status_aktif', '1')
            ->where('mp.petugas_meter', $petugas)
            ->groupBy('kel.id_kelurahan')->orderBy('kel.id_kelurahan', 'ASC')
            ->get()->getResultArray();

        echo json_encode([
            'html' =>  $html,
            'dataKelurahan' => $dataKelurahan
        ]);
    }

    function export_pdf_laporan_aktifitas_pcm($bulan, $tahun, $petugas, $lokasi, $status, $keyword = null)
    {
        $html = '';
        $html .= '<div style="margin-top: 10px;">';
        $html .= "<h3 align='center' style='margin-bottom: 0px; margin-top: 0px;'>LAPORAN AKTIFITAS PCM</h3>";

        $nama_petugas = $this->db->table('users')->select('nama_lengkap')->where('id_session', $petugas)->get()->getRowArray()['nama_lengkap'];

        $html .= '<br>';

        $html .= "<table width='100%'>";
        $html .= "<tr>";
        $html .= "<td width='12%'><b>PETGUAS</b></td>";
        $html .= "<td width='1%'><b>:</b></td>";
        $html .= "<td><b>" . strtoupper($nama_petugas) . "</b></td>";
        $html .= "</tr>";
        $html .= "<tr>";
        $html .= "<td width='12%'><b>PERIODE</b></td>";
        $html .= "<td width='1%'><b>:</b></td>";
        $html .= "<td><b>" . strtoupper(date('F Y', strtotime("$tahun-$bulan-01"))) . "</b></td>";
        $html .= "</tr>";

        if ($status != 'all') {
            $html .= "<tr>";
            $html .= "<td width='12%'><b>STATUS</b></td>";
            $html .= "<td width='1%'><b>:</b></td>";
            $html .= "<td><b>" . ($status == 1) ? "TERCATAT" : "BELUM DICATAT" . "</b></td>";
            $html .= "</tr>";
        }

        $html .= "</table>";

        $html .= '<br>';
        $html .= '<div class="table">';
        $html .= '<table>';

        $html .= '<tr>';
        $html .= '<th width="5%">No</th>';
        $html .= '<th>No. Sambungan</th>';
        $html .= '<th>Nama Pelanggan</th>';
        $html .= '<th>Kecamatan</th>';
        $html .= '<th>Desa/Kelurahan</th>';
        $html .= '<th>Golongan</th>';
        $html .= '<th>Angka Meter</th>';
        $html .= '<th>Tgl. Pencatatan</th>';
        $html .= '<th>Status Pelanggan</th>';
        if ($status == 'all') {
            $html .= '<th>Status Meter</th>';
        }
        $html .= '</tr>';

        $kelurahan = $this->db->table('m_pelanggan mp')
            ->select('kel.kelurahan, kel.id_kelurahan AS id_kel')
            ->select('kec.kecamatan')
            ->join('t_kelurahan kel', 'mp.id_kelurahan = kel.id_kelurahan')
            ->join('t_kecamatan kec', 'kec.id_kec = kel.id_kec')
            ->where('mp.status_aktif', '1')
            ->where('mp.petugas_meter', $petugas);
        if ($lokasi != 'all') {
            $kelurahan->where('kel.id_kelurahan', $lokasi);
        }
        $kelurahan->groupBy('kel.id_kelurahan')->orderBy('kel.id_kelurahan', 'ASC');
        $kelurahan = $kelurahan->get()->getResultArray();

        $no = 1;
        foreach ($kelurahan as $valueKel) {
            $pelanggan = $this->db->table('m_pelanggan mp')
                ->select('mp.id_pelanggan, mp.no_sambungan, mp.nama_pelanggan, mp.status_aktif')
                ->select('kec.kecamatan, kel.kelurahan')
                ->select('mjp.jenis_pelanggan')
                ->join('t_kecamatan kec', 'mp.id_kec = kec.id_kec')
                ->join('t_kelurahan kel', 'mp.id_kelurahan = kel.id_kelurahan')
                ->join('m_jenis_pelanggan mjp', 'mp.id_jenis = mjp.id_jenis')
                ->where('mp.petugas_meter', $petugas)
                ->where('mp.id_kelurahan', $valueKel['id_kel']);

            if ($keyword != '') {
                $pelanggan->like('mp.nama_pelanggan', $keyword);
            }
            
            $pelanggan = $pelanggan->get()->getResultArray();

            $html .= "<tr>";
            $html .= "<td colspan='10' class='text-center' style='background:rgb(224, 186, 186)'>" . $valueKel['kecamatan'] . " - " . $valueKel['kelurahan'] . "</td>";
            $html .= "</tr>";

            $jumlahPelanggan = 0;
            foreach ($pelanggan as $keyPel => $valuePel) {
                $dataCatat = $this->db->table('view_catat_meter')
                        ->where('MONTH(TANGGALCATAT)', $bulan)
                        ->where('YEAR(TANGGALCATAT)', $tahun)
                        ->where('PETUGASSESSION', $petugas)
                        ->where('IDPELANGGAN', $valuePel['id_pelanggan'])
                        ->get()
                        ->getRowArray();

                $rekening = $this->db->table('t_tagihan')
                    ->where('MONTH(bulan_tagihan)', $bulan)
                    ->where('YEAR(bulan_tagihan)', $tahun)
                    ->where('id_pelanggan', $valuePel['id_pelanggan'])
                    ->get()
                    ->getRowArray();

                    $statusPelanggan = ($valuePel['status_aktif'] == 1) ? "<b style='color: green'>AKTIF</b>" : "<b style='color: red'>Non Aktif</b>";
                    if ($dataCatat && $rekening) {
                        $tglCatat = ($dataCatat['TANGGALCATAT'] > 0) ? date('d M Y', strtotime($dataCatat['TANGGALCATAT'])) : date('d M Y', strtotime($rekening['bulan_tagihan']));
                        $angkaMeter = ($dataCatat['ANGKA_METER'] > 0) ? $dataCatat['ANGKA_METER'] : $rekening['pemakaian_air'];
                        $statusMeter = "<b style='color: green'>TERCATAT</b>";
                    } else {
                        $tglCatat = "-";
                        $angkaMeter = "-";
                        $statusMeter = "<b style='color: red'>BELUM DICATAT</b>";
                    }

                $html .= "<tr>";
                $html .= "<td class='text-center'>" . ($no++)  . "</td>";
                $html .= "<td class='text-center'>" . $valuePel['no_sambungan'] . "</td>";
                $html .= "<td>" . $valuePel['nama_pelanggan'] . "</td>";
                $html .= "<td class='text-center'>" . $valuePel['kecamatan'] . "</td>";
                $html .= "<td class='text-center'>" . $valueKel['kelurahan'] . "</td>";
                $html .= "<td class='text-center'>" . $valuePel['jenis_pelanggan'] . "</td>";
                $html .= "<td class='text-center'>" . $angkaMeter . "</td>";
                $html .= "<td class='text-center'>" . $tglCatat . "</td>";
                $html .= "<td class='text-center'>" . $statusPelanggan . "</td>";
                $html .= "<td class='text-center'>" . $statusMeter . "</td>";
                $html .= "</tr>"; 

                $jumlahPelanggan++;
            }

            $html .= "<tr>";
            $html .= "<td class='text-center' colspan='8'><b>Total Pelanggan</b></td>";
            $html .= "<td class='text-center' colspan='2'><b>" . number_format($jumlahPelanggan, 0, '.', '.')  . "</b></td>";
            $html .= "</tr>";

        }

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
        $mpdf->SetTitle("LAPORAN LPP PENAGIHAN");
        $this->response->setHeader('Content-Type', 'application/pdf');
        $mpdf->Output("PDF", 'I');
    }

}
