<div class="modal fade" id="modal" tabindex="-1" data-bs-backdrop="static" aria-labelledby="modalLabel" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen modal-dialog-centered">
        <div class="modal-content w-100">
            <div class="modal-header border-bottom">
                <h1 class="modal-title fs-5" id="modalLabel">Tambah <?= $title ?></h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="formAddPelanggan">
                    <div class="p-3 border rounded mb-4 bg-light">
                        <h5 class="mb-3 border-bottom pb-2">Data Registrasi Pelanggan</h5>
                        <div class="row g-3">
                            <div class="col-lg-2">
                                <label class="form-label">Tanggal Registrasi</label>
                                <input type="date" name="tgl_registrasi" value="<?= date('Y-m-d') ?>" class="form-control" required>
                            </div>
                            <div class="col-lg-2">
                                <label class="form-label">No. Sambungan</label>
                                <input type="text" class="form-control text-center" name="no_reg" id="resultIdSambungan" placeholder="[ OTOMATIS ]" readonly required>
                            </div>
                            <div class="col-lg-2">
                                <label class="form-label">Unit / Lokasi</label>
                                <select class="form-select" id="sambungan_baru" required>
                                <option value="" selected>[ Pilih Unit / Lokasi ]</option>
                                <?php foreach ($sambungan as $sambung) : ?>
                                    <option value="<?= $sambung['id_kec'] ?>"><?= $sambung['kecamatan'] ?></option>
                                <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-lg-2">
                                <label class="form-label">NIK</label>
                                <input type="number" name="NIK" class="form-control" placeholder="Nomor Induk Kependudukan" required>
                            </div>
                            <div class="col-lg-4">
                                <label class="form-label">Nama Lengkap</label>
                                <input type="text" name="nama_lengkap" class="form-control" placeholder="Masukan Nama Lengkap" required>
                            </div>

                            <div class="col-lg-3">
                                <label class="form-label">Status Pendaftaran</label>
                                <select name="status_pendaftaran" class="form-select" required>
                                <option value="" selected>[ Pilih Status Pendaftaran ]</option>
                                <option value="Pelanggan Baru">Baru</option>
                                <option value="Pelanggan Lama">Lama</option>
                                <option value="Pelanggan Bantuan">Bantuan</option>
                                </select>
                            </div>
                            <div class="col-lg-2">
                                <label class="form-label">Jml. Penghuni Tetap</label>
                                <input type="number" name="penghuni_tetap" class="form-control" placeholder="Jumlah Penghuni Tetap" required>
                            </div>
                            <div class="col-lg-2">
                                <label class="form-label">Jml. Penghuni Tidak Tetap</label>
                                <input type="number" name="penghuni_tidak_tetap" class="form-control" placeholder="Jumlah Penghuni Tidak Tetap" required>
                            </div>
                            <div class="col-lg-2">
                                <label class="form-label">Jml. Kran Dipasang</label>
                                <input type="number" name="jlh_kran" class="form-control" placeholder="Jumlah Kran yang Dipasang" required>
                            </div>
                            <div class="col-lg-3">
                                <label class="form-label">Kelompok Tarif</label>
                                <select name="id_jasa" class="form-select" id="kelompok_tarif" required>
                                <option value="" selected>[ Pilih Kelompok Tarif ]</option>
                                <?php foreach ($kelompok_tarif as $kelompok) : ?>
                                    <option value="<?= $kelompok['id_jasa'] ?>"><?= $kelompok['kelompok_jasa'] ?></option>
                                <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-lg-2">
                                <label class="form-label">Jenis Pelanggan</label>
                                <select name="id_jenis" class="form-select" id="jenis_pelanggan" required>
                                <option value="">[ Pilih Jenis ]</option>
                                </select>
                            </div>
                            <div class="col-lg-2">
                                <label class="form-label">Kabupaten</label>
                                <select name="id_kab" class="form-select" id="kabupaten" required>
                                <option value="">[ Pilih Kabupaten ]</option>
                                </select>
                            </div>
                            <div class="col-lg-2">
                                <label class="form-label">Kecamatan</label>
                                <select name="id_kec" class="form-select" id="kec" required>
                                <option value="">[ Pilih Kecamatan ]</option>
                                </select>
                            </div>
                            <div class="col-lg-2">
                                <label class="form-label">Kelurahan</label>
                                <select name="id_kel" class="form-select" id="kel" required>
                                <option value="">[ Pilih Kelurahan ]</option>
                                </select>
                            </div>
                            <div class="col-lg-4">
                                <label class="form-label">Alamat</label>
                                <input type="text" name="alamat" class="form-control" placeholder="Alamat Pemasangan Baru" required>
                            </div>
                        </div>
                    </div>

                    <div class="p-3 border rounded bg-secondary bg-opacity-10 mb-4">
                        <h5 class="text-center text-dark border-bottom pb-2 mb-3">Isian Khusus Petugas</h5>
                        <div class="row g-3">
                            <div class="col-lg-3">
                                <label class="form-label">Tanggal Pasang</label>
                                <input type="date" name="tgl_pasang" class="form-control" required>
                            </div>
                            <div class="col-lg-5">
                                <label class="form-label">Dipasang Oleh</label>
                                <input type="text" name="petugas_pemasang" class="form-control" placeholder="Masukan nama petugas Pemasangan baru" required>
                            </div>
                            <div class="col-lg-4">
                                <label class="form-label">Merek Meter</label>
                                <input type="text" name="merek_meter" class="form-control" placeholder="Masukan Merek Meter" required>
                            </div>
                            <div class="col-lg-4">
                                <label class="form-label">Lama Pemasangan</label>
                                <input type="text" name="lama_pemasangan" class="form-control" placeholder="Masukan Lama Pemasangan" required>
                            </div>
                            <div class="col-lg-4">
                                <label class="form-label">Ukuran Meter</label>
                                <input type="text" name="ukuran_meter" class="form-control" placeholder="Masukan Ukuran Meter" required>
                            </div>
                            <div class="col-lg-4">
                                <label class="form-label">Nomor Meter</label>
                                <input type="text" name="kode_segel" class="form-control" placeholder="Masukan Nomor Meter" required>
                            </div>
                        </div>
                    </div>

                    <div class="text-center">
                        <button type="submit" id="btnSave" class="btn btn-success btn-lg px-5">
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
    $('#formAddPelanggan').submit(function(e) {
        e.preventDefault();
        let formData = $(this).serialize();
        console.log(formData);
        $.ajax({
            type: "get",
            url: "<?= base_url('Master/Pelanggan/save') ?>",
            data: formData,
            dataType: "json",
            beforeSend: function() {
                $('#btnSave').attr('disabled', 'disabled');
                $('#btnSave').html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Loading...');
            },
            success: function(response) {
                $('#btnSave').attr('disabled', false);
                $('#btnSave').html('<i class="fa fa-save"></i> Simpan');
                if (response.message) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: response.message,
                        showConfirmButton: false,
                        timer: 1500
                    })
                    $('#modal').modal('hide');
                    $('#table').DataTable().ajax.reload(null, false);
                }
            },
            error: function(xhr, status, error) {
                $('#btnSave').attr('disabled', false);
                $('#btnSave').html('<i class="fa fa-save"></i> Simpan');
                console.log(xhr.responseText);
            }
        });
    });
    $(document).ready(function() {
        $('.form-select').select2({
            width: '100%',
            dropdownParent: $("#modal")
        });
    });

    $('#sambungan_baru').change(function(e) {
        e.preventDefault();
        var id_sambungan = $(this).find('option:selected').val();
        $.ajax({
            type: "post",
            url: "<?= base_url('Master/Pelanggan/getNoSambungan') ?>",
            data: {
                id_kec: id_sambungan
            },
            dataType: "json",
            success: function(response) {
                $('#resultIdSambungan').val(response.data);

                $('#kabupaten').html(`
                    <option>[ Pilih Kabupaten ]</option>
                    <option value="${response.id_kab}">${response.kab}</option>
                `);
            }
        });
    });

    $('#kabupaten').change(function(e) {
        e.preventDefault();
        let kabupaten = $(this).find('option:selected').val();

        $.ajax({
            type: "post",
            url: "<?= base_url('Master/Pelanggan/getkecamatan') ?>",
            data: {
                kabupaten: kabupaten
            },
            dataType: "json",
            beforeSend: function() {
                $('#kec').html('<option>Processing...</option>')
            },
            success: function(response) {
                $('#kec').html(response.data);
            },
            error: function() {
                $('#kec').html('<option>[Pilih Kecamatan]</option>');
            }
        });
    })

    $('#kec').change(function(e) {
        e.preventDefault();
        let kecamatan = $(this).find('option:selected').val();
        $.ajax({
            type: "post",
            url: "<?= base_url('Master/Pelanggan/getkelurahan') ?>",
            data: {
                kecamatan: kecamatan
            },
            dataType: "json",
            beforeSend: function() {
                $('#kel').html('<option>Processing...</option>')
            },
            success: function(response) {
                $('#kel').html(response.data);
            },
            error: function() {
                $('#kel').html('<option>[Pilih Kelurahan]</option>');
            }
        });
    })

    $('#kelompok_tarif').change(function(e) {
        e.preventDefault();
        let id_jasa = $(this).find('option:selected').val();
        $.ajax({
            type: "post",
            url: "<?= base_url('Master/Pelanggan/getJenis') ?>",
            data: {
                id: id_jasa,
            },
            dataType: "json",
            beforeSend: function() {
                $('#jenis_pelanggan').html('<option>Processing...</option>')
            },
            success: function(response) {
                $('#jenis_pelanggan').html(response.data);
            },
            error: function() {
                $('#jenis_pelanggan').html('<option>[Pilih Jenis Pelanggan]</option>');
            }
        });
    });
</script>

<script>
    var alertList = document.querySelectorAll(".alert");
    alertList.forEach(function(alert) {
        new bootstrap.Alert(alert);
    });
</script>