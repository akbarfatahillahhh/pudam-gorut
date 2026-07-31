<div class="modal fade" id="modal" tabindex="-1"   data-bs-backdrop="static"  aria-labelledby="modalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="modalLabel">Edit <?= $title ?></h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="form-update">
                    <input type="hidden" name="id_session" id="id_session" value="<?= $data['id_session'] ?>">
                    <input type="hidden" nama="mobile_akses" id="mobile_akses" placeholder="mobile_akses">
                    <input type="hidden" nama="id_kec" id="id_kec" placeholder="id_kec">

                    <div class="row">
                        <div class="col-lg-4 mb-2">
                            <label for="" class="form-label">Pegawai</label>
                            <input type="text" class="form-control" placeholder="Pegawai..." value="<?= $data['nama_lengkap'] ?>" readonly>
                        </div>

                        <div class="col-lg-4 mb-2">
                            <label for="" class="form-label">Username</label>
                            <input type="text" class="form-control" name="username" placeholder="Username..." value="<?= $data['username'] ?>" autocomplete="off" required>
                        </div>

                        <div class="col-lg-4 mb-2">
                            <label for="" class="form-label">Level</label>
                            <select class="form-select" name="level" id="level">
                                <option value="" selected>[ Pilih Level ]</option>
                                <option value="admin" <?= ($data['level'] == 'admin') ? 'selected' : '' ?>>ADMINISTRATOR</option>
                                <option value="direktur" <?= ($data['level'] == 'direktur') ? 'selected' : '' ?>>DIREKTUR</option>
                                <option value="wakil1" <?= ($data['level'] == 'wakil1') ? 'selected' : '' ?>>WAKIL DIREKTUR 1</option>
                                <option value="wakil2" <?= ($data['level'] == 'wakil2') ? 'selected' : '' ?>>WAKIL DIREKTUR 2</option>
                                <option value="kabag_akun" <?= ($data['level'] == 'kabag_akun') ? 'selected' : '' ?>>KABAG AKUNTANSI</option>
                                <option value="bendahara" <?= ($data['level'] == 'bendahara') ? 'selected' : '' ?>>BENDAHARA</option>
                                <option value="loket" <?= ($data['level'] == 'loket') ? 'selected' : '' ?>>LOKET</option>
                                <option value="mobileloket" <?= ($data['level'] == 'mobileloket') ? 'selected' : '' ?>>MOBILE LOKET</option>
                                <option value="user" <?= ($data['level'] == 'user') ? 'selected' : '' ?>>ADMINISTRASI</option>
                            </select>
                        </div>

                        <div class="col-lg-4 mb-2">
                            <label for="" class="form-label">Username</label>
                            <input type="text" class="form-control" name="username" placeholder="Username..." value="<?= $data['username'] ?>" autocomplete="off" required>
                        </div>

                        <div class="col-lg-4 mb-2">
                            <label for="" class="form-label">Email</label>
                            <input type="text" class="form-control" name="email" placeholder="Email..." value="<?= $data['email'] ?>" required>
                        </div>
                        <div class="col-lg-4 mb-2">
                            <label for="" class="form-label">No. Telp</label>
                            <input type="text" class="form-control" name="no_telp" placeholder="No. Telp..." value="<?= $data['no_telp'] ?>" required>
                        </div>

                        <div class="col-lg-4 mb-2 select-mobile-akses">
                            <label for="" class="form-label">Jenis Petugas</label>
                            <select class="form-select" name="akses" id="akses">
                                <option value="" selected>[ Pilih Jenis Petugas ]</option>
                                <option value="1" <?= ($data['mobile_akses'] == '1') ? 'selected' : '' ?>>Petugas Penagih</option>
                                <option value="2" <?= ($data['mobile_akses'] == '2') ? 'selected' : '' ?>>Petugas Cek Meter</option>
                            </select>
                        </div>

                        <div class="col-lg-4 mb-2 select-unit">
                            <label for="" class="form-label">Unit</label>
                            <select class="form-select" name="unit" id="unit">
                                <option value="" selected>[ Pilih Unit ]</option>
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
        $('.select-mobile-akses, .select-unit').hide();

        if ('<?= $data['level'] ?>' == 'mobileloket') {
            $('.select-mobile-akses').show();
            $('.select-unit').show();
        }

        $('#level, #akses').select2({
            width: '100%',
            dropdownParent: $("#modal")
        });
    });

    $('#form-update').submit(function(e) {
        e.preventDefault();
        $.ajax({
            type: "post",
            url: "<?= base_url('Master/Akun/insert') ?>",
            data: $(this).serialize(),
            dataType: "json",
            success: function(response) {
                Swal.fire('Berhasil', response.msg, 'success');
                $('#table').DataTable().ajax.reload();
                $('#modal').modal('hide');
            }
        });
    });

    $('#level').change(function(e) {
        e.preventDefault();
        var level = $(this).val();

        if (level == 'mobileloket') {
            $('.select-mobile-akses').show();
            $('.select-unit').show();

            $('#mobile_akses').val('');
            $('#akses').val($('#akses option:first-of-type').val()).trigger('change');

            get_select_unit();
        } else {
            $('.select-mobile-akses').hide();
            $('.select-unit').hide();

            $('#mobile_akses').val(4);
            $('#akses').val($('#akses option:first-of-type').val()).trigger('change');

            $('#id_kec').val('');

            $('#unit').val($('#unit option:first-of-type').val()).trigger('change');
        }
    });

    $('#akses').change(function(e) {
        e.preventDefault();
        var akses = $(this).val();
        if ($('#level').val() === 'mobileloket') {
            $('#mobile_akses').val(akses);
        } else {
            $('#mobile_akses').val(4);
        }
    });

    $('#unit').change(function(e) {
        e.preventDefault();
        var unit = $(this).val();
        if ($('#level').val() === 'mobileloket') {
            $('#id_kec').val(unit);
        } else {
            $('#id_kec').val('');
        }
    });

    function get_select_unit() {
        $.ajax({
            type: "get",
            url: "<?= base_url('GetData/getUnit') ?>",
            dataType: "json",
            success: function(response) {
                var html = '';
                html += '<option value="" selected>[ Pilih Unit ]</option>';
                $.each(response, function(key, value) {
                    html += '<option value="' + value.id + '">' + value.text + '</option>';
                });
                $('#unit').html(html);
                $('#unit').select2({
                    width: '100%',
                    dropdownParent: $('#modal')
                });
            }
        });
    }
</script>