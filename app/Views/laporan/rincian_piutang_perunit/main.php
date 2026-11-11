<?= $this->extend('layouts/default') ?>
<?= $this->section('content') ?>

<div class="card border border-dark">
    <div class="card-body p-4">
        <h5 class="card-title fw-semibold mb-4">Rekapan Rekening Ditagih (DRD Air) Perunit</h5>
        <div>
            <div class="row">
                <div class="col-lg-3 mb-2">
                    <label for="" class="form-label fw-semibold">Unit</label>
                    <select name="" id="unit" class="form-select">
                        <option value="" selected>[ Pilih Unit ]</option>
                    </select>
                </div>
                <div class="col-lg-2 mb-2">
                    <label for="" class="form-label fw-semibold">Periode</label>
                    <select name="" id="dateStart" class="form-select">
                        <option value="">[ Pilih Tahun ]</option>
                        <?php for ($i = date('Y'); $i >= 2010; $i--): ?>
                            <option value="<?= $i ?>" <?= ($i == date('Y', strtotime('-1 year'))) ? 'selected' : '' ?>><?= $i ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="col-lg-2 mb-2">
                    <label for="" class="form-label fw-semibold">S/D Tahun</label>
                    <select name="" id="dateEnd" class="form-select">
                        <option value="">[ Pilih Tahun ]</option>
                        <?php for ($i = date('Y'); $i >= 2010; $i--): ?>
                            <option value="<?= $i ?>" <?= ($i == date('Y')) ? 'selected' : '' ?>><?= $i ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="col-lg-3 mb-2">
                    <label for="" class="form-label fw-semibold">Jenis Laporan</label>
                    <select name="" id="statusLaporan" class="form-select">
                        <option value="all" selected>Keseluruhan</option>
                        <option value="0">Belum Bayar</option>
                        <option value="1">Sudah Bayar</option>
                    </select>
                </div>
            </div>

            <div class="mt-3">
                <div class="mb-2">
                    <button class="btn btn-sm btn-danger" id="exportPdf">
                        <i class="fa fa-file-pdf"></i>
                        PDF
                    </button>
                    <button class="btn btn-sm btn-success" id="exportExcel">
                        <i class="fa fa-file-excel"></i>
                        Excel
                    </button>
                </div>
                <div class="py-2 border border-dark">
                    <div class="border-bottom border-dark">
                        <table class="table table-sm table-borderless">
                            <tr>
                                <td class="text-end align-middle" width="15%">
                                    <img src="<?= base_url() ?>/logo/perumda-gorut.png" class="me-2" width="100">
                                </td>
                                <td class="align-middle">
                                    <div class="fs-2 text-black fw-semibold">PERUSAHAAN UMUM DAERAH AIR MINUM</div>
                                    <div class="fs-2 text-black fw-semibold">TIRTA GERBANG EMAS</div>
                                    <div class="fs-2 text-black fw-semibold">KABUPATEN GORONTALO UTARA</div>
                                    <div class="fs-2 text-black fw-semibold">Jl. Kasmat Lahay Titidu - Kwandang No. 17, Telp. (0442) 310077, Kode Pos 96252</div>
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div class="px-3 py-3" id="pagePlace">
                        <div class="text-center py-5">
                            <i class="fas fa-file-pdf text-dark h1"></i>
                            <h5 class="text-dark">PREVIEW LAPORAN</h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal-place"></div>

<?= $this->endSection() ?>

<?= $this->section('script') ?>


<script>
    $(document).ready(function() {
        get_select_unit();
        loadPage('perUnit');

        $('#unit, #startDate, #endDate, #statusLaporan').change(function(e) {
            e.preventDefault();
            loadPage('perUnit');
        });
    });

    $('#exportPdf').click(function(e) {
        e.preventDefault();
        var dateStart = $('#dateStart').val();
        var dateEnd = $('#dateEnd').val();
        var statusLaporan = $('#statusLaporan').val();
        var unit = $('#unit').val();

        $.ajax({
            type: "post",
            url: "<?= base_url('Laporan/RincianPiutangPerunit/modal_export_pdf') ?>",
            data: {
                dateStart: dateStart,
                dateEnd: dateEnd,
                statusLaporan: statusLaporan,
                unit: unit
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
                $('.modal-place').html("").html(response.html);
                $('.modal-place #modal').modal('show');
            }
        });
    });

    function get_select_unit() {
        $.ajax({
            type: "get",
            url: "<?= base_url('GetData/getUnit') ?>",
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
                var html = '';
                html += '<option value="" selected>[ Pilih Unit ]</option>';
                $.each(response, function(key, value) {
                    html += '<option value="' + value.id + '">' + value.text + '</option>';
                });
                $('#unit').html(html);
                $('#unit').select2({
                    width: '100%'
                });
            }
        });
    }

    function loadPage(page) {
        var unit = $('#unit').val();
        if (!unit) {
            Swal.fire('Peringatan', 'Unit harus dipilih', 'warning');
            return false;
        }
        $.ajax({
            type: "get",
            url: "<?= base_url('Laporan/RincianPiutangPerunit/loadPage') ?>/" + page,
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
                $('#pagePlace').html(response.page);
            }
        });
    }
</script>

<?= $this->endSection() ?>