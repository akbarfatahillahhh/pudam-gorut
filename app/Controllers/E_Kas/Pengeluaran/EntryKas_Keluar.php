<?php

namespace App\Controllers\E_Kas\Pengeluaran;

use Hermawan\DataTables\DataTable;
use App\Controllers\BaseController;

class EntryKas_Keluar extends BaseController
{
    var $title;
    public function __construct()
    {
        $this->title = "Kas Keluar";
    }

    public function index()
    {
        return view('e-kas/pengeluaran/entry_kas_keluar/main', ['title' => $this->title]);
    }


    // dari aplikasi lama
    public function simpan_merah()
	{
		if ($this->request->isAJAX()) {
			
			$NoVoucher = $this->request->getPost('no_vocer');
			$cekTahun = date('Y', strtotime($this->request->getPost('tanggal_vocer')));

			if($NoVoucher == ""){ //jika nomor voucher kosong, maka otomatis
				$query = $this->db->table('akun_kas_keluar')
				->select('max(NOMOR) as nomor')
				->where(["DATE_FORMAT(TANGGAL,'%Y')" => $cekTahun])
				->get();
				
				$getnomor = $query->getRow();
				if($getnomor->nomor >= 1){
					$geNov = $getnomor->nomor + 1;
				}else{
					$geNov = 1;
				}
			}else{ // jika nomor voucher sudah ditentukan
				$geNov = $NoVoucher;
			}
			
			$idperkiraan = $this->request->getPost('idperkiraan');
			// $debet = str_replace(".", "", $this->request->getPost('debet'));
			// $kredit = str_replace(".", "", $this->request->getPost('kredit'));
				// Simpan voucher merah
				$perkiraanH = $this->request->getPost('idperkiraanhijauh');
				$debetH = str_replace(".", "", $this->request->getPost('debethijauh'));
				$kreditH = str_replace(".", "", $this->request->getPost('kredithijauh'));

					$dataH = array();
					$indexH = 0;
					foreach ($perkiraanH as $row) {
						array_push($dataH, array(
							'NOMOR' => $geNov,
							'TANGGAL' => $this->request->getPost('tanggal_vocer'),
							'BANK' => $this->request->getPost('bank'),
							'NO_GIRO' => $this->request->getPost('no_giro'),
							'BAGIAN_DIREKSI' => $this->request->getPost('bagian_direksi'),
							'DIBAYAR_KEPADA' => $this->request->getPost('dibayar_kepada'),
							'PERKIRAAN_ID' => $perkiraanH[$indexH],
							'DEBET' => $debetH[$indexH],
							'KREDIT' => $kreditH[$indexH],
							'YG_MENERIMA' => $this->request->getPost('yg_menerima'),
							'KETERANGAN' => $this->request->getPost('keterangan'),
							'STATUS' => $this->request->getPost('status'),
							'USERID' => '',
						));
						$indexH++;
					}

                    $akun_kas_keluar = $this->db->table('akun_kas_keluar');
                    $akun_kas_keluar->insertBatch($dataH);

					// $hijauh = new Model_kas_keluar();
					// $hijauh->insertBatch($dataH); // insert banyak record

			$msg = [
                'status' => true,
				'message' => 'Berhasil!'
			];
			echo json_encode($msg);
		
		} else {
			return view('error_page');
		}
	}
	public function simpan_hutang_lalu(){
		if ($this->request->isAJAX()) {
			$cekTahun = date('Y', strtotime($this->request->getPost('tanggal_vocer')));

			$query = $this->db->table('akun_kas_keluar')
			->select('max(NOMOR) as nomor')
			->where(["DATE_FORMAT(TANGGAL,'%Y')" => $cekTahun])
			->get();
			
			$getnomor = $query->getRow();
			if($getnomor->nomor >= 1){
				$geNov = $getnomor->nomor + 1;
			}else{
				$geNov = 1;
			}

			$idperkiraan = $this->request->getPost('idperkiraan');
			$debet = str_replace(".", "", $this->request->getPost('debet'));
			$kredit = str_replace(".", "", $this->request->getPost('kredit'));

				$simpandata = [
					'NOMOR' => $geNov,
					'TANGGAL' => $this->request->getPost('tanggal_vocer'),
					'BANK' => $this->request->getPost('bank'),
					'NO_GIRO' => $this->request->getPost('no_giro'),
					'BAGIAN_DIREKSI' => $this->request->getPost('bagian_direksi'),
					'DIBAYAR_KEPADA' => $this->request->getPost('dibayar_kepada'),
					'PERKIRAAN_ID' => $idperkiraan,
					'DEBET' => $debet,
					'KREDIT' => $kredit,
					'YG_MENERIMA' => $this->request->getPost('yg_menerima'),
					'KETERANGAN' => $this->request->getPost('keterangan'),
					'STATUS' => "N",
					'USERID' => '',
				];

				$simpanvovermerah = new Model_kas_keluar();
				$simpanvovermerah->insert($simpandata); // insert banyak record

			$msg = [
				'sukses' => 'Berhasil! Berhasil! Silahkan cetak voucer di Tabel Daftar Pengeluaran!'
			];
			echo json_encode($msg);
		
		} else {
			return view('error_page');
		}
	}
    public function hapusVoucher()
    {
        if ($this->request->isAJAX()) {
            $novoucher = $this->request->getVar('NOVOUCHER');
            $tanggal = $this->request->getVar('TANGGAL');
            // Panggila Model order lab pk
            $tkeluar = $this->db->table('akun_kas_keluar');
			$tkeluar->delete(
				[
					'NOMOR' => $novoucher,
					'TANGGAL' => $tanggal
				]
			);
            // $tkeluar->deleteBatch('NOMOR', $novoucher);
            $msg = [
                'sukses' => 'Sukses hapus voucher nomor : ' . $novoucher
            ];
            echo json_encode($msg);
        }
    }
    // dari aplikasi lama

    public function save(){
        if($this->request->isAJAX()){
            $data = $this->request->getPost();

            
            if(empty($data['TANGGAL']) || empty($data['BAGIAN_DIREKSI']) || empty($data['BANK']) || empty($data['STATUS']) || empty($data['DIBAYAR_KEPADA']) || empty($data['KETERANGAN']) || empty($data['rows'])){
                return json_encode([
                    'status' => false,
                    'message' => 'Data tidak boleh kosong, Harap mengisi semua data',
                ]);
                return false; 
            }
            
            $table = $this->db->table('akun_kas_keluar');
            $nomorAKhir = $this->db->table('akun_kas_keluar')->select('NOMOR')->orderBy('NOMOR', 'DESC')->limit(1)->get()->getRowArray();
            
            foreach($data['rows'] as $key => $value){
                $dataInsert = [
                    'NOMOR' => (empty($data['NOMOR']) ? number_format($nomorAKhir['NOMOR'] + 1, 0, ',', '.') : $data['NOMOR']),
                    'TANGGAL' => $data['TANGGAL'],
                    'NO_GIRO' => $data['NO_GIRO'],
                    'BAGIAN_DIREKSI' => $data['BAGIAN_DIREKSI'],
                    'BANK' => $data['BANK'],
                    'STATUS' => $data['STATUS'],
                    'DIBAYAR_KEPADA' => $data['DIBAYAR_KEPADA'],
                    'YG_MENERIMA' => $data['YANG_MENERIMA'],
                    'KETERANGAN' => $data['KETERANGAN'],
                    'PERKIRAAN_ID' => $value['PERKIRAAN_ID'],
                    'DEBET' => $value['DEBET'],
                    'KREDIT' => $value['KREDIT'],
                ];
                
                $table->insert($dataInsert);
            }
            
            return json_encode([
                'status' => true,
                'msg' => 'Data berhasil disimpan'
            ]);
            
        }
    }

    public function riwayat(){
        return view('e-kas/pengeluaran/riwayat/main', ['title' => "Riwayat Kas Pengeluaran"]);
    }

    public function datatable(){
        $tglAwal = $this->request->getVar('tanggalAwal');
        $tglAkhir = $this->request->getVar('tanggalAkhir');


        // return DataTables::use('view_akun_kas_keluar')
		// 	->select('IDVOUCHER, 
		// 				  NOVOUCHER, 
		// 				  TANGGAL, 
		// 				  BANK, 
		// 				  NO_GIRO, 
		// 				  BAGIAN_DIREKSI, 
		// 				  DIBAYAR_KEPADA, 
		// 				  PERKIRAAN_ID,  
		// 				  USERID, 
		// 				  YG_MENERIMA,
		// 				  KETERANGAN,
		// 				  IDGROUPPER,
		// 				  NO_PERKIRAANGROUP,
		// 				  GROUPPERKIRAAN,
		// 				  STATUS')

		// 	->addColumn('nourut', function ($data) {
        //         $no = 1;
        //         return $no++;
        //     })
			
		// 	->addColumn('DEBET', function ($data) {
        //         return number_format($data->DEBET);
        //     })
		// 	->addColumn('KREDIT', function ($data) {
        //         return number_format($data->KREDIT);
        //     })
		// 	->addColumn('TANGGAL', function ($data) {
        //         return date('d-m-Y', strtotime($data->TANGGAL));
        //     })

        //      ->addColumn('aksi', function ($data) {
				  
        //     })
		// 	->where(['akun_kas_keluar.TANGGAL >=' => $awal])
        //     ->where(['akun_kas_keluar.TANGGAL <='=> $akhir])
        //     ->rawColumns(['nourut','aksi']) //tambahkan rawColumns agar terbaca kode HTML pada name aksiubah
        //     ->make(true);
        
        $table = $this->db->table('view_akun_kas_keluar');
        $table->select('IDVOUCHER, NOVOUCHER, TANGGAL, BANK, NO_GIRO, BAGIAN_DIREKSI, DIBAYAR_KEPADA, PERKIRAAN_ID, USERID, YG_MENERIMA, KETERANGAN, IDGROUPPER, NO_PERKIRAANGROUP, GROUPPERKIRAAN, STATUS');
        $table->where('TANGGAL >=', $tglAwal);
        $table->where('TANGGAL <=', $tglAkhir);
        $table->orderBy('TANGGAL', 'DESC');

        return DataTable::of($table)
        ->add('aksi', function ($data) {
            $btn = "
                <div class=\"dropdown\">
                    <button class=\"btn btn-sm\" type=\"button\" data-bs-toggle=\"dropdown\" aria-expanded=\"false\">
                        <i class=\"fas fa-ellipsis-v\"></i>
                    </button>
                    <ul class=\"dropdown-menu py-0\">";
             if($data->STATUS == "Y"){
                $btn .= "<li>
                            <a href=\"javascript:void(0)\" class=\"dropdown-item\"  onclick=\"getvoucher('" . $data->NOVOUCHER . "','" . $data->TANGGAL . "')\">
                                <i class=\"fas fa-receipt text-danger\"></i> 
                                Voucher
                            </a>
                        </li>
                        <li>
                            <a href=\"javascript:void(0)\" class=\"dropdown-item\"  onclick=\"getvoucherdetail('" . $data->NOVOUCHER . "','" . $data->TANGGAL . "')\">
                                <i class=\"fas fa-receipt text-success\"></i> 
                                Voucher
                            </a>
                        </li>
                        <li>
                            <a href=\"javascript:void(0)\" class=\"dropdown-item\" onclick=\"getkwitansi('" . $data->NOVOUCHER . "','" . $data->TANGGAL . "')\">
                                <i class=\"fas fa-receipt text-primary\"></i> 
                                Kwitansi
                            </a>
                        </li>
                        <li>
                            <a href=\"javascript:void(0)\" class=\"dropdown-item\" onclick=\"hapusVoucher('" . $data->NOVOUCHER . "','" . $data->TANGGAL . "')\">
                                <i class=\"fas fa-trash\"></i>
                                Hapus
                            </a>
                        </li>
                        ";
            }else{
                $btn .= "<li>
                            <a href=\"javascript:void(0)\" class=\"dropdown-item\"  onclick=\"getvoucher('" . $data->NOVOUCHER . "','" . $data->TANGGAL . "')\">
                                <i class=\"fas fa-receipt text-danger\"></i> 
                                Voucher
                            </a>
                        </li>
                        <li>
                            <a href=\"javascript:void(0)\" class=\"dropdown-item\" onclick=\"getkwitansi('" . $data->NOVOUCHER . "','" . $data->TANGGAL . "')\">
                                <i class=\"fas fa-receipt text-primary\"></i> 
                                Kwitansi
                            </a>
                        </li>
                        <li>
                            <a href=\"javascript:void(0)\" class=\"dropdown-item\" onclick=\"hapusVoucher('" . $data->NOVOUCHER . "','" . $data->TANGGAL . "')\">
                                <i class=\"fas fa-trash text-black\"></i>
                                Hapus
                            </a>
                        </li>";
            }

            $btn .= "</ul>
                </div>";
            return $btn;
         })
        ->toJson(true);
    }

    public function datatable_old(){
        $tglAwal = $this->request->getVar('tanggalAwal');
        $tglAkhir = $this->request->getVar('tanggalAkhir');

        
        $table = $this->db->table('akun_kas_keluar');
        $table->select('akun_kas_keluar.ID,akun_kas_keluar.NOMOR , akun_kas_keluar.TANGGAL , akun_kas_keluar.KETERANGAN,akun_kas_keluar.STATUS');
        // $table->select('akun_perkiraan.NAMA_PERKIRAAN, akun_perkiraan.NOMOR AS NOMOR_PERKIRAAN');
        // $table->join('akun_perkiraan', 'akun_kas_keluar.PERKIRAAN_ID = akun_perkiraan.ID', 'left');
        $table->select('akun_perkiraan_group.GROUP_PERKIRAAN as NAMA_PERKIRAAN, akun_perkiraan_group.NO_PERKIRAAN as NOMOR_PERKIRAAN');
        $table->join('akun_perkiraan_group', 'akun_kas_keluar.PERKIRAAN_ID = akun_perkiraan_group.ID');
        // $table->where('akun_kas_keluar.TANGGAL >=', $tglAwal);
        // $table->where('akun_kas_keluar.TANGGAL <=', $tglAkhir);
        $table->orderBy('akun_kas_keluar.TANGGAL', 'DESC');

        return DataTable::of($table)
        ->add('aksi', function ($row) {
             return '
             <div class="dropdown">
             <button class="btn btn-sm" type="button" data-bs-toggle="dropdown" aria-expanded="false">
             <i class="fas fa-ellipsis-v"></i>
             </button>
             <ul class="dropdown-menu py-0">
             <li>
             <a href="javascript:void(0)" class="dropdown-item" onclick="voucher(\'success\',\''.$row->ID.'\')"><i class="fas fa-receipt text-success"></i> Voucher</a>
             </li>
             <li>
             <a href="javascript:void(0)" class="dropdown-item" onclick="voucher(\'danger\',\''.$row->ID.'\')"><i class="fas fa-receipt text-danger"></i> Voucher</a>
             </li>
             <li>
             <a href="javascript:void(0)" class="dropdown-item" onclick="kwitansi(\''.$row->ID.'\')"><i class="fas fa-receipt text-primary"></i> Kwitansi</a>
             </li>
             <li>
             <a href="javascript:void(0)" class="dropdown-item" onclick="hapus(\''.$row->ID.'\')"><i class="fas fa-trash"></i> Hapus</a>
             </li>
             </ul>
             </div>
             ';
         })
        ->toJson(true);
    }

    public function voucher(){
        $color = $this->request->getVar('warna');
        $nomor = $this->request->getVar('nomor');
        $queryTable = $this->db->table('akun_kas_keluar');
        $queryTable->where('akun_kas_keluar.ID', $nomor);
        $res = $queryTable->get()->getRow();
        $html = ['html' => view('e-kas/pengeluaran/riwayat/modal/voucher', ['color' => $color,'nomor' => $nomor,'res' => $res]) ];

        echo json_encode($html);
    }
    public function kwitansi(){
        $nomor = $this->request->getVar('nomor');
        $queryTable = $this->db->table('akun_kas_keluar');
        $queryTable->where('akun_kas_keluar.ID', $nomor);
        $res = $queryTable->get()->getRow();
        $html = ['html' => view('e-kas/pengeluaran/riwayat/modal/kwitansi',['nomor' => $nomor,'res' => $res]) ];

        echo json_encode($html);
    }

    public function voucer_pdf($nomor){
    helper('terbilang_helper');
     $queryTable = $this->db->table('akun_kas_keluar');
     $queryTable->where('akun_kas_keluar.ID', $nomor);
     $queryTable->select('akun_kas_keluar.*');
     $queryTable->select('akun_perkiraan.NAMA_PERKIRAAN, akun_perkiraan.NOMOR AS NOMOR_PERKIRAAN');
     $queryTable->select('akun_perkiraan_group.GROUP_PERKIRAAN AS NAMA_PERKIRAAN_GROUP,akun_perkiraan_group.NO_PERKIRAAN AS NOMOR_PERKIRAAN_GROUP,akun_perkiraan_group.NORMAL');
     $queryTable->join('akun_perkiraan', 'akun_perkiraan.ID = akun_kas_keluar.PERKIRAAN_ID', 'left');
     $queryTable->join('akun_perkiraan_group', 'akun_perkiraan_group.ID = akun_perkiraan.ID_GROP_PERKIRAAN', 'left');
     $result = $queryTable->get()->getResultArray();
     
     $data = ['result' => $result];

     $html = view('e-kas/pengeluaran/riwayat/modal/pdf/voucher_pdf', $data);

     $pdf = view('pdf/landscape', ['html' => $html]);

     $mpdf = new \Mpdf\Mpdf([
        'mode' => 'utf-8',
        'format' => 'Legal',
        'orientation' => 'P',
        'tempDir' => WRITEPATH . 'mpdf_temp',
    ]);
    $mpdf->setFooter('Voucher');
    $mpdf->WriteHTML($pdf);
    $mpdf->SetTitle('Voucher');
    $this->response->setHeader('Content-Type', 'application/pdf');
    $mpdf->Output('Voucher.pdf', 'I');

    }

    public function kwitansi_pdf($nomor){
    helper('terbilang_helper');
    helper('tanggal_helper');
        $talbe = $this->db->table('akun_kas_keluar');
        $talbe->where('akun_kas_keluar.ID', $nomor);
        $result = $talbe->get()->getResultArray();
        $html = view('e-kas/pengeluaran/riwayat/modal/pdf/kwitansi_pdf', ['nomor' => $nomor, 'result' => $result]);

        $pdf = view('pdf/landscape', ['html' => $html]);

       
     $mpdf = new \Mpdf\Mpdf([
        'mode' => 'utf-8',
        'format' => 'Legal',
        'orientation' => 'P',
        'tempDir' => WRITEPATH . 'mpdf_temp',
    ]);
        $mpdf->setFooter('KWITANSSI');
        $mpdf->WriteHTML($pdf);
        $mpdf->SetTitle('KWITANSSI');
        $this->response->setHeader('Content-Type', 'application/pdf');
        $mpdf->Output('KWITANSSI.pdf', 'I');

    }

    public function pdfRekapan(){
        $filterPdf = $this->request->getVar('filterLaporan');

        echo json_encode(['path'=> $filterPdf]);
    }

    public function pdfPreview($pdf, $tanggal){
        helper('tgl_indo');

        $table = $this->db->table('akun_kas_keluar');
        if($pdf == 'jbk'){
            $table->select('akun_kas_keluar.*');
        $table->select('akun_perkiraan.NAMA_PERKIRAAN, akun_perkiraan.NOMOR AS NOMOR_PERKIRAAN');
        $table->select('akun_perkiraan_group.GROUP_PERKIRAAN AS NAMA_PERKIRAAN_GROUP,akun_perkiraan_group.NO_PERKIRAAN AS NOMOR_PERKIRAAN_GROUP,akun_perkiraan_group.NORMAL');
        $table->join('akun_perkiraan', 'akun_perkiraan.ID = akun_kas_keluar.PERKIRAAN_ID', 'left');
        $table->join('akun_perkiraan_group', 'akun_perkiraan_group.ID = akun_perkiraan.ID_GROP_PERKIRAAN', 'left');
        $table->where('MONTH(akun_kas_keluar.TANGGAL)', date('m',strtotime($tanggal)));
        $table->where('YEAR(akun_kas_keluar.TANGGAL)', date('Y',strtotime($tanggal)));
        $table->orderBy('akun_kas_keluar.NOMOR', 'ASC');
        $result = $table->get()->getResultArray();
        }else if($pdf == 'rjbk'){
        $table->select('
        SUM(akun_kas_keluar.DEBET) AS DEBET,
        SUM(akun_kas_keluar.KREDIT) AS KREDIT
        ');
        $table->select('
        akun_perkiraan_group.GROUP_PERKIRAAN,
        akun_perkiraan_group.NO_PERKIRAAN,
        akun_perkiraan_group.NORMAL
        ');
        $table->join('akun_perkiraan', 'akun_perkiraan.ID = akun_kas_keluar.PERKIRAAN_ID', 'left');
        $table->join('akun_perkiraan_group', 'akun_perkiraan.ID_GROP_PERKIRAAN = akun_perkiraan_group.ID', 'left');
        $table->where('MONTH(akun_kas_keluar.TANGGAL)', date('m',strtotime($tanggal)));
        $table->where('YEAR(akun_kas_keluar.TANGGAL)', date('Y',strtotime($tanggal)));
        $table->groupBy('akun_perkiraan_group.GROUP_PERKIRAAN, akun_perkiraan_group.NO_PERKIRAAN, akun_perkiraan_group.NORMAL');
        $result = $table->get()->getResultArray();
        }else if($pdf == 'dhhd'){
            $table->select('akun_kas_keluar.NOMOR AS NMR,akun_kas_keluar.TANGGAL as TGL,akun_kas_keluar.BANK,akun_kas_keluar.KETERANGAN,akun_kas_keluar.DEBET,akun_kas_keluar.KREDIT');
            $table->select('akun_perkiraan_group.GROUP_PERKIRAAN,akun_perkiraan_group.NO_PERKIRAAN,akun_perkiraan_group.NORMAL,akun_perkiraan.NAMA_PERKIRAAN,akun_perkiraan.NOMOR AS NOMOR_PERKIRAAN');
            $table->join('akun_perkiraan', 'akun_perkiraan.ID = akun_kas_keluar.PERKIRAAN_ID', 'left');
            $table->join('akun_perkiraan_group', 'akun_perkiraan.ID_GROP_PERKIRAAN = akun_perkiraan_group.ID', 'left');
            $table->where('MONTH(akun_kas_keluar.TANGGAL)', date('m',strtotime($tanggal)));
            $table->where('YEAR(akun_kas_keluar.TANGGAL)', date('Y',strtotime($tanggal)));
            $table->orderBy('akun_perkiraan_group.NO_PERKIRAAN' , 'ASC');
            $result = $table->get()->getResultArray();
        }else if($pdf == 'djhhd'){
            $table->where('MONTH(akun_kas_keluar.TANGGAL)', date('m',strtotime($tanggal)));
            $table->where('YEAR(akun_kas_keluar.TANGGAL)', date('Y',strtotime($tanggal)));
            $result = $table->get()->getResultArray();
        }
$html = view('e-kas/pengeluaran/riwayat/modal/pdf/pdf_rekapan_'.$pdf.'',['tanggal' =>
bulan(date('m',strtotime($tanggal))) ,'tahun' => date('Y',strtotime($tanggal)),'result' => $result]);


$convert = view('pdf/landscape', ['html' => $html]);

$mpdf = new \Mpdf\Mpdf([
'mode' => 'utf-8',
'format' => 'Legal',
'orientation' => 'P',
'tempDir' => WRITEPATH . 'mpdf_temp',
]);
$mpdf->setFooter('REKAP JURNAL BAYAR KAS');
$mpdf->WriteHTML($convert);
$mpdf->SetTitle('REKAP JURNAL BAYAR KAS');
$this->response->setHeader('Content-Type', 'application/pdf');
$mpdf->Output('REKAP JURNAL BAYAR KAS.pdf', 'I');
}

}