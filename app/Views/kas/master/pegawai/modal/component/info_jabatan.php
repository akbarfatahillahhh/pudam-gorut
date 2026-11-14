<div class="row">
    <div class="col-lg-4 mb-2">
        <div class="card border border-dark">
            <div class="card-header border-bottom border-dark py-2 rounded-top">
                <h6 class="card-title fs-3 fw-semibold mb-0 text-center">FORM JABATAN</h6>
            </div>
            <div class="card-body p-4">
                <form id="formInsertJabatan">
                    <div class="row">
                        <div class="col-12 mb-2">
                            <label class="form-label">No. SK</label>
                            <input type="text" class="form-control" name="val-no_sk" id="val-no_sk" placeholder="No. SK..." required autocomplete="off">
                        </div>
                        <div class="col-12 mb-2">
                            <label class="form-label">Jabatan</label>
                            <select class="form-select" name="val-jabatan" id="val-jabatan" required>
                                <option value="">[ Pilih Jabatan ]</option>
                                <?php if ($jabatan) : ?>
                                    <?php foreach ($jabatan as $j) : ?>
                                        <option value="<?= $j['ID'] ?>"><?= $j['JABATAN'] ?></option>
                                    <?php endforeach; ?>
                                <?php else : ?>
                                    <option value="">[ Jabatan Tidak Ditemukan ]</option>
                                <?php endif ?>
                            </select>
                        </div>
                        <div class="col-lg-6 mb-2">
                            <label class="form-label">Periode</label>
                            <input type="date" class="form-control" name="val-tgl_mulai" id="val-tgl_mulai" required>
                        </div>
                        <div class="col-lg-6 mb-2">
                            <label class="form-label">S/D</label>
                            <input type="date" class="form-control" name="val-tgl_selesai" id="val-tgl_selesai" required>
                        </div>
                        <div class="col-12 mb-2">
                            <label class="form-label">Status Pegawai</label>
                            <select class="form-select" name="val-status_pegawai" id="val-status_pegawai" required>
                                <option value="">[ Pilih Status Pegawai ]</option>
                                <option value="Tetap">Tetap</option>
                                <option value="Kontrak">Kontrak</option>
                            </select>
                        </div>
                        <div class="col-12 mt-2">
                            <button class="btn btn-primary w-100" name="btnSimpanJabatan" id="btnSimpanJabatan">
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
                <h6 class="card-title fs-3 fw-semibold mb-0 text-center">RIWAYAT JABATAN</h6>
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
                    <table class="table table-sm table-hover table-bordered border-dark mb-0" id="tableJabatan">
                        <thead>
                            <tr class="text-center">
                                <th class="align-middle text-dark fs-2">#</th>
                                <th class="align-middle text-dark fs-2">No. SK</th>
                                <th class="align-middle text-dark fs-2">Nama Jabatan</th>
                                <th class="align-middle text-dark fs-2">Periode</th>
                                <th class="align-middle text-dark fs-2">S/D</th>
                                <th class="align-middle text-dark fs-2">Status Pegawai</th>
                                <th class="align-middle text-dark fs-2">Status Jabatan</th>
                                <th class="align-middle text-dark fs-2">#</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="align-middle text-black fs-2 text-center" colspan="8">Tidak ada data</td>
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
        $('#val-jabatan').select2({
            width: '100%',
            dropdownParent: $("#modal")
        });
        get_data_jabatan()
    });

    $('#formInsertJabatan').submit(function(e) {
        e.preventDefault();

        if ($('#val-no_sk').val() == '') {
            Swal.fire('Gagal!', 'No. SK harus diisi', 'error');
            return false;
        }

        if ($('#val-jabatan').val() == '') {
            Swal.fire('Gagal!', 'Jabatan harus diisi', 'error');
            return false;
        }

        if ($('#val-tgl_mulai').val() == '') {
            Swal.fire('Gagal!', 'Periode harus diisi', 'error');
            return false;
        }

        if ($('#val-tgl_selesai').val() == '') {
            Swal.fire('Gagal!', 'Periode harus diisi', 'error');
            return false;
        }

        if ($('#val-status_pegawai').val() == '') {
            Swal.fire('Gagal!', 'Status Pegawai harus diisi', 'error');
            return false;
        }

        var data = new FormData(this);
        data.append('id_pegawai', '<?= $pegawai_id ?>');
        $.ajax({
            type: "post",
            url: "<?= base_url('Master/Pegawai/saveJabatan') ?>",
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
                get_data_jabatan();
                Swal.fire('Berhasil!', response.msg, 'success');
            }
        });
    });

    function get_data_jabatan() {
        $.ajax({
            type: "get",
            url: "<?= base_url('Master/Pegawai/get_data_jabatan') ?>",
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
                $('#tableJabatan tbody').html(response.html);
            }
        });
    }

    function hapusJabatan(id_jabatan) {
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
                    url: "<?= base_url('Master/Pegawai/delete_jabatan') ?>",
                    data: {
                        id_jabatan: id_jabatan,
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
                        get_data_jabatan();
                    }
                });
            }
        })
    }

    $('#btnExportPdf').click(function(e) {
        e.preventDefault();
        $('#place-btn-back').show();
        $('#place-btn-export').hide();

        $('#content-place').prepend(`<iframe id="iframe" src="<?= base_url('Master/Pegawai/pdf_jabatan') ?>/<?= $pegawai_id ?>" width="100%" height="500" frameborder="0"></iframe>`);
        $('#tableJabatan').hide();
    });

    $('#btnBack').click(function(e) {
        e.preventDefault();
        $('#place-btn-back').hide();
        $('#place-btn-export').show();
        $('#iframe').remove();
        $('#tableJabatan').show();
    });
</script>