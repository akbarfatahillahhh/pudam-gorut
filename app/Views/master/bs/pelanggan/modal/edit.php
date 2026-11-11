<div class="modal fade" id="modal" tabindex="-1" data-bs-backdrop="static" aria-labelledby="modalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content w-100">
            <div class="modal-header border-bottom">
                <h1 class="modal-title fs-5" id="modalLabel">Edit <?= $title ?></h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editJP">
                    <div class="row">
                        <div class="col-lg-3 mb-2">
                            <label for="" class="form-label">No. Sambungan</label>
                            <input type="text" readonly name="no_sambungan" value="<?= $data['no_sambungan'] ?>" class="form-control" placeholder="Kelompok Tarif Pelanggan">
                        </div>

                        <div class="col-lg-9 mb-2">
                            <label for="" class="form-label">Nama Pelanggan</label>
                            <input type="text" name="nama_pelanggan" class="form-control" value="<?= $data['nama_pelanggan'] ?>" placeholder="Bea. Pemasangan">
                        </div>

                        <div class="col-lg-6 mb-2">
                            <label class="form-label">Kelompok Tarif</label>
                            <select class="form-select" id="kelompok_tarif" name="id_jasa">
                                <?php foreach ($jasa as $jasa_val) : ?>
                                    <option value="<?= $jasa_val['id_jasa'] ?>" <?= ($data['id_jasa'] == $jasa_val['id_jasa']) ? 'selected' : '' ?>><?= $jasa_val['kelompok_jasa'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-lg-6 mb-2">
                            <label class="form-label">Jenis Pelanggan</label>
                            <select class="form-select" id="jenis_pelanggan" name="id_jenis">
                                <option value="">[ Pilih Jenis Pelanggan ]</option>
                                <?php foreach ($jasa as $jenis_val) : ?>
                                    <option value="<?= $jenis_val['id_jenis'] ?>" <?= ($data['id_jenis'] == $jenis_val['id_jenis']) ? 'selected' : '' ?>><?= $jenis_val['jenis_pelanggan'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-lg-4 mb-2">
                            <label for="" class="form-label">Kabupaten</label>
                            <select class="form-select" id="kabupaten" name="id_kab">
                                <option value="">[ Pilih Kabupaten ]</option>
                                <?php foreach ($kab as  $kab_val) : ?>
                                    <option value="<?= $kab_val['id_kab'] ?>" <?= ($kab_val['id_kab'] == $data['id_kab']) ? 'selected' : '' ?>><?= $kab_val['kabupaten'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-lg-4 mb-2">
                            <label class="form-label">Kecamatan</label>
                            <select class="form-select" id="kec" name="id_kec">
                                <option value="">[ Pilih Kecamatan]</option>
                                <?php foreach ($kec as $kec_val) :  ?>
                                    <option value="<?= $kec_val['id_kec'] ?>" <?= ($kec_val['id_kec'] == $data['id_kec']) ? 'selected' : '' ?>><?= $kec_val['kecamatan'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-lg-4 mb-2">
                            <label class="form-label">Kelurahan</label>
                            <select class="form-select w-100" id="kel" name="id_kelurahan">
                                <option value="">[ Pilih Kelurahan ]</option>
                                <?php foreach ($kel as $kec_val) : ?>
                                    <option value="<?= $kec_val['id_kelurahan'] ?>" <?= ($kec_val['id_kelurahan'] == $data['id_kelurahan']) ? 'selected' : '' ?>><?= $kec_val['kelurahan'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-lg-12 mb-2">
                            <label for="" class="form-label">Alamat</label>
                            <input type="text" name="alamat" class="form-control" value="<?= $data['alamat'] ?>" placeholder="Bea. Pemasangan">
                        </div>


                    </div>
                    <div class="border-top text-end pt-3 mt-4">
                        <button type="submit" id="btnSave" class="btn btn-sm btn-success">
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
    $('#editJP').submit(function(e) {
        e.preventDefault();
        var formData = $(this).serialize();
        formData += '&id_pelanggan=<?= $data['id_pelanggan'] ?>'
        $.ajax({
            type: "get",
            url: "<?= base_url('Master/Pelanggan/update') ?>",
            data: formData,
            dataType: "json",
            beforeSend: function() {
                $('#btnSave').html('<i class="fa fa-spinner"></i> Loading...');
                $('#btnSave').attr('disabled', true);

            },
            success: function(response) {
                $('#btnSave').html('<i class="fa fa-save"></i> Simpan');
                $('#btnSave').attr('disabled', false);
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
            error: function(erorr, status, xhr) {
                $('#btnSave').html('<i class="fa fa-save"></i> Simpan');
                $('#btnSave').attr('disabled', false);
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal!',
                    text: erorr.responseText,
                    showConfirmButton: false,
                    timer: 1500
                })
            }
        });

    });

    $(document).ready(function() {
        $('.form-select').select2({
                dropdownParent: $("#modal"),
                width: '100%',
            }

        );

    });

    $('#kelompok_tarif').change(function(e) {
        e.preventDefault();
        var id_jasa = $(this).find('option:selected').val();
        $.ajax({
            type: "get",
            url: "<?= base_url('Master/Pelanggan/getJenis') ?>",
            data: {
                id: id_jasa
            },
            dataType: "json",
            success: function(response) {
                $('#jenis_pelanggan').html(response.data);
            }
        });
    });

    $('#kabupaten').change(function(e) {
        e.preventDefault();
        var kabupaten = $(this).find('option:selected').val();
        $.ajax({
            type: "get",
            url: "<?= base_url('Master/Pelanggan/getKecamatan') ?>",
            data: {
                kabupaten: kabupaten
            },
            dataType: "json",
            success: function(response) {

                if (response.data != null) {
                    $('#kec').html(response.data);
                } else {
                    $('#kec').html(response.kosong);
                }
            },

        });
    });

    $('#kec').change(function(e) {
        e.preventDefault();
        var kecamatan = $(this).find('option:selected').val();

        $.ajax({
            type: "get",
            url: "<?= base_url('Master/Pelanggan/getkelurahan') ?>",
            data: {
                kecamatan: kecamatan
            },
            dataType: "json",
            success: function(response) {
                $('#kel').html(response.data);
            }
        });
    });
</script>