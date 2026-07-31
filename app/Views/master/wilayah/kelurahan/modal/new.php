<div class="modal fade" id="modal" tabindex="-1"  data-bs-backdrop="static" aria-labelledby="modalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="modalLabel">Tambah <?= $title ?></h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="form-insert">
                    <div class="row">
                        <div class="col-lg-12 mb-2">
                            <label for="" class="form-label mb-1">KABUPATEN</label>
                            <select name="id_kab" id="kabupaten" class="form-select" required>
                                <option value="">[ Pilih Kabupaten ]</option>
                            </select>
                        </div>
                        <div class="col-lg-12 mb-2">
                            <label for="" class="form-label mb-1">KECAMATAN</label>
                            <select name="id_kec" id="kecamatan" class="form-control" required>
                                <option value="">[ Pilih Kecamatan]</option>
                            </select>
                        </div>
                        <div class="col-lg-12 mb-2">
                            <label for="" class="form-label mb-1">KELURAHAN</label>
                            <input class="form-control" type="text" name="kelurahan" placeholder="Nama Kelurahan..." required>
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
            url: "<?= base_url('Master/Wilayah/kelurahanmaster') ?>",
            data: $(this).serialize(),
            dataType: "json",
            success: function(response) {
                if (response.status == 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: response.message,
                        showConfirmButton: false,
                        timer: 1500
                    }).then(() => {
                        $('#modal').modal('hide');
                        $('#table').DataTable().ajax.reload();
                    });
                }
            }
        });
    });

    $('#kabupaten').select2({
        placeholder: '[ Pilih Kabupaten ]',
        dropdownParent: $('#modal'),
        width: '100%',
        ajax: {
            url: "<?= base_url('GetData/getKabupatenMaster') ?>",
            dataType: 'json',
            delay: 250,
            data: function(params) {
                return {
                    search: params.term
                }
            },
            processResults: function(data) {
                return {
                    results: data
                }
            }
        },
    });
    $('#kecamatan').select2({
        placeholder: '[ Pilih Kecamatan ]',
        dropdownParent: $('#modal'),
        width: '100%',
        ajax: {
            url: "<?= base_url('GetData/getKecamatanMaster') ?>",
            dataType: 'json',
            delay: 250,
            data: function(params) {
                if ($('#kabupaten').val() == '') {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Perhatian',
                        text: 'Pilih Kabupaten terlebih dahulu',
                        showConfirmButton: false,
                        timer: 1500
                    });
                    return false;
                }
                return {
                    search: params.term,
                    kabupaten: $('#kabupaten').val()
                }
            },
            processResults: function(data) {
                return {
                    results: data
                }
            }
        },
    });
</script>