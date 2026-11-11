<div class="modal fade" id="modal" tabindex="-1" data-bs-backdrop="static"  aria-labelledby="modalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="modalLabel">Setting Akses</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="form-setting-akses">
                    <div>
                        
                       <div class="">
                            <div class="row text-black">
                                <div class="col-md-4 p-1">
                                    <div class="border border-dark p-3 rounded">
                                        <!-- DASHBOARD -->
                                        <div>
                                            <label class="fw-bolder mb-2 d-block">Home</label>
                                            <div class="ms-3">
                                                <div class="form-check">
                                                    <input class="form-check-input border-dark" type="checkbox" name="menu[]" value="dashboard" id="dashboard" checked <?= $dashboard ?> disabled>
                                                    <label class="form-check-label fw-bold" for="menuDashboard">Dashboard</label>
                                                </div>
                                            </div>
                                        </div>
    
                                        <!-- MASTER DATA -->
                                        <div>
                                            <label class="fw-bolder mb-2 mt-3 d-block">Master Data</label>
                                            <div class="ms-3">
                                                <div class="form-check">
                                                    <input class="form-check-input border-dark" type="checkbox" name="menu[]" value="perkiraan" id="perkiraan" <?= $perkiraan ?>>
                                                    <label class="form-check-label" for="menuPerkiraan">Perkiraan</label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input border-dark" type="checkbox" name="menu[]" value="jabatan" id="jabatan" <?= $jabatan ?>>
                                                    <label class="form-check-label" for="menuJabatan">Jabatan</label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input border-dark" type="checkbox" name="menu[]" value="tugas_pokok" id="tugas_pokok" <?= $tugas_pokok ?>>
                                                    <label class="form-check-label" for="menuTugasPokok">Tugas Pokok</label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input border-dark" type="checkbox" name="menu[]" value="data_pegawai" id="data_pegawai" <?= $data_pegawai ?>>
                                                    <label class="form-check-label" for="menuDataPegawai">Data Pegawai</label>
                                                </div>
                                            </div>
                                        </div>
    
                                        <!-- MASTER PELANGGAN -->
                                        <div>
                                            <label class="fw-bolder mb-2 mt-3 d-block">Master Pelanggan</label>
                                            <div class="ms-3">
                                                <div class="form-check">
                                                    <input class="form-check-input border-dark" type="checkbox" name="menu[]" value="daftar_pelanggan" id="daftar_pelanggan" <?= $daftar_pelanggan ?>>
                                                    <label class="form-check-label" for="menuDaftarPelanggan">Daftar Pelanggan</label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input border-dark" type="checkbox" name="menu[]" value="kelompok_tarif" id="kelompok_tarif" <?= $kelompok_tarif ?>>
                                                    <label class="form-check-label" for="menuKelompokTarif">Kelompok Tarif</label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input border-dark" type="checkbox" name="menu[]" value="jenis_pelanggan" id="jenis_pelanggan" <?= $jenis_pelanggan ?>>
                                                    <label class="form-check-label" for="menuJenisPelanggan">Jenis Pelanggan</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                    
                                <div class="col-md-4 p-1">
                                    <div class="border border-dark p-3 rounded">
                                        <!-- MASTER WILAYAH -->
                                        <div>
                                            <label class="fw-bolder mb-2 d-block">Master Wilayah</label>
                                            <div class="ms-3">
                                                <div class="form-check">
                                                    <input class="form-check-input border-dark" type="checkbox" name="menu[]" value="kabupaten" id="kabupaten" <?= $kabupaten ?>>
                                                    <label class="form-check-label" for="menuKabupaten">Kabupaten</label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input border-dark" type="checkbox" name="menu[]" value="kecamatan" id="kecamatan" <?= $kecamatan ?>>
                                                    <label class="form-check-label" for="menuKecamatan">Kecamatan</label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input border-dark" type="checkbox" name="menu[]" value="kelurahan" id="kelurahan" <?= $kelurahan ?>>
                                                    <label class="form-check-label" for="menuKelurahan">Kelurahan</label>
                                                </div>
                                            </div>
                                        </div>
                                            
                                        <!-- AKUN -->
                                        <div>
                                            <label class="fw-bolder mb-2 mt-3 d-block">Akun</label>
                                            <div class="ms-3">
                                                <div class="form-check">
                                                    <input class="form-check-input border-dark" type="checkbox" name="menu[]" value="user_akun" id="user_akun" <?= $user_akun ?>>
                                                    <label class="form-check-label" for="menuUserAkun">User/Akun</label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input border-dark" type="checkbox" name="menu[]" value="absensi" id="absensi" <?= $absensi ?>>
                                                    <label class="form-check-label" for="menuAbsensi">Absensi</label>
                                                </div>
                                            </div>
                                        </div>
    
                                        <!-- LOGISTIK -->
                                        <div>
                                            <label class="fw-bolder mb-2 mt-3 d-block">Logistik</label>
                                            <div class="ms-3">
                                                <div class="form-check">
                                                    <input class="form-check-input border-dark" type="checkbox" name="menu[]" value="faktur_pembelian" id="faktur_pembelian" <?= $faktur_pembelian ?>>
                                                    <label class="form-check-label" for="menuFakturPembelian">Faktur Pembelian</label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input border-dark" type="checkbox" name="menu[]" value="entry_permintaan" id="entry_permintaan" <?= $entry_permintaan ?>>
                                                    <label class="form-check-label" for="menuEntryPermintaan">Entry Permintaan</label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input border-dark" type="checkbox" name="menu[]" value="data_barang" id="data_barang" <?= $data_barang ?>>
                                                    <label class="form-check-label" for="menuDataBarang">Gudang > Data Barang</label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input border-dark" type="checkbox" name="menu[]" value="cek_permintaan" id="cek_permintaan" <?= $cek_permintaan ?>>
                                                    <label class="form-check-label" for="menuCekPermintaan">Gudang > Cek Permintaan</label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input border-dark" type="checkbox" name="menu[]" value="opname_stok" id="opname_stok" <?= $opname_stok ?>>
                                                    <label class="form-check-label" for="menuOpnameStok">Gudang > Opname Stok</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-4 p-1">
                                    <div class="border border-dark p-3 rounded">
                                        <!-- PELANGGAN -->
                                        <div>
                                            <label class="fw-bolder mb-2 d-block">Pelanggan</label>
                                            <div class="ms-3">
                                                <div class="form-check">
                                                    <input class="form-check-input border-dark" type="checkbox" name="menu[]" value="pelanggan" id="pelanggan" <?= $pelanggan ?>>
                                                    <label class="form-check-label" for="menuPelanggan">Pelanggan</label>
                                                </div>
                                            </div>
                                        </div>
    
                                        <!-- KAS -->
                                        <div>
                                            <label class="fw-bolder mb-2 mt-3 d-block">Kas</label>
                                            <div class="ms-3">
                                                <div class="form-check">
                                                    <input class="form-check-input border-dark" type="checkbox" name="menu[]" value="kas_pengeluaran" id="kas_pengeluaran" <?= $kas_pengeluaran ?>>
                                                    <label class="form-check-label" for="menuKasPengeluaran">Kas Keluar > Kas Pengeluaran</label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input border-dark" type="checkbox" name="menu[]" value="riwayat_kas_pengeluaran" id="riwayat_kas_pengeluaran" <?= $riwayat_kas_pengeluaran ?>>
                                                    <label class="form-check-label" for="menuRiwayatKasPengeluaran">Kas Keluar > Riwayat Kas Pengeluaran</label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input border-dark" type="checkbox" name="menu[]" value="penerimaan_kas_air" id="penerimaan_kas_air" <?= $penerimaan_kas_air ?>>
                                                    <label class="form-check-label" for="menuPenerimaanKasAir">Kas Masuk > Penerimaan Kas Air</label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input border-dark" type="checkbox" name="menu[]" value="penerimaan_kas_non_air" id="penerimaan_kas_non_air" <?= $penerimaan_kas_non_air ?>>
                                                    <label class="form-check-label" for="menuPenerimaanKasNonAir">Kas Masuk > Penerimaan Kas Non Air</label>
                                                </div>
                                            </div>
                                        </div>
    
                                        <!-- LAPORAN -->
                                        <div>
                                            <label class="fw-bolder mb-2 mt-3 d-block">Laporan</label>
                                            <div class="ms-3">
                                                <div class="form-check">
                                                    <input class="form-check-input border-dark" type="checkbox" name="menu[]" value="drd_air" id="drd_air" <?= $drd_air ?>>
                                                    <label class="form-check-label" for="menuDRDAir">DRD Air</label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input border-dark" type="checkbox" name="menu[]" value="drd_unit_jenis" id="drd_unit_jenis" <?= $drd_unit_jenis ?>>
                                                    <label class="form-check-label" for="menuDRDUnitJenis">DRD Unit/Jenis</label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input border-dark" type="checkbox" name="menu[]" value="rincian_piutang_perunit" id="rincian_piutang_perunit" <?= $rincian_piutang_perunit ?>>
                                                    <label class="form-check-label" for="menuRincianPiutang">Rincian Piutang Perunit</label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input border-dark" type="checkbox" name="menu[]" value="pelanggan_duplikat" id="pelanggan_duplikat" <?= $pelanggan_duplikat ?>>
                                                    <label class="form-check-label" for="menuPelangganDuplikat">Pelanggan Duplikat</label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input border-dark" type="checkbox" name="menu[]" value="rekapitulasi_piutang" id="rekapitulasi_piutang" <?= $rekapitulasi_piutang ?>>
                                                    <label class="form-check-label" for="menuRekapitulasiPiutang">Rekapitulasi Piutang</label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input border-dark" type="checkbox" name="menu[]" value="rekap_persediaan" id="rekap_persediaan" <?= $rekap_persediaan ?>>
                                                    <label class="form-check-label" for="menuRekapPersediaan">Rekap Persediaan</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                    <div class="border-top text-end pt-3 mt-4">
                        <input type="hidden" name="id_session" value="<?= $id_session ?>">
                        <button type="submit" class="btn btn-sm btn-success">
                            <i class="fa fa-save"></i>
                            Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function () {
        $('#form-setting-akses').submit(function (e) { 
            e.preventDefault();
            $.ajax({
                type: "post",
                url: "<?= base_url('Master/Akun/save_setting_akses') ?>",
                data: $(this).serialize(),
                dataType: "json",
                success: function (response) {
                    
                }
            });
        });
    });
</script>