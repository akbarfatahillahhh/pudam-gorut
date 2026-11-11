<div class="modal fade" id="modal" tabindex="-1" data-bs-backdrop="static" aria-labelledby="modalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-bottom">
                <h1 class="modal-title fs-5" id="modalLabel">Tambah <?= $title ?></h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="form-insert">
                    <input type="hidden" nama="nama_lengkap" id="nama_lengkap" placeholder="nama_lengkap">
                    <input type="hidden" nama="nik" id="nik" placeholder="nik">
                    <input type="hidden" nama="tempat_lahir" id="tempat_lahir" placeholder="tempat_lahir">
                    <input type="hidden" nama="tgl_lahir" id="tgl_lahir" placeholder="tgl_lahir">
                    <input type="hidden" nama="jenis_kelamin" id="jenis_kelamin" placeholder="jenis_kelamin">
                    <input type="hidden" nama="alamat" id="alamat" placeholder="alamat">
                    <input type="hidden" nama="id_pegawai" id="id_pegawai" placeholder="id_pegawai">
                    <input type="hidden" nama="mobile_akses" id="mobile_akses" placeholder="mobile_akses">
                    <input type="hidden" nama="id_kec" id="id_kec" placeholder="id_kec">
                    <div class="row">
                        <div class="col-lg-4 mb-2">
                            <label for="" class="form-label">Pegawai</label>
                            <select name="" id="pegawai" class="form-select" required>
                                <option value="" selected>[ Pilih Pegawai ]</option>
                            </select>
                        </div>
                        <div class="col-lg-4 mb-2">
                            <label for="" class="form-label">Username</label>
                            <input type="text" class="form-control" name="username" placeholder="Username..." required>
                        </div>
                        <div class="col-lg-4 mb-2">
                            <label for="" class="form-label">Password</label>
                            <input type="text" class="form-control" name="password" placeholder="Password..." required>
                        </div>

                        <div class="col-lg-4 mb-2">
                            <label for="" class="form-label">Level</label>
                            <select class="form-select" name="level" id="level">
                                <option value="" selected>[ Pilih Level ]</option>
                                <option value="admin">ADMINISTRATOR</option>
                                <option value="direktur">DIREKTUR</option>
                                <option value="wakil1">WAKIL DIREKTUR 1</option>
                                <option value="wakil2">WAKIL DIREKTUR 2</option>
                                <option value="kabag_akun">KABAG AKUNTANSI</option>
                                <option value="bendahara">BENDAHARA</option>
                                <option value="loket">LOKET</option>
                                <option value="mobileloket">MOBILE LOKET</option>
                                <option value="user">ADMINISTRASI</option>
                            </select>
                        </div>
                        <div class="col-lg-4 mb-2">
                            <label for="" class="form-label">Email</label>
                            <input type="text" class="form-control" name="email" placeholder="Email..." required>
                        </div>
                        <div class="col-lg-4 mb-2">
                            <label for="" class="form-label">No. Telp</label>
                            <input type="text" class="form-control" name="no_telp" placeholder="No. Telp..." required>
                        </div>

                        <div class="col-lg-4 mb-2 select-mobile-akses">
                            <label for="" class="form-label">Jenis Petugas</label>
                            <select class="form-select" name="akses" id="akses">
                                <option value="" selected>[ Pilih Jenis Petugas ]</option>
                                <option value="1">Petugas Penagih</option>
                                <option value="2">Petugas Cek Meter</option>
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
        get_data_pegawai();
        $('.select-mobile-akses, .select-unit').hide();
        $('#level, #akses').select2({
            width: '100%',
            dropdownParent: $("#modal")
        });
    });

    $('#form-insert').submit(function(e) {
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

    function get_data_pegawai() {
        $.ajax({
            type: "get",
            url: "<?= base_url('GetData/getPegawai') ?>",
            dataType: "json",
            success: function(response) {
                var html = '';
                html += '<option value="" selected>[ Pilih Pegawai ]</option>';
                $.each(response, function(key, value) {
                    html += '<option value="' + value.id + '">' + value.text + '</option>';
                });
                $('#pegawai').html(html);
                $('#pegawai').select2({
                    dropdownParent: $('#modal'),
                    width: '100%'
                });
            }
        });
    }

    $('#pegawai').change(function(e) {
        e.preventDefault();
        var id_pegawai = $(this).val();
        $.ajax({
            type: "post",
            url: "<?= base_url('Master/Akun/getDataPegawai') ?>",
            data: {
                id_pegawai: id_pegawai
            },
            dataType: "json",
            success: function(response) {
                $('#nama_lengkap').val(response.nama);
                $('#nik').val(response.nik);
                $('#id_pegawai').val(response.id_pegawai);
                $('#tempat_lahir').val(response.tempat_lahir);
                $('#tgl_lahir').val(response.tanggal_lahir);
                $('#jenis_kelamin').val((response.kelamin == 'L') ? 'Laki-laki' : 'Perempuan');
                $('#alamat').val(response.alamat);
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
            get_select_unit();
        } else {
            $('.select-mobile-akses').hide();
            $('.select-unit').hide();
            $('#mobile_akses').val(4);
            $('#id_kec').val('');
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