<div class="row">
    <div class="col-lg-4 mb-2">
        <div class="card border border-dark">
            <div class="card-header border-bottom border-dark py-2 rounded-top">
                <h6 class="card-title fs-3 fw-semibold mb-0 text-center">FORM PENDIDIKAN</h6>
            </div>
            <div class="card-body p-4">
                <form id="formInsertPendidikan">
                    <div class="row">
                        <div class="col-12 mb-2">
                            <label class="form-label">Tempat Pendidikan</label>
                            <input type="text" class="form-control" name="val-tempat_pendidikan" id="val-tempat_pendidikan" placeholder="Tempat Pendidikan..." required autocomplete="off">
                        </div>
                        <div class="col-lg-5 mb-2">
                            <label class="form-label">Tahun Kelulusan</label>
                            <input type="year" class="form-control" name="val-tahun_lulus" id="val-tahun_lulus" placeholder="Tahun Kelulusan..." required>
                        </div>
                        <div class="col-lg-7 mb-2">
                            <label class="form-label">Jenjang Pendidikan</label>
                            <input type="text" class="form-control" name="val-pendidikan" id="val-pendidikan" placeholder="Jenjang Pendidikan..." required>
                        </div>
                        <div class="col-12 mt-2">
                            <button class="btn btn-primary w-100" name="btnSimpanPendidikan" id="btnSimpanPendidikan">
                                <i class="fa fa-save"></i>
                                Simpan
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-8 mb-2">
        <div class="card border border-dark">
            <div class="card-header border-bottom border-dark py-2 rounded-top">
                <h6 class="card-title fs-3 fw-semibold mb-0 text-center">RIWAYAT PENDIDIKAN</h6>
            </div>
            <div class="card-body p-2">
                <div class="mb-2" id="place-btn-back">
                    <button class="btn btn-sm btn-outline-dark" id="btnBack">
                        <i class="fa fa-arrow-left"></i>
                        Kembali
                    </button>
                </div>
                <div class="mb-2 text-end" id="place-btn-export">
                    <button class="btn btn-sm btn-danger" id="btnExportPdf">
                        <i class="fa fa-file-pdf"></i>
                        PDF
                    </button>
                </div>
                <div class="table-responsive-off" id="content-place" style="min-height: 300px; max-height: 350px; overflow-y: auto;">
                    <table class="table table-sm table-hover table-bordered border-dark mb-0" id="tablePendidikan">
                        <thead>
                            <tr class="text-center">
                                <th class="align-middle text-dark fs-2">#</th>
                                <th class="align-middle text-dark fs-2">Jenjang Pendidikan</th>
                                <th class="align-middle text-dark fs-2">Tempat Pendidikan</th>
                                <th class="align-middle text-dark fs-2">Tahun Kelulusan</th>
                                <th class="align-middle text-dark fs-2">#</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="align-middle text-black fs-2 text-center" colspan="5">Tidak ada data</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $(function() {
        $('#place-btn-back').hide();
        get_data_pendidikan()
    });

    $('#formInsertPendidikan').submit(function(e) {
        e.preventDefault();

        if ($('#val-tempat_pendidikan').val() == '') {
            Swal.fire('Gagal!', 'Tempat Pendidikan harus diisi', 'error');
            return false;
        }

        var tahun_lulus = $('#val-tahun_lulus').val();
        if (tahun_lulus == '') {
            Swal.fire('Gagal!', 'Tahun Kelulusan harus diisi', 'error');
            return false;
        } else if (tahun_lulus < 1900 || tahun_lulus > 2999) {
            Swal.fire('Gagal!', 'Tahun Kelulusan harus berupa tahun yang valid', 'error');
            return false;
        }

        if ($('#val-pendidikan').val() == '') {
            Swal.fire('Gagal!', 'Jenjang Pendidikan harus diisi', 'error');
            return false;
        }

        var data = new FormData(this);
        data.append('id_pegawai', '<?= $pegawai_id ?>');
        $.ajax({
            type: "post",
            url: "<?= base_url('Master/Pegawai/savePendidikan') ?>",
            data: data,
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
                get_data_pendidikan();
                Swal.fire('Berhasil!', response.msg, 'success');
            }
        });
    });

    function get_data_pendidikan() {
        $.ajax({
            type: "get",
            url: "<?= base_url('Master/Pegawai/get_data_pendidikan') ?>",
            data: {
                id_pegawai: '<?= $pegawai_id ?>'
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
                $('#tablePendidikan tbody').html(response.html);
            }
        });
    }

    function hapusPendidikan(id_pendidikan) {
        Swal.fire({
            icon: 'warning',
            title: 'Apakah anda yakin?',
            text: "Data akan dihapus",
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Hapus',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    type: "post",
                    url: "<?= base_url('Master/Pegawai/delete_pendidikan') ?>",
                    data: {
                        id_pendidikan: id_pendidikan,
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
                        Swal.fire('Berhasil!', response.msg, 'success');
                        get_data_pendidikan();
                    }
                });
            }
        })
    }

    $('#btnExportPdf').click(function(e) {
        e.preventDefault();
        $('#place-btn-back').show();
        $('#place-btn-export').hide();

        $('#content-place').prepend(`<iframe id="iframe" src="<?= base_url('Master/Pegawai/pdf_pendidikan') ?>/<?= $pegawai_id ?>" width="100%" height="500" frameborder="0"></iframe>`);
        $('#tablePendidikan').hide();
    });

    $('#btnBack').click(function(e) {
        e.preventDefault();
        $('#place-btn-back').hide();
        $('#place-btn-export').show();
        $('#iframe').remove();
        $('#tablePendidikan').show();
    });
</script>