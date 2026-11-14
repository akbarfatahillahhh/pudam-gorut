<div class="modal fade" id="modal" tabindex="-1" data-bs-backdrop="static" aria-labelledby="modalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-bottom">
                <h1 class="modal-title fs-5" id="modalLabel">Tambah <?= $title ?></h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="form-insert">
                    <div class="row">
                        <div class="col-lg-5 mb-2">
                            <label for="" class="form-label">Grup Perkiraan</label>
                            <select name="ID_GROP_PERKIRAAN" id="group-perkiraan" class="form-control">
                                <option value="">[ Pilih Grup Perkiraan ]</option>
                            </select>
                        </div>
                        <div class="col-lg-7 mb-2">
                            <label for="" class="form-label">Perkiraan</label>
                            <input type="text" class="form-control" name="NAMA_PERKIRAAN" placeholder="Perkiraan">
                        </div>

                        <div class="col-lg-4 mb-2">
                            <label for="" class="form-label">Nomor Perkiraan</label>
                            <input type="text" class="form-control" name="NOMOR" placeholder="Nomor">
                        </div>
                        <div class="col-lg-5 mb-2">
                            <label for="" class="form-label">Normal</label>
                            <select name="NORMAL" id="" class="form-control">
                                <option value="">[ Pilih Normal ]</option>
                                <option value="Debet">DEBET</option>
                                <option value="Kredit">KREDIT</option>
                            </select>
                        </div>
                        <div class="col-lg-3 mb-2">
                            <label for="" class="form-label">Status</label>
                            <select name="STATUS" id="" class="form-control">
                                <option value="">[ Pilih Status ]</option>
                                <option value="Biaya">BIAYA</option>
                                <option value="Pendapatan">PENDAPATAN</option>
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
    $(document).ready(function() {
        getGroupPerkiraan();
    });

    async function getGroupPerkiraan() {
        $.ajax({
            type: "GET",
            url: "<?= base_url('GetData/getPerkiraanGroup') ?>",
            dataType: "json",
            success: function(response) {
                if (Array.isArray(response)) {
                    $.each(response, function(indexInArray, items) {
                        $('#group-perkiraan').append(`<option value="${items.id}">${items.text}</option>`);
                    });
                    $('#group-perkiraan').select2({
                        dropdownParent: $("#modal"),
                        width: '100%',
                    });
                }
            }
        });
    }
    $('#form-insert').submit(function(e) {
        e.preventDefault();
        $.ajax({
            type: "POST",
            url: "<?= base_url('Master/Perkiraan/master') ?>",
            data: $(this).serialize(),
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
                if (response.status == 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: response.message,
                        showConfirmButton: false,
                        timer: 1500
                    });
                }
            }
        });
    });
</script>