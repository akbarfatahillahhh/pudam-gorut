<div class="modal fade" id="modal" tabindex="-1" data-bs-backdrop="static" aria-labelledby="modalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-bottom">
                <h1 class="modal-title fs-5" id="modalLabel">Tambah <?= $title ?></h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="form-insert">
                    <input type="hidden" class="form-control" name="KODE" placeholder="KODE" value="<?= $KODE ?>" readonly required>
                    <div class="row">
                        <!--<div class="col-lg-12 mb-2">
                            <div class="card card-body shadow rounded-circle overflow-hidden d-flex mx-auto align-items-center justify-content-center"
                                style="width: 150px; height: 150px; border-radius: 50%; ">
                                <div id="previewHide" style="display: show; text-align: center">
                                    <span class="text-secondary text-center font-weight-light" style="font-size: 5rem">
                                        <i class="fa fa-image"></i>
                                    </span>
                                </div>
                                <div id="showFoto" style="display: none">
                                    <img src="" alt="" class="img-fluid w-100" id="previewImage"
                                        style="height: 150px; width: 150px; border-radius: 50%">
                                </div>
                            </div>
                            <label for="" class="form-label mb-1">Foto</label>
                            <input type="file" class="form-control" name="FOTO" accept="image/*" required>
                        </div> -->

                        <div class="col-lg-5 mb-2">
                            <label for="" class="form-label mb-1">NIK</label>
                            <input type="text" class="form-control" name="NIK" placeholder="NIK..." required>
                        </div>
                        <div class="col-lg-7 mb-2">
                            <label for="" class="form-label mb-1">Nama</label>
                            <input type="text" class="form-control" name="NAMA" placeholder="Nama Pegawai..." required>
                        </div>

                        <div class="col-lg-6 mb-2">
                            <label for="" class="form-label mb-1">Tempat Lahir</label>
                            <input type="text" class="form-control" name="TEMPAT_LAHIR" placeholder="Tempat Lahir..." required>
                        </div>
                        <div class="col-lg-3 mb-2">
                            <label for="" class="form-label mb-1">Tanggal Lahir</label>
                            <input type="date" class="form-control" name="TGL_LAHIR" required>
                        </div>
                        <div class="col-lg-3 mb-2">
                            <label for="" class="form-label mb-1">Jenis Kelamin</label>
                            <select type="text" class="form-control" name="KELAMIN" required>
                                <option value="" selected>[ Pilih Jenis Kelamin ]</option>
                                <option value="L">Laki-Laki</option>
                                <option value="P">Perempuan</option>
                            </select>
                        </div>

                        <div class="col-lg-6 mb-2">
                            <label for="" class="form-label mb-1">Alamat</label>
                            <input type="text" class="form-control" name="ALAMAT" placeholder="Alamat..." required>
                        </div>

                        <div class="col-lg-3 mb-2">
                            <label for="" class="form-label mb-1">Longitude</label>
                            <input type="text" name="LONGTITUDE" id="" class="form-control" placeholder="Longitude..." required>
                        </div>
                        <div class="col-lg-3 mb-2">
                            <label for="" class="form-label mb-1">Latitude</label>
                            <input type="text" name="LATITUDE" id="" class="form-control" placeholder="Latitude..." required>
                        </div>


                        <div class="col-lg-3 mb-2">
                            <label for="" class="form-label mb-1">Provinsi</label>
                            <select name="PROVINSI" id="prov" class="form-control" required>
                                <option value="">[ Pilih Provinsi ]</option>
                            </select>
                        </div>
                        <div class="col-lg-3 mb-2">
                            <label for="" class="form-label mb-1">Kabupaten</label>
                            <select name="KABUPATEN" id="kab" class="form-control" required>
                                <option value="">[ Pilih Kabupaten ]</option>
                            </select>
                        </div>
                        <div class="col-lg-3 mb-2">
                            <label for="" class="form-label mb-1">Kecamatan</label>
                            <select name="KECAMATAN" id="kec" class="form-control" required>
                                <option value="">[ Pilih Kecamatan ]</option>
                            </select>
                        </div>
                        <div class="col-lg-3 mb-2">
                            <label for="" class="form-label mb-1">Kelurahan</label>
                            <select name="KELURAHAN" id="kel" class="form-control" required>
                                <option value="">[ Pilih Kelurahan ]</option>
                            </select>
                        </div>

                    </div>
                    <div class="border-top text-end pt-3 mt-4">
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
    $('#form-insert').submit(function(e) {
        e.preventDefault();
        $.ajax({
            type: "POST",
            url: "<?= base_url('Master/Pegawai/master') ?>",
            data: $(this).serialize(),
            dataType: "json",
            success: function(response) {
                if (response.status == 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: response.message,
                        showConfirmButton: true,
                        timer: 1500
                    }).then(() => {
                        $('#modal').modal('hide');
                        location.reload();
                    })
                }
            }
        });
    });

    $('#prov').select2({
        width: '100%',
        dropdownParent: $("#modal"),
        ajax: {
            url: '<?= base_url('GetData/getprovinsi') ?>',
            type: 'GET',
            dataType: 'json',
            data: function(params) {
                return {
                    search: params.term
                };
            },
            processResults: function(data) {
                return {
                    results: data
                };
            },
            cache: true
        },
    });

    $('#kab').select2({
        width: '100%',
        dropdownParent: $("#modal"),
        ajax: {
            url: '<?= base_url('GetData/getkabupaten') ?>',
            type: 'GET',
            dataType: 'json',
            data: function(params) {
                return {
                    search: params.term,
                    provinsi: $('#prov').val()
                };
            },
            processResults: function(data) {
                return {
                    results: data
                };
            },
            cache: true
        }
    });

    $('#kec').select2({
        width: '100%',
        dropdownParent: $("#modal"),
        ajax: {
            url: '<?= base_url('GetData/getkecamatan') ?>',
            type: 'GET',
            dataType: 'json',
            data: function(params) {
                return {
                    search: params.term,
                    kabupaten: $('#kab').val()
                }
            },
            processResults: function(data) {
                return {
                    results: data
                };
            },
            cache: true
        }
    });

    $('#kel').select2({
        width: '100%',
        dropdownParent: $("#modal"),
        ajax: {
            url: '<?= base_url('GetData/getkelurahan') ?>',
            type: 'GET',
            dataType: 'json',
            data: function(params) {
                return {
                    search: params.term,
                    kecamatan: $('#kec').val()
                }
            },
            processResults: function(data) {
                return {
                    results: data
                };
            },
            cache: true
        }
    });
</script>