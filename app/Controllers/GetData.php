<?php

namespace App\Controllers;

class Getdata extends BaseController
{

    public function GetPosisi()
    {
        if ($this->request->isAJAX()) {
            $caridata = $this->request->getGet('search');
            if ($caridata != "") {
                $table = $this->db->table('m_posisi')
                ->LIKE('posisi', $caridata)
                ->LIKE('blokir', 'N')
                ->get();
            } else {
                $table = $this->db->table('m_posisi')
                ->LIKE('blokir', 'N')
                ->get();
            }

            if ($table->getNumRows() > 0) {
                $list = [];
                $key = 0;
                $list[$key]['id'] = 'all';
                $list[$key]['text'] = 'SEMUA POSISI';
                foreach ($table->getResultArray() as $row) :
                    $key++;
                    $list[$key]['id'] = $row['id'];
                    $list[$key]['text'] = $row['posisi'];
                endforeach;
                echo json_encode($list);
            }
        }
    }

    public function GetSatuan()
    {
        if ($this->request->isAJAX()) {
            $caridata = $this->request->getGet('search');
            if ($caridata != "") {
                $table = $this->db->table('m_satuan')
                ->LIKE('nama_satuan', $caridata)
                ->LIKE('status', 'Y')
                ->get();
            } else {
                $table = $this->db->table('m_satuan')
                ->LIKE('status', 'Y')
                ->get();
            }

            if ($table->getNumRows() > 0) {
                $list = [];
                $key = 0;
                foreach ($table->getResultArray() as $row) :
                    $list[$key]['id'] = $row['id'];
                    $list[$key]['text'] = $row['nama_satuan'];
                    $key++;
                endforeach;
                echo json_encode($list);
            }
        }
    }

    public function GetSupplier()
    {
        if ($this->request->isAJAX()) {
            $caridata = $this->request->getGet('search');
            if ($caridata != "") {
                $table = $this->db->table('m_suplier')
                ->LIKE('nama_supplier', $caridata)
                ->orLIKE('kode_supplier', $caridata)
                ->orLIKE('blokir', 'N')
                ->get();
            } else {
                $table = $this->db->table('m_suplier')
                ->LIKE('blokir', 'N')
                ->get();
            }

            if ($table->getNumRows() > 0) {
                $list = [];
                $key = 0;
                foreach ($table->getResultArray() as $row) :
                    $list[$key]['id'] = $row['id'];
                    $list[$key]['text'] = $row['nama_supplier'];
                    $key++;
                endforeach;
                echo json_encode($list);
            }
        }
    }

    public function GetMerek()
    {
        if ($this->request->isAJAX()) {
            $caridata = $this->request->getGet('search');
            if ($caridata != "") {
                $table = $this->db->table('m_merek')
                ->LIKE('merek', $caridata)
                ->LIKE('status', 'Y')
                ->get();
            } else {
                $table = $this->db->table('m_merek')
                ->LIKE('status', 'Y')
                ->get();
            }

            if ($table->getNumRows() > 0) {
                $list = [];
                $key = 0;
                $list[$key]['id'] = 'all';
                $list[$key]['text'] = 'SEMUA MEREK';
                foreach ($table->getResultArray() as $row) :
                    $key++;
                    $list[$key]['id'] = $row['id'];
                    $list[$key]['text'] = $row['merek'];
                endforeach;
                echo json_encode($list);
            }
        }
    }

    public function GetKategori()
    {
        if ($this->request->isAJAX()) {
            $caridata = $this->request->getGet('search');
            if ($caridata != "") {
                $table = $this->db->table('m_kategori')
                ->LIKE('kategori', $caridata)
                ->LIKE('status', 'Y')
                ->get();
            } else {
                $table = $this->db->table('m_kategori')
                ->LIKE('status', 'Y')
                ->get();
            }

            if ($table->getNumRows() > 0) {
                $list = [];
                $key = 0;
                $list[$key]['id'] = 'all';
                $list[$key]['text'] = 'SEMUA KATEGORI';
                foreach ($table->getResultArray() as $row) :
                    $key++;
                    $list[$key]['id'] = $row['id'];
                    $list[$key]['text'] = $row['kategori'];
                endforeach;
                echo json_encode($list);
            }
        }
    }

    public function getKreditur()
    {
        if ($this->request->isAJAX()) {
            $caridata = $this->request->getGet('search');
            if ($caridata != "") {
                $table = $this->db->table('m_kreditur')
                ->LIKE('kode', $caridata)
                ->orLIKE('nama_kreditur', $caridata)
                ->get();
            } else {
                $table = $this->db->table('m_kreditur')
                ->get();
            }

            if ($table->getNumRows() > 0) {
                $list = [];
                $key = 0;
                foreach ($table->getResultArray() as $row) :
                    $list[$key]['id'] = $row['id'];
                    $list[$key]['text'] = $row['nama_kreditur'];
                    $key++;
                endforeach;
                echo json_encode($list);
            }
        }
    }

    public function GetToko()
    {
        if ($this->request->isAJAX()) {
            $caridata = $this->request->getGet('search');
            if ($caridata != "") {
                $table = $this->db->table('m_toko')
                    ->LIKE('kode_toko', $caridata)
                    ->LIKE('nama_toko', $caridata)
                    ->get();
            } else {
                $table = $this->db->table('m_toko')
                    ->get();
            }

            if ($table->getNumRows() > 0) {
                $list = [];
                $key = 0;
                $list[$key]['id'] = 'all';
                $list[$key]['text'] = 'SEMUA TOKO';
                foreach ($table->getResultArray() as $row) :
                    $key++;
                    $list[$key]['id'] = $row['id'];
                    $list[$key]['text'] = $row['nama_toko'];
                endforeach;
                echo json_encode($list);
            }
        }
    }

    public function getTokoOnly()
    {
        if ($this->request->isAJAX()) {
            $caridata = $this->request->getGet('search');
            if ($caridata != "") {
                $caridata = strtoupper($caridata);
                $table = $this->db->table('m_toko')
                    ->LIKE('nama_toko', $caridata)
                    ->get();
            } else {
                $table = $this->db->table('m_toko')
                    ->get();
            }
            if ($table->getNumRows() > 0) {
                $list = [];
                $key = 0;
                foreach ($table->getResultArray() as $row) :
                    $list[$key]['id'] = $row['id'];
                    $list[$key]['text'] = $row['nama_toko'];
                    $key++;
                endforeach;
                echo json_encode($list);
            }
        }
    }

    public function GetBarang()
    {
        if ($this->request->isAJAX()) {
            $caridata = $this->request->getGet('search');
            if ($caridata != "") {
                $table = $this->db->table('m_barang')
                    ->LIKE('kode_brg', $caridata)
                    ->orLIKE('nama_brg', $caridata)
                    ->get();
            } else {
                $table = $this->db->table('m_barang')
                    ->get();
            }

            if ($table->getNumRows() > 0) {
                $list = [];
                $key = 0;
                foreach ($table->getResultArray() as $row) :
                    $list[$key]['id'] = $row['id'];
                    $list[$key]['text'] = $row['nama_brg'];
                    $key++;
                endforeach;
                echo json_encode($list);
            }
        }
    }

    public function GetJenisPengeluaran()
    {
        if ($this->request->isAJAX()) {
            $caridata = $this->request->getGet('search');
            if ($caridata != "") {
                $table = $this->db->table('m_jenis_pengeluaran')
                    ->LIKE('jenis_pengeluaran', $caridata)
                    ->get();
            } else {
                $table = $this->db->table('m_jenis_pengeluaran')
                    ->get();
            }

            if ($table->getNumRows() > 0) {
                $list = [];
                $key = 0;
                $list[$key]['id'] = 'all';
                $list[$key]['text'] = 'SEMUA JENIS PENGELUARAN';
                foreach ($table->getResultArray() as $row) :
                    $key++;
                    $list[$key]['id'] = $row['id'];
                    $list[$key]['text'] = $row['jenis_pengeluaran'];
                endforeach;
                echo json_encode($list);
            }
        }
    }

    public function getprovinsi()
    {
        if ($this->request->isAJAX()) {
            $caridata = $this->request->getGet('search');
            if ($caridata != "") {
                $instalasi = $this->db->table('master_provinsi')

                    ->LIKE('ID', $caridata)
                    ->orLIKE('PROVINSI', $caridata)
                    // ->WHERE('NORMAL', 'DEBET')
                    ->get();
            } else {
                $instalasi = $this->db->table('master_provinsi')
                    // ->WHERE('NORMAL', 'DEBET')
                    ->get();
            }

            if ($instalasi->getNumRows() > 0) {
                $list = [];
                $key = 0;
                foreach ($instalasi->getResultArray() as $row) :
                    $list[$key]['id'] = $row['ID'];
                    $list[$key]['text'] = $row['PROVINSI'];
                    $key++;
                endforeach;
                echo json_encode($list);
            }
        }
    }

    public function getkabupaten()
    {
        if ($this->request->isAJAX()) {
            $provinsi = $this->request->getVar('provinsi');

            $dtkabupaten = $this->db->table('master_kabupaten')->where('IDPROVINSI', $provinsi)->get();

            if ($dtkabupaten->getNumRows() > 0) {

                $isidata = "";
                $isidata .= "<option>[ Pilih Kabupaten ]</option>";
                foreach ($dtkabupaten->getResultArray() as $row) :

                    $isidata .= '<option value="' . $row['ID'] . '">' . $row['KABUPATEN'] . '</option>';

                endforeach;

                $msg = [
                    'data' => $isidata
                ];

                echo json_encode($msg);
            } else {
                $msg = [
                    'kosong' => "<option>Data tidak ditemukan</option>"
                ];
            }
        }
    }

    public function getkecamatan()
    {
        if ($this->request->isAJAX()) {
            $kabupaten = $this->request->getVar('kabupaten');

            $dtkecamatan = $this->db->table('master_kecamatan')->where('IDKAB', $kabupaten)->get();

            if ($dtkecamatan->getNumRows() > 0) {

                $isidata = "";
                $isidata .= "<option>[ Pilih Kecamatan ]</option>";
                foreach ($dtkecamatan->getResultArray() as $row) :

                    $isidata .= '<option value="' . $row['ID'] . '">' . $row['KECAMATAN'] . '</option>';

                endforeach;

                $msg = [
                    'data' => $isidata
                ];

                echo json_encode($msg);
            } else {
                $msg = [
                    'kosong' => "<option>Data tidak ditemukan</option>"
                ];
            }
        }
    }

    public function getkelurahan()
    {
        if ($this->request->isAJAX()) {
            $kecamatan = $this->request->getVar('kecamatan');

            $dtkelurahan = $this->db->table('master_kelurahan')->where('IDKEC', $kecamatan)->get();

            if ($dtkelurahan->getNumRows() > 0) {

                $isidata = "";
                $isidata .= "<option>[ Pilih Kelurahan ]</option>";
                foreach ($dtkelurahan->getResultArray() as $row) :

                    $isidata .= '<option value="' . $row['ID'] . '">' . $row['KELURAHAN'] . '</option>';

                endforeach;

                $msg = [
                    'data' => $isidata
                ];

                echo json_encode($msg);
            } else {
                $msg = [
                    'kosong' => "<option>Data tidak ditemukan</option>"
                ];
            }
        }
    }
}
