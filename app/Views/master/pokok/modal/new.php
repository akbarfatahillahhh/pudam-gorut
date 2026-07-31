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
                            <label for="" class="form-label mb-1">Jabatan</label>
                            <select name="JABATAN_ID" id="JABATAN" class="form-select">
                                <option value="">[ Pilih Jabatan ]</option>
                            </select>
                        </div>
                        <div class="col-lg-12 mb-2">
                            <label for="" class="form-label mb-1">Tugas Pokok</label>
                            <textarea name="TUGAS_POKOK" id="" rows="3" class="form-control"></textarea>
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
    $(document).ready(function() {
        getDataPokok();
    });

    $('#JABATAN').select2({
        placeholder: '[ Pilih Jabatan ]',
        dropdownParent: $("#modal"),
        width: '100%',
        class: 'form-control',
        ajax: {
            url: '/GetData/getJabatan',
            type: 'GET',
            dataType: 'json',
            data: function(params) {
                return {
                    search: params.term,
                };
            },
            processResults: function(data) {
                return {
                    results: data
                }
            }
        }
    });

    $('#form-insert').submit(function(e) {
        e.preventDefault();
        $.ajax({
            type: "POST",
            url: "<?= base_url('Master/Pokok/master') ?>",
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
                        getDataPokok();
                    });
                }
            }
        });
    });
</script>