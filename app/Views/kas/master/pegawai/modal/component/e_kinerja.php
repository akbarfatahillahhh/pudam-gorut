<div class="row">
    <div class="col-lg-4 mb-2">
        <div class="card border border-dark">
            <div class="card-header border-bottom border-dark py-2 rounded-top">
                <h6 class="card-title fs-3 fw-semibold mb-0 text-center">FORM KINERJA</h6>
            </div>
            <div class="card-body p-4">
                <form id="formInsertJabatan">
                    <div class="row">
                        <div class="col-lg-12 mb-2">
                            <label class="form-label">Tugas Pokok <b class="text-danger">*</b></label>
                            <select name="val-tugas-pokok" class="form-select" id="val-tugas-pokok" required>
                                <option value="">[ Pilih Tugas Pokok ]</option>
                            </select>
                        </div>
                        <div class="col-lg-12 mb-2">
                            <label class="form-label">Uraian Kegiatan <b class="text-danger">*</b></label>
                            <textarea name="val-uraian-kegiatan" class="form-control" id="val-uraian-kegiatan" rows="3" required></textarea>
                        </div>
                        <div class="col-lg-6 mb-2">
                            <label class="form-label">Tanggal Kegiatan <b class="text-danger">*</b></label>
                            <input type="date" class="form-control" name="val-tgl-kegiatan" id="val-tgl-kegiatan" required>
                        </div>
                        <div class="col-lg-6 mb-2">
                            <label class="form-label">Bukti Kegiatan <b class="text-black">(Opsional)</b></label>
                            <input type="file" class="form-control" name="val-bukti-kegiatan" id="val-bukti-kegiatan">
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
        <div class="card border h-100 border-dark">
            <div class="card-header border-bottom border-dark py-2 rounded-top">
                <h6 class="card-title fs-3 fw-semibold mb-0 text-center">RIWAYAT KINERJA</h6>
            </div>

            <div class="card-body px-3 py-2">
                <div class="row mb-2">
                    <div class="col-lg-3 mb-2">
                        <label class="form-label fs-2">Periode</label>
                        <input type="date" class="form-control form-control-sm" id="tglAwal" name="tglAwal" value="<?= date('Y-m-01'); ?>">
                    </div>
                    <div class="col-lg-3 mb-2">
                        <label class="form-label fs-2">S/D</label>
                        <input type="date" class="form-control form-control-sm" id="tglAkhir" name="tglAkhir" value="<?= date('Y-m-d'); ?>">
                    </div>
                    <div class="col mb-2 mt-auto">
                        <div class="" id="place-btn-back">
                            <button class="btn btn-sm btn-outline-dark" id="btnBack">
                                <i class="fa fa-arrow-left"></i>
                                Kembali
                            </button>
                        </div>
                        <div class=" text-end" id="place-btn-export">
                            <button class="btn btn-sm btn-danger" id="btnExportPdf">
                                <i class="fa fa-file-pdf"></i>
                                PDF
                            </button>
                        </div>
                    </div>
                </div>

                <div class="table-responsive-off" id="content-place" style="min-height: 300px; max-height: 350px; overflow-y: auto;">
                    <table class="table table-sm table-hover table-bordered border-dark mb-0" id="tableKinerja">
                        <thead>
                            <tr class="text-center">
                                <th class="align-middle text-dark fs-2">#</th>
                                <th class="align-middle text-dark fs-2">Tanggal</th>
                                <th class="align-middle text-dark fs-2">Tugas Pokok</th>
                                <th class="align-middle text-dark fs-2">Uraian Kegiatan</th>
                                <th class="align-middle text-dark fs-2">Bukti Kegiatan</th>
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
        get_data_kinerja();
        getTugasPokok();
    });

    $('#formInsertJabatan').submit(function(e) {
        e.preventDefault();

        if ($('#val-tugas-pokok').val() == '') {
            Swal.fire('Gagal!', 'Tugas Pokok harus diisi', 'error');
            return false;
        }

        if ($('#val-uraian-kegiatan').val() == '') {
            Swal.fire('Gagal!', 'Uraian Kegiatan harus diisi', 'error');
            return false;
        }

        if ($('#val-tgl-kegiatan').val() == '') {
            Swal.fire('Gagal!', 'Tanggal Kegiatan harus diisi', 'error');
            return false;
        }

        var data = new FormData(this);
        data.append('id_pegawai', '<?= $pegawai_id ?>');

        $.ajax({
            type: "post",
            url: "<?= base_url('Master/Pegawai/saveKinerja') ?>",
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
                Swal.fire('Berhasil!', response.msg, 'success');
                $('#formInsertJabatan')[0].reset();
                setTimeout(function() {
                    get_data_kinerja();
                }, 2000);
            }
        });
    });

    async function getTugasPokok() {
        $.ajax({
            type: "GET",
            url: "<?= base_url('GetData/getTugasPokok') ?>",
            dataType: "json",
            success: function(response) {
                if (Array.isArray(response)) {
                    $.each(response, function(indexInArray, items) {
                        $('#val-tugas-pokok').append(`<option value="${items.id}">${items.text_after}</option>`);
                    });
                    $('#val-tugas-pokok').select2({
                        dropdownParent: $("#modal"),
                        width: '100%',
                    });
                }
            }
        });
    }

    function get_data_kinerja() {
        $.ajax({
            type: "get",
            url: "<?= base_url('Master/Pegawai/get_data_kinerja') ?>",
            data: {
                id_pegawai: '<?= $pegawai_id ?>',
                tglAwal: $('#tglAwal').val(),
                tglAkhir: $('#tglAkhir').val()
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
                $('#tableKinerja tbody').html(response.html);
            }
        });
    }

    function lihatKinerja(img) {
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

        if (img == '' || img == null) {
            img = '<?= base_url('perumda/blank.jpg') ?>';
        }

        Swal.fire({
            imageWidth: '100%',
            imageHeight: '100%',
            imageUrl: img,
        });


    }

    function hapusKinerja(id_kinerja) {
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
                    url: "<?= base_url('Master/Pegawai/delete_kinerja') ?>",
                    data: {
                        id_kinerja: id_kinerja,
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
                        setTimeout(function() {
                            get_data_kinerja();
                        }, 2000);
                    }
                });
            }
        })
    }

    $('#btnExportPdf').click(function(e) {
        e.preventDefault();
        $('#place-btn-back').show();
        $('#place-btn-export').hide();

        var tglAwal = $('#tglAwal').val();
        var tglAkhir = $('#tglAkhir').val();

        $('#content-place').prepend(`<iframe id="iframe" src="<?= base_url('Master/Pegawai/pdf_kinerja') ?>/<?= $pegawai_id ?>/${tglAwal}/${tglAkhir}" width="100%" height="500" frameborder="0"></iframe>`);
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