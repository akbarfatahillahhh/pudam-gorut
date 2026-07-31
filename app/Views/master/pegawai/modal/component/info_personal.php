<div class="row">
    <div class="col-lg-5 mt-2">
        <div class="card border border-dark">
            <div class="card-header border-bottom border-dark py-2 rounded-top">
                <h6 class="card-title fs-3 fw-semibold mb-0 text-center">BIODATA</h6>
            </div>
            <div class="card-body py-2 px-3">
                <form id="formBiodata">
                    <div class="row">
                        <div class="col-5 mb-2">
                            <label class="form-label">NIK</label>
                            <input type="text" class="form-control" name="nik" value="<?= $pegawai->NIK ?>">
                        </div>
                        <div class="col-7 mb-2">
                            <label class="form-label">Nama Pegawai</label>
                            <input type="text" class="form-control" name="nama" value="<?= $pegawai->NAMA ?>">
                        </div>

                        <div class="col-lg-3 mb-2">
                            <label class="form-label">Tanggal Lahir</label>
                            <input type="date" class="form-control" name="tanggal_lahir" value="<?= $pegawai->TGL_LAHIR ?>">
                        </div>
                        <div class="col-lg-5 mb-2">
                            <label class="form-label">Tempat Lahir</label>
                            <input type="text" class="form-control" name="tempat_lahir" value="<?= $pegawai->TEMPAT_LAHIR ?>">
                        </div>

                        <div class="col-lg-4 mb-2">
                            <label class="form-label">Jenis Kelamin</label>
                            <select name="jenis_kelamin" class="form-select">
                                <option value="">[ Pilih Jenis Kelamin ]</option>
                                <?php $kelamin = ['L' => 'Laki-Laki', 'P' => 'Perempuan'] ?>
                                <?php foreach ($kelamin as $key => $value) : ?>
                                    <option value="<?= $key ?>" <?= $pegawai->KELAMIN == $key ? 'selected' : '' ?>><?= $value ?></option>
                                <?php endforeach ?>
                            </select>
                        </div>

                        <div class="col-12 my-2">
                            <button type="submit" class="btn btn-primary w-100" id="btnSimpanBiodata">
                                <i class="fa fa-save"></i>
                                Simpan Biodata
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4 mt-2">
        <div class="card border border-dark">
            <div class="card-header border-bottom border-dark py-2 rounded-top">
                <h6 class="card-title fs-3 fw-semibold mb-0 text-center">ALAMAT</h6>
            </div>
            <div class="card-body py-2 px-3">
                <form id="formAlamat">
                    <div class="row">
                        <div class="col-lg-6 mb-2">
                            <label class="form-label">Provinsi</label>
                            <select class="form-select" id="provinsi">
                                <option value="">[ Pilih Provinsi ]</option>
                                <option value="<?= $pegawai->PROVINSI_ID ?? '' ?>" selected><?= $pegawai->PROVINSI ?? '' ?></option>
                            </select>
                        </div>
                        <div class="col-lg-6 mb-2">
                            <label class="form-label">Kabupaten</label>
                            <select class="form-select" id="kabupaten">
                                <option value="">[ Pilih Kabupaten ]</option>
                                <option value="<?= $pegawai->KABUPATEN_ID ?? '' ?>" selected><?= $pegawai->KABUPATEN ?? '' ?></option>
                            </select>
                        </div>
                        <div class="col-lg-6 mb-2">
                            <label class="form-label">Kecamatan</label>
                            <select class="form-select" id="kecamatan">
                                <option value="">[ Pilih Kecamatan ]</option>
                                <option value="<?= $pegawai->KECAMATAN_ID ?? ''?>" selected><?= $pegawai->KECAMATAN ?? '' ?></option>
                            </select>
                        </div>
                        <div class="col-lg-6 mb-2">
                            <label class="form-label">Kelurahan/Desa</label>
                            <select class="form-select" id="kelurahan">
                                <option value="">[ Pilih Kelurahan/Desa ]</option>
                                <option value="<?= $pegawai->KELURAHAN_ID ? 'ADA' :'TIDAK' ?>" selected><?= $pegawai->KELURAHAN ? 'ADA' : 'TIDAK' ?></option>
                            </select>
                        </div>

                        <div class="col-12 my-2">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fa fa-save"></i>
                                Simpan Alamat
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-3 mt-2">
        <div class="card border border-dark">
            <div class="card-header border-bottom border-dark py-2 rounded-top">
                <h6 class="card-title fs-3 fw-semibold mb-0 text-center">TITIK ABSENSI KARYAWAN</h6>
            </div>
            <div class="card-body py-2 px-3">
                <div class="text-center">
                    <select class="form-select text-center" id="titik_absen">
                        <option value="">[ Ganti Titik Absensi ]</option>
                        <?php foreach ($titik_absen as $key => $value) : ?>
                            <option
                                value="<?= $value['ID'] ?>"
                                data-lat="<?= $value['LATITUDE'] ?>"
                                data-long="<?= $value['LONGITUDE'] ?>"
                                <?= $pegawai->LATITUDE == $value['LATITUDE'] && $pegawai->LONGITUDE == $value['LONGITUDE'] ? 'selected' : '' ?>>
                                <?= $value['UNIT'] ?>
                            </option>
                        <?php endforeach ?>
                    </select>
                </div>
                <div class="text-center py-2">
                    <span class="form-label border-bottom border-dark">TITIK ABSENSI SAAT INI</span>
                </div>

                <div class="">
                    <input type="hidden" id="latitude" value="<?= $pegawai->LATITUDE ?? '-' ?>">
                    <input type="hidden" id="longitude" value="<?= $pegawai->LONGITUDE ?? '-' ?>">
                    <?php if ($pegawai->LONGITUDE && $pegawai->LATITUDE) : ?>
                        <div class="mb-3">
                            <h6 class="fw-semibold">Latitude:</h6>
                            <span class="form-label">[ <span id="text-LATITUDE"><?= $pegawai->LATITUDE ?? '-' ?></span> ]</span>
                        </div>
                        <div class="mb-3">
                            <h6 class="fw-semibold">Longitude:</h6>
                            <span class="form-label">[ <span id="text-LONGITUDE"><?= $pegawai->LONGITUDE ?? '-' ?></span> ]</span>
                        </div>
                    <?php else : ?>
                        <div class="alert small alert-danger" role="alert">
                            <div class="text-center">
                                <i class="fa fa-info-circle pe-1"></i>
                                <span class="fw-semibold">MOHON SETTING TITIK ABSENSI</span>
                            </div>
                        </div>
                    <?php endif ?>

                    <div class="my-2">
                        <button type="button" class="btn btn-primary w-100" id="btnSimpanTitikAbsen">
                            <i class="fa fa-save"></i>
                            Simpan Titik Absensi
                        </button>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        get_select_provinsi();
        $('#titik_absen').select2({
            width: '100%',
            dropdownParent: $("#modal")
        });
    });

    function get_select_provinsi() {}

    $('#formBiodata').submit(function(e) {
        e.preventDefault();
        var formData = new FormData(this);
        formData.append('id_pegawai', '<?= $pegawai->ID ?>');
        $.ajax({
            type: "post",
            url: "<?= base_url('Master/Pegawai/saveBiodata') ?>",
            data: formData,
            contentType: false,
            processData: false,
            dataType: "json",
            beforeSend: function() {
                Swal.fire({
                    icon: 'warning',
                    title: 'Loading...',
                    html: 'Sedang Diproses',
                    showConfirmButton: false,
                    allowOutsideClick: false,
                    onBeforeOpen: () => {
                        Swal.showLoading()
                    }
                });
            },
            complete: function() {
                Swal.close();
            },
            success: function(response) {
                Swal.fire('Berhasil!', response.msg, 'success');
            }
        });
    });

    $('#formAlamat').submit(function(e) {
        e.preventDefault();
        var formData = new FormData(this);
        formData.append('id_pegawai', '<?= $pegawai->ID ?>');
        $.ajax({
            type: "post",
            url: "<?= base_url('Master/Pegawai/saveAlamat') ?>",
            data: formData,
            contentType: false,
            processData: false,
            dataType: "json",
            beforeSend: function() {
                Swal.fire({
                    icon: 'warning',
                    title: 'Loading...',
                    html: 'Sedang Diproses',
                    showConfirmButton: false,
                    allowOutsideClick: false,
                    onBeforeOpen: () => {
                        Swal.showLoading()
                    }
                });
            },
            complete: function() {
                Swal.close();
            },
            success: function(response) {
                Swal.fire('Berhasil!', response.msg, 'success');
            }
        });
    });

    $('#titik_absen').change(function(e) {
        e.preventDefault();
        var lat = $(this).find('option:selected').data('lat');
        var long = $(this).find('option:selected').data('long');
        $('#latitude').val(lat);
        $('#longitude').val(long);
    });
    $('#btnSimpanTitikAbsen').click(function(e) {
        e.preventDefault();
        var lat = $('#latitude').val();
        var long = $('#longitude').val();

        Swal.fire({
            icon: 'warning',
            title: 'Ubah titik absensi ?',
            text: "Apakah anda yakin ingin mengubah titik absensi ?",
            showCancelButton: true,
            confirmButtonText: 'Ya',
            cancelButtonText: 'Tidak',
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    type: "post",
                    url: "<?= base_url('Master/Pegawai/saveTitikAbsen') ?>",
                    data: {
                        id_pegawai: '<?= $pegawai->ID ?>',
                        lat: lat,
                        long: long
                    },
                    dataType: "json",
                    beforeSend: function() {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Loading...',
                            html: 'Sedang Diproses',
                            showConfirmButton: false,
                            allowOutsideClick: false,
                            onBeforeOpen: () => {
                                Swal.showLoading()
                            }
                        });
                    },
                    complete: function() {
                        Swal.close();
                    },
                    success: function(response) {
                        $('#text-LATITUDE').text(lat);
                        $('#text-LONGITUDE').text(long);

                        Swal.fire('Berhasil!', response.msg, 'success');
                    }
                });
            }
        });
    });
</script>