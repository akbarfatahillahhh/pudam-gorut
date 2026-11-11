<?php

namespace App\Controllers\Laporan;

use App\Controllers\BaseController;
use Hermawan\DataTables\DataTable;

class DrdAir extends BaseController
{
    var $title;
    public function __construct()
    {
        $this->title = "DRD Air";
    }

    public function index()
    {
        $data['title'] = $this->title;
        return view('/laporan/drd_air/main', $data);
    }

    public function loadPage($page)
    {
        if ($page == 'all') {
            $msg = ['page' =>  view('/laporan/drd_air/page/all')];
        } else if ($page == 'perPetugas') {
            $msg = ['page' =>  view('/laporan/drd_air/page/per_petugas')];
        } else if ($page == 'perUnit') {
            $msg = ['page' =>  view('/laporan/drd_air/page/per_unit')];
        } else if ($page == 'perKel') {
            $msg = ['page' =>  view('/laporan/drd_air/page/per_kel')];
        }

        echo json_encode($msg);
    }

    public function datatable()
    {
        $jenisLaporan = $this->request->getVar('jenisLaporan');
        $dateStart = $this->request->getVar('dateStart');
        $dateEnd = $this->request->getVar('dateEnd');
        $statusLaporan = $this->request->getVar('statusLaporan');
        if ($jenisLaporan == 'all') {
            return $this->getAllData($dateStart, $dateEnd, $statusLaporan);
        } else if ($jenisLaporan == 'perPetugas') {
            $petugas = $this->request->getVar('petugas');
            return $this->getDataPerPetugas($dateStart, $dateEnd, $statusLaporan, $petugas);
        } else if ($jenisLaporan == 'perUnit') {
            $unit = $this->request->getVar('unit');
            return $this->getDataPerUnit($dateStart, $dateEnd, $statusLaporan, $unit);
        } else if ($jenisLaporan == 'perKel') {
            $kel = $this->request->getVar('kel');
            return $this->getDataPerKel($dateStart, $dateEnd, $statusLaporan, $kel);
        }
    }

    function getAllData($dateStart, $dateEnd, $statusLaporan)
    {
        $builder = $this->db->table('t_tagihan');
        $builder->select('
            m_pelanggan.id_pelanggan,
            m_pelanggan.no_sambungan,
            m_pelanggan.nama_pelanggan,

            t_tagihan.no_rek,
            t_tagihan.tag_dibawah_10,
            t_tagihan.tag_diatas_10,
            t_tagihan.tarif_diatas_10 ,
            t_tagihan.by_admin,
            t_tagihan.jlh_tagihan,
            t_tagihan.bulan_tagihan,
            t_tagihan.pemakaian_air,
            
            m_jenis_pelanggan.jenis_pelanggan,

            users.nama_lengkap
        ');
        $builder->join('m_pelanggan', 't_tagihan.id_pelanggan = m_pelanggan.id_pelanggan');
        $builder->join('m_jenis_pelanggan', 't_tagihan.id_jenis = m_jenis_pelanggan.id_jenis');
        $builder->join('users', 't_tagihan.id_user = users.id_session');

        $builder->where('t_tagihan.bulan_tagihan >=', $dateStart);
        $builder->where('t_tagihan.bulan_tagihan <=', $dateEnd);

        if ($statusLaporan != 'all') {
            $builder->where('t_tagihan.status', $statusLaporan);
        }

        $builder->orderBy('m_pelanggan.no_sambungan', 'ASC');
        $builder->orderBy('m_pelanggan.id_kec', 'ASC');

        return DataTable::of($builder)
            ->add('hargaAir', function ($row) {
                $hargaAir = $row->tag_dibawah_10 + $row->tag_diatas_10;
                return "<i>Rp. " . number_format(($hargaAir), 0, '.', '.') . "</i>";
            })
            ->add('by_admin', function ($row) {
                return "<i>Rp. " . number_format($row->by_admin, 0, '.', '.') . "</i>";
            })
            ->add('jlh_tagihan', function ($row) {
                return "<i>Rp. " . number_format($row->jlh_tagihan, 0, '.', '.') . "</i>";
            })
            ->add('bulan_tagihan', function ($row) {
                return date('F Y', strtotime($row->bulan_tagihan));
            })
            ->add('pemakaian', function ($row) {
                return $this->getPemakaian($row);
            })
            ->add('user', function ($row) {
                return $row->nama_lengkap;
            })
            ->toJson(true);
    }

    function getDataPerPetugas($dateStart, $dateEnd, $statusLaporan, $petugas)
    {
        $builder = $this->db->table('t_tagihan');
        $builder->select('
            m_pelanggan.id_pelanggan,
            m_pelanggan.no_sambungan,
            m_pelanggan.nama_pelanggan,

            t_tagihan.no_rek,
            t_tagihan.tag_dibawah_10,
            t_tagihan.tag_diatas_10,
            t_tagihan.tarif_diatas_10 ,
            t_tagihan.by_admin,
            t_tagihan.jlh_tagihan,
            t_tagihan.bulan_tagihan,
            t_tagihan.pemakaian_air,
            
            m_jenis_pelanggan.jenis_pelanggan,

            users.nama_lengkap
        ');
        $builder->join('m_pelanggan', 't_tagihan.id_pelanggan = m_pelanggan.id_pelanggan');
        $builder->join('m_jenis_pelanggan', 't_tagihan.id_jenis = m_jenis_pelanggan.id_jenis');
        $builder->join('users', 't_tagihan.id_user = users.id_session');

        $builder->where('t_tagihan.bulan_tagihan >=', $dateStart);
        $builder->where('t_tagihan.bulan_tagihan <=', $dateEnd);

        if ($statusLaporan != 'all') {
            $builder->where('t_tagihan.status', $statusLaporan);
        }

        if ($petugas != 'all') {
            $builder->where('t_tagihan.id_user', $petugas);
        }

        $builder->orderBy('m_pelanggan.no_sambungan', 'ASC');
        $builder->orderBy('m_pelanggan.id_kec', 'ASC');

        return DataTable::of($builder)
            ->add('hargaAir', function ($row) {
                $hargaAir = $row->tag_dibawah_10 + $row->tag_diatas_10;
                return "<i>Rp. " . number_format(($hargaAir), 0, '.', '.') . "</i>";
            })
            ->add('by_admin', function ($row) {
                return "<i>Rp. " . number_format($row->by_admin, 0, '.', '.') . "</i>";
            })
            ->add('jlh_tagihan', function ($row) {
                return "<i>Rp. " . number_format($row->jlh_tagihan, 0, '.', '.') . "</i>";
            })
            ->add('bulan_tagihan', function ($row) {
                return date('F Y', strtotime($row->bulan_tagihan));
            })
            ->add('pemakaian', function ($row) {
                return $this->getPemakaian($row);
            })
            ->add('user', function ($row) {
                return $row->nama_lengkap;
            })
            ->toJson(true);
    }

    function getDataPerUnit($dateStart, $dateEnd, $statusLaporan, $unit)
    {
        $builder = $this->db->table('t_tagihan');
        $builder->select('
            m_pelanggan.id_pelanggan,
            m_pelanggan.no_sambungan,
            m_pelanggan.nama_pelanggan,

            t_tagihan.no_rek,
            t_tagihan.tag_dibawah_10,
            t_tagihan.tag_diatas_10,
            t_tagihan.tarif_diatas_10 ,
            t_tagihan.by_admin,
            t_tagihan.jlh_tagihan,
            t_tagihan.bulan_tagihan,
            t_tagihan.pemakaian_air,
            
            m_jenis_pelanggan.jenis_pelanggan,

            users.nama_lengkap
        ');
        $builder->join('m_pelanggan', 't_tagihan.id_pelanggan = m_pelanggan.id_pelanggan');
        $builder->join('m_jenis_pelanggan', 't_tagihan.id_jenis = m_jenis_pelanggan.id_jenis');
        $builder->join('users', 't_tagihan.id_user = users.id_session');

        $builder->where('t_tagihan.bulan_tagihan >=', $dateStart);
        $builder->where('t_tagihan.bulan_tagihan <=', $dateEnd);

        if ($statusLaporan != 'all') {
            $builder->where('t_tagihan.status', $statusLaporan);
        }

        if ($unit != 'all') {
            $builder->where('m_pelanggan.id_kec', $unit);
        }

        $builder->orderBy('m_pelanggan.no_sambungan', 'ASC');
        $builder->orderBy('m_pelanggan.id_kec', 'ASC');

        return DataTable::of($builder)
            ->add('hargaAir', function ($row) {
                $hargaAir = $row->tag_dibawah_10 + $row->tag_diatas_10;
                return "<i>Rp. " . number_format(($hargaAir), 0, '.', '.') . "</i>";
            })
            ->add('by_admin', function ($row) {
                return "<i>Rp. " . number_format($row->by_admin, 0, '.', '.') . "</i>";
            })
            ->add('jlh_tagihan', function ($row) {
                return "<i>Rp. " . number_format($row->jlh_tagihan, 0, '.', '.') . "</i>";
            })
            ->add('bulan_tagihan', function ($row) {
                return date('F Y', strtotime($row->bulan_tagihan));
            })
            ->add('pemakaian', function ($row) {
                return $this->getPemakaian($row);
            })
            ->add('user', function ($row) {
                return $row->nama_lengkap;
            })
            ->toJson(true);
    }

    function getDataPerKel($dateStart, $dateEnd, $statusLaporan, $kel)
    {
        $builder = $this->db->table('t_tagihan');
        $builder->select('
            m_pelanggan.id_pelanggan,
            m_pelanggan.no_sambungan,
            m_pelanggan.nama_pelanggan,

            t_tagihan.no_rek,
            t_tagihan.tag_dibawah_10,
            t_tagihan.tag_diatas_10,
            t_tagihan.tarif_diatas_10 ,
            t_tagihan.by_admin,
            t_tagihan.jlh_tagihan,
            t_tagihan.bulan_tagihan,
            t_tagihan.pemakaian_air,
            
            m_jenis_pelanggan.jenis_pelanggan,

            users.nama_lengkap
        ');
        $builder->join('m_pelanggan', 't_tagihan.id_pelanggan = m_pelanggan.id_pelanggan');
        $builder->join('m_jenis_pelanggan', 't_tagihan.id_jenis = m_jenis_pelanggan.id_jenis');
        $builder->join('users', 't_tagihan.id_user = users.id_session');

        $builder->where('t_tagihan.bulan_tagihan >=', $dateStart);
        $builder->where('t_tagihan.bulan_tagihan <=', $dateEnd);

        if ($statusLaporan != 'all') {
            $builder->where('t_tagihan.status', $statusLaporan);
        }

        if ($kel != 'all') {
            $builder->where('m_pelanggan.id_kelurahan', $kel);
        }

        $builder->orderBy('m_pelanggan.no_sambungan', 'ASC');
        $builder->orderBy('m_pelanggan.id_kec', 'ASC');

        return DataTable::of($builder)
            ->add('hargaAir', function ($row) {
                $hargaAir = $row->tag_dibawah_10 + $row->tag_diatas_10;
                return "<i>Rp. " . number_format(($hargaAir), 0, '.', '.') . "</i>";
            })
            ->add('by_admin', function ($row) {
                return "<i>Rp. " . number_format($row->by_admin, 0, '.', '.') . "</i>";
            })
            ->add('jlh_tagihan', function ($row) {
                return "<i>Rp. " . number_format($row->jlh_tagihan, 0, '.', '.') . "</i>";
            })
            ->add('bulan_tagihan', function ($row) {
                return date('F Y', strtotime($row->bulan_tagihan));
            })
            ->add('pemakaian', function ($row) {
                return $this->getPemakaian($row);
            })
            ->add('user', function ($row) {
                return $row->nama_lengkap;
            })
            ->toJson(true);
    }

    private function getPemakaian($row)
    {
        $tarifbawah = $row->tag_dibawah_10 + $row->by_admin;
        $cekTagihan = $row->jlh_tagihan;

        if ($cekTagihan > $tarifbawah) {
            $kubikasi = ($cekTagihan - $tarifbawah) / $row->tarif_diatas_10 + 10;
        } else {
            $mundur_sebulan = date('Y-m-d', strtotime(date($row->bulan_tagihan) . '- 1 month'));
            $n_tahun = date('Y', strtotime(date($mundur_sebulan)));
            $n_bulan = date('m', strtotime(date($mundur_sebulan)));

            $dataBawah = $this->db->table('t_tagihan')
                ->select('pemakaian_air')
                ->where('id_pelanggan', $row->id_pelanggan)
                ->where('MONTH(bulan_tagihan)', $n_bulan)
                ->where('YEAR(bulan_tagihan)', $n_tahun)
                ->get()
                ->getRow();

            $na = $row->pemakaian_air;
            $nb = $dataBawah->pemakaian_air ?? 0;

            $kubikasi = $na - $nb;
            $kubikasi = ($kubikasi > 10 || $kubikasi < 0) ? 0 : $kubikasi;
        }

        return round($kubikasi);
    }


    public function modal_export_pdf()
    {
        $data['jenisLaporan'] = $this->request->getVar('jenisLaporan');
        $data['dateStart'] = $this->request->getVar('dateStart');
        $data['dateEnd'] = $this->request->getVar('dateEnd');
        $data['statusLaporan'] = $this->request->getVar('statusLaporan');
        $data['petugas'] = $this->request->getVar('petugas');
        $data['unit'] = $this->request->getVar('unit');
        $data['kel'] = $this->request->getVar('kel');

        $msg = ['html' => view('laporan/drd_air/modal/pdf', $data)];
        echo json_encode($msg);
    }

    public function export_pdf($jenisLaporan, $dateStart, $dateEnd, $statusLaporan, $petugas, $unit, $kel)
    {
        $html = '';
        $html .= '<div style="margin-top: 10px;">';
        $html .= "<h3 align='center' style='margin-bottom: 0px; margin-top: 0px;'>LAPORAN DAFTAR REKENING DITAGIH (DRD) AIR</h3>";
        $html .= "<p align='center' style='margin-bottom: 0px; margin-top: 0px;'><b>PERIODE : " . date('d/m/Y', strtotime($dateStart)) . " - " . date('d/m/Y', strtotime($dateEnd)) . "</b></p>";

        if ($jenisLaporan == 'all') {
            $builder = $this->db->table('t_tagihan');
            $builder->select('
                m_pelanggan.id_pelanggan,
                m_pelanggan.no_sambungan,
                m_pelanggan.nama_pelanggan,

                t_tagihan.no_rek,
                t_tagihan.tag_dibawah_10,
                t_tagihan.tag_diatas_10,
                t_tagihan.tarif_diatas_10 ,
                t_tagihan.by_admin,
                t_tagihan.jlh_tagihan,
                t_tagihan.bulan_tagihan,
                t_tagihan.pemakaian_air,
                
                m_jenis_pelanggan.jenis_pelanggan,

                users.nama_lengkap
            ');
            $builder->join('m_pelanggan', 't_tagihan.id_pelanggan = m_pelanggan.id_pelanggan');
            $builder->join('m_jenis_pelanggan', 't_tagihan.id_jenis = m_jenis_pelanggan.id_jenis');
            $builder->join('users', 't_tagihan.id_user = users.id_session');

            $builder->where('t_tagihan.bulan_tagihan >=', $dateStart);
            $builder->where('t_tagihan.bulan_tagihan <=', $dateEnd);

            if ($statusLaporan != 'all') {
                $builder->where('t_tagihan.status', $statusLaporan);
            }

            $builder->orderBy('m_pelanggan.no_sambungan', 'ASC');
            $builder->orderBy('m_pelanggan.id_kec', 'ASC');

            $result = $builder->get()->getResultArray();

            $html .= '<br>';
            $html .= '<div class="table">';
            $html .= '<table>';
            $html .= '<tr>';
            $html .= '<th width="5%">No</th>';
            $html .= '<th>No. Sambungan</th>';
            $html .= '<th>Pelanggan</th>';
            $html .= '<th>No. Rekening</th>';
            $html .= '<th>Harga Air</th>';
            $html .= '<th>Bea. Admin</th>';
            $html .= '<th>Jumlah</th>';
            $html .= '<th>PMK</th>';
            $html .= '<th>Bulan Tagihan</th>';
            $html .= '<th>Ket</th>';
            $html .= '</tr>';

            if ($result) {
                foreach ($result as $key => $value) {
                    $html .= '<tr>';
                    $html .= '<td class="text-center">' . ($key + 1) . '</td>';
                    $html .= '<td class="text-center">' . $value['no_sambungan'] . '</td>';
                    $html .= '<td>' . $value['nama_pelanggan'] . '</td>';
                    $html .= '<td class="text-center">' . $value['no_rek'] . '</td>';
                    $html .= '<td class="text-center">' . number_format(($value['tag_dibawah_10'] + $value['tag_diatas_10']), 0, ',', '.') . '</td>';
                    $html .= '<td class="text-center"> Rp. ' . number_format($value['by_admin'], 0, ',', '.') . '</td>';
                    $html .= '<td class="text-center"> Rp. ' . number_format($value['jlh_tagihan'], 0, ',', '.') . '</td>';
                    $html .= '<td class="text-center">' . number_format($value['pemakaian_air'], 0, ',', '.') . '</td>';
                    $html .= '<td class="text-center">' . $value['bulan_tagihan'] . '</td>';
                    $html .= '<td class="text-center">' . $value['jenis_pelanggan'] . '</td>';
                    $html .= '</tr>';
                }
            } else {
                $html .= '<tr>';
                $html .= '<td colspan="2" class="text-center">Tidak Ada Data</td>';
                $html .= '</tr>';
            }

            $html .= '</table>';
            $html .= '</div>';
        } else if ($jenisLaporan == 'perPetugas') {
            $nama = ($petugas == 'all') ? 'SEMUA PETUGAS' : $this->db->table('users')->where('id_session', $petugas)->get()->getRowArray()['nama_lengkap'];
            $html .= "<h3 align='center' style='margin-bottom: 0px; margin-top: 0px;'>PETUGAS : " . strtoupper($nama) . "</h3>";

            $builder = $this->db->table('t_tagihan');
            $builder->select('
                m_pelanggan.id_pelanggan,
                m_pelanggan.no_sambungan,
                m_pelanggan.nama_pelanggan,

                t_tagihan.no_rek,
                t_tagihan.tag_dibawah_10,
                t_tagihan.tag_diatas_10,
                t_tagihan.tarif_diatas_10 ,
                t_tagihan.by_admin,
                t_tagihan.jlh_tagihan,
                t_tagihan.bulan_tagihan,
                t_tagihan.pemakaian_air,
                
                m_jenis_pelanggan.jenis_pelanggan,

                users.nama_lengkap,
                users.id_session,
            ');
            $builder->join('m_pelanggan', 't_tagihan.id_pelanggan = m_pelanggan.id_pelanggan');
            $builder->join('m_jenis_pelanggan', 't_tagihan.id_jenis = m_jenis_pelanggan.id_jenis');
            $builder->join('users', 't_tagihan.id_user = users.id_session');

            $builder->where('t_tagihan.bulan_tagihan >=', $dateStart);
            $builder->where('t_tagihan.bulan_tagihan <=', $dateEnd);

            if ($statusLaporan != 'all') {
                $builder->where('t_tagihan.status', $statusLaporan);
            }

            if ($petugas != 'all') {
                $builder->where('t_tagihan.id_user', $petugas);
            }

            $builder->orderBy('m_pelanggan.no_sambungan', 'ASC');
            $builder->orderBy('m_pelanggan.id_kec', 'ASC');

            $result = $builder->get()->getResultArray();

            $html .= '<br>';
            $html .= '<div class="table">';
            $html .= '<table>';
            $html .= '<tr>';
            $html .= '<th width="5%">No</th>';
            $html .= '<th>No. Sambungan</th>';
            $html .= '<th>Pelanggan</th>';
            $html .= '<th>No. Rekening</th>';
            $html .= '<th>Harga Air</th>';
            $html .= '<th>Bea. Admin</th>';
            $html .= '<th>Jumlah</th>';
            $html .= '<th>PMK</th>';
            $html .= '<th>Bulan Tagihan</th>';
            $html .= '<th>Ket</th>';
            $html .= '</tr>';

            if ($result) {
                $temp_petugas = '';
                foreach ($result as $key => $value) {
                    if ($petugas == 'all') {
                        if ($temp_petugas != $value['id_session']) {
                            $temp_petugas = $value['id_session'];
                            $html .= '<tr>';
                            $html .= '<td colspan="10" class="text-center" style="background-color:rgb(255, 217, 159);">' . strtoupper($value['nama_lengkap']) . '</td>';
                            $html .= '</tr>';
                        }
                    }
                    $html .= '<tr>';
                    $html .= '<td class="text-center">' . ($key + 1) . '</td>';
                    $html .= '<td class="text-center">' . $value['no_sambungan'] . '</td>';
                    $html .= '<td>' . $value['nama_pelanggan'] . '</td>';
                    $html .= '<td class="text-center">' . $value['no_rek'] . '</td>';
                    $html .= '<td class="text-center">Rp. ' . number_format(($value['tag_dibawah_10'] + $value['tag_diatas_10']), 0, ',', '.') . '</td>';
                    $html .= '<td class="text-center">Rp. ' . number_format($value['by_admin'], 0, ',', '.') . '</td>';
                    $html .= '<td class="text-center">Rp. ' . number_format($value['jlh_tagihan'], 0, ',', '.') . '</td>';
                    $html .= '<td class="text-center">' . number_format($value['pemakaian_air'], 0, ',', '.') . '</td>';
                    $html .= '<td class="text-center">' . $value['bulan_tagihan'] . '</td>';
                    $html .= '<td class="text-center">' . $value['jenis_pelanggan'] . '</td>';
                    $html .= '</tr>';
                }
            } else {
                $html .= '<tr>';
                $html .= '<td colspan="2" class="text-center">Tidak Ada Data</td>';
                $html .= '</tr>';
            }

            $html .= '</table>';
            $html .= '</div>';
        } else if ($jenisLaporan == 'perUnit') {
            $nama = ($unit == 'all') ? 'SEMUA UNIT' : $this->db->table('m_kecamatan')->where('id_kec', $unit)->get()->getRowArray()['kecamatan'];
            $html .= "<h3 align='center' style='margin-bottom: 0px; margin-top: 0px;'>UNIT : " . strtoupper($nama) . "</h3>";

            $builder = $this->db->table('t_tagihan');
            $builder->select('
                m_pelanggan.id_pelanggan,
                m_pelanggan.no_sambungan,
                m_pelanggan.nama_pelanggan,
                m_pelanggan.id_kec,

                t_tagihan.no_rek,
                t_tagihan.tag_dibawah_10,
                t_tagihan.tag_diatas_10,
                t_tagihan.tarif_diatas_10 ,
                t_tagihan.by_admin,
                t_tagihan.jlh_tagihan,
                t_tagihan.bulan_tagihan,
                t_tagihan.pemakaian_air,
                
                m_jenis_pelanggan.jenis_pelanggan,

                users.nama_lengkap,

                t_kecamatan.kecamatan
            ');
            $builder->join('m_pelanggan', 't_tagihan.id_pelanggan = m_pelanggan.id_pelanggan');
            $builder->join('m_jenis_pelanggan', 't_tagihan.id_jenis = m_jenis_pelanggan.id_jenis');
            $builder->join('users', 't_tagihan.id_user = users.id_session');
            $builder->join('t_kecamatan', 'm_pelanggan.id_kec = t_kecamatan.id_kec');

            $builder->where('t_tagihan.bulan_tagihan >=', $dateStart);
            $builder->where('t_tagihan.bulan_tagihan <=', $dateEnd);

            if ($statusLaporan != 'all') {
                $builder->where('t_tagihan.status', $statusLaporan);
            }

            if ($unit != 'all') {
                $builder->where('m_pelanggan.id_kec', $unit);
            }

            $builder->orderBy('m_pelanggan.no_sambungan', 'ASC');
            $builder->orderBy('m_pelanggan.id_kec', 'ASC');

            $result = $builder->get()->getResultArray();

            $html .= '<br>';
            $html .= '<div class="table">';
            $html .= '<table>';
            $html .= '<tr>';
            $html .= '<th width="5%">No</th>';
            $html .= '<th>No. Sambungan</th>';
            $html .= '<th>Pelanggan</th>';
            $html .= '<th>No. Rekening</th>';
            $html .= '<th>Harga Air</th>';
            $html .= '<th>Bea. Admin</th>';
            $html .= '<th>Jumlah</th>';
            $html .= '<th>PMK</th>';
            $html .= '<th>Bulan Tagihan</th>';
            $html .= '<th>Ket</th>';
            $html .= '</tr>';

            if ($result) {
                $temp_unit = '';
                foreach ($result as $key => $value) {
                    if ($unit == 'all') {
                        if ($temp_unit != $value['id_kec']) {
                            $temp_unit = $value['id_kec'];
                            $html .= '<tr>';
                            $html .= '<td colspan="10" class="text-center" style="background-color:rgb(255, 217, 159);">' . strtoupper($value['kecamatan']) . '</td>';
                            $html .= '</tr>';
                        }
                    }
                    $html .= '<tr>';
                    $html .= '<td class="text-center">' . ($key + 1) . '</td>';
                    $html .= '<td class="text-center">' . $value['no_sambungan'] . '</td>';
                    $html .= '<td>' . $value['nama_pelanggan'] . '</td>';
                    $html .= '<td class="text-center">' . $value['no_rek'] . '</td>';
                    $html .= '<td class="text-center">Rp. ' . number_format(($value['tag_dibawah_10'] + $value['tag_diatas_10']), 0, ',', '.') . '</td>';
                    $html .= '<td class="text-center">Rp. ' . number_format($value['by_admin'], 0, ',', '.') . '</td>';
                    $html .= '<td class="text-center">Rp. ' . number_format($value['jlh_tagihan'], 0, ',', '.') . '</td>';
                    $html .= '<td class="text-center">' . number_format($value['pemakaian_air'], 0, ',', '.') . '</td>';
                    $html .= '<td class="text-center">' . $value['bulan_tagihan'] . '</td>';
                    $html .= '<td class="text-center">' . $value['jenis_pelanggan'] . '</td>';
                    $html .= '</tr>';
                }
            } else {
                $html .= '<tr>';
                $html .= '<td colspan="10" class="text-center">Tidak Ada Data</td>';
                $html .= '</tr>';
            }

            $html .= '</table>';
            $html .= '</div>';
        } else if ($jenisLaporan == 'perKel') {
            $nama_kelurahan = ($kel == 'all') ? 'SEMUA KELURAHAN' : $this->db->table('t_kelurahan')->where('id_kelurahan', $kel)->get()->getRowArray()['kelurahan'];
            $html .= "<h3 align='center' style='margin-bottom: 0px; margin-top: 0px;'>PERKELURAHAN : " . $nama_kelurahan . "</h3>";

            $builder = $this->db->table('t_tagihan');
            $builder->select('
                m_pelanggan.id_pelanggan,
                m_pelanggan.no_sambungan,
                m_pelanggan.nama_pelanggan,

                t_tagihan.no_rek,
                t_tagihan.tag_dibawah_10,
                t_tagihan.tag_diatas_10,
                t_tagihan.tarif_diatas_10 ,
                t_tagihan.by_admin,
                t_tagihan.jlh_tagihan,
                t_tagihan.bulan_tagihan,
                t_tagihan.pemakaian_air,
                
                m_jenis_pelanggan.jenis_pelanggan,

                users.nama_lengkap,

                m_pelanggan.id_kelurahan,
                t_kelurahan.kelurahan
            ');
            $builder->join('m_pelanggan', 't_tagihan.id_pelanggan = m_pelanggan.id_pelanggan');
            $builder->join('m_jenis_pelanggan', 't_tagihan.id_jenis = m_jenis_pelanggan.id_jenis');
            $builder->join('users', 't_tagihan.id_user = users.id_session');
            $builder->join('t_kelurahan', 'm_pelanggan.id_kelurahan = t_kelurahan.id_kelurahan');

            $builder->where('t_tagihan.bulan_tagihan >=', $dateStart);
            $builder->where('t_tagihan.bulan_tagihan <=', $dateEnd);

            if ($statusLaporan != 'all') {
                $builder->where('t_tagihan.status', $statusLaporan);
            }

            if ($kel != 'all') {
                $builder->where('m_pelanggan.id_kelurahan', $kel);
            }

            $builder->orderBy('m_pelanggan.no_sambungan', 'ASC');
            $builder->orderBy('m_pelanggan.id_kec', 'ASC');

            $result = $builder->get()->getResultArray();

            $html .= '<br>';
            $html .= '<div class="table">';
            $html .= '<table>';
            $html .= '<tr>';
            $html .= '<th width="5%">No</th>';
            $html .= '<th>No. Sambungan</th>';
            $html .= '<th>Pelanggan</th>';
            $html .= '<th>No. Rekening</th>';
            $html .= '<th>Harga Air</th>';
            $html .= '<th>Bea. Admin</th>';
            $html .= '<th>Jumlah</th>';
            $html .= '<th>PMK</th>';
            $html .= '<th>Bulan Tagihan</th>';
            $html .= '<th>Ket</th>';
            $html .= '</tr>';

            if ($result) {
                $temp_kelurahan = '';
                foreach ($result as $key => $value) {
                    if ($kel == 'all') {
                        if ($temp_kelurahan != $value['id_kelurahan']) {
                            $temp_kelurahan = $value['id_kelurahan'];
                            $html .= '<tr>';
                            $html .= '<td colspan="10" class="text-center" style="background-color:rgb(255, 217, 159);">' . strtoupper($value['kelurahan']) . '</td>';
                            $html .= '</tr>';
                        }
                    }
                    $html .= '<tr>';
                    $html .= '<td class="text-center">' . ($key + 1) . '</td>';
                    $html .= '<td class="text-center">' . $value['no_sambungan'] . '</td>';
                    $html .= '<td>' . $value['nama_pelanggan'] . '</td>';
                    $html .= '<td class="text-center">' . $value['no_rek'] . '</td>';
                    $html .= '<td class="text-center">Rp. ' . number_format(($value['tag_dibawah_10'] + $value['tag_diatas_10']), 0, ',', '.') . '</td>';
                    $html .= '<td class="text-center">Rp. ' . number_format($value['by_admin'], 0, ',', '.') . '</td>';
                    $html .= '<td class="text-center">Rp. ' . number_format($value['jlh_tagihan'], 0, ',', '.') . '</td>';
                    $html .= '<td class="text-center">' . number_format($value['pemakaian_air'], 0, ',', '.') . '</td>';
                    $html .= '<td class="text-center">' . $value['bulan_tagihan'] . '</td>';
                    $html .= '<td class="text-center">' . $value['jenis_pelanggan'] . '</td>';
                    $html .= '</tr>';
                }
            } else {
                $html .= '<tr>';
                $html .= '<td colspan="10" class="text-center">Tidak Ada Data</td>';
                $html .= '</tr>';
            }

            $html .= '</table>';
            $html .= '</div>';
        }

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
        $mpdf->SetTitle("LAPORAN DAFTAR REKENING DITAGIH (DRD) AIR");
        $this->response->setHeader('Content-Type', 'application/pdf');
        $mpdf->Output("PDF", 'I');
    }
}
