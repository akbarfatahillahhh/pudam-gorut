<?= $this->extend('layouts/default') ?>
<?= $this->section('content') ?>

<div class="card border border-dark">
    <div class="card-body p-4">
        <div class="row">
            <div class="col-lg-9">
                <h5 class="card-title fw-semibold mb-4">Rekapan Rekening Ditagih (DRD Air) Perunit</h5>
            </div>
            <div class="col-lg-3">
                <select name="" id="jenisLaporan" class="form-select">
                    <option value="perUnit" selected>Per Unit</option>
                    <option value="perKel">Per Kel/Desa</option>
                </select>
            </div>
        </div>
        <div>
            <div class="row">
                <div class="col-lg-2 mb-2">
                    <label for="" class="form-label fw-semibold">Periode</label>
                    <input type="date" class="form-control" name="" id="dateStart" value="<?= date('Y-m-01') ?>">
                </div>
                <div class="col-lg-2 mb-2">
                    <label for="" class="form-label fw-semibold">S/D</label>
                    <input type="date" class="form-control" name="" id="dateEnd" value="<?= date('Y-m-d') ?>">
                </div>
                <div class="col-lg-3 mb-2">
                    <label for="" class="form-label fw-semibold">Jenis Rekening</label>
                    <select name="" id="statusLaporan" class="form-select">
                        <option value="all" selected>Keseluruhan</option>
                        <option value="0">Belum Bayar</option>
                        <option value="1">Sudah Bayar</option>
                    </select>
                </div>
                <div class="col-lg-3 mb-2 filter-per-unit">
                    <label for="" class="form-label fw-semibold">Unit</label>
                    <select name="" id="unit" class="form-select">
                        <option value="all" selected>Semua Unit</option>
                    </select>
                </div>
                <div class="col-lg-3 mb-2 filter-per-kel">
                    <label for="" class="form-label fw-semibold">Kel/Desa</label>
                    <select name="" id="kel" class="form-select">
                        <option value="all" selected>Semua Kel/Desa</option>
                    </select>
                </div>
                <div class="col-lg-1 mb-2 mt-auto sr-only">
                    <button type="button" class="btn btn-primary w-100" id="btnSearch">
                        Lihat
                    </button>
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
        $('.filter-per-kel').hide();
        loadPage('perUnit');
    });

    $('#exportPdf').click(function(e) {
        e.preventDefault();
        var dateStart = $('#dateStart').val();
        var dateEnd = $('#dateEnd').val();
        var jenisLaporan = $('#jenisLaporan').val();
        var statusLaporan = $('#statusLaporan').val();
        var unit = $('.filter-per-unit #unit').val();
        var kel = $('.filter-per-kel #kel').val();

        $.ajax({
            type: "post",
            url: "<?= base_url('Laporan/RekapDrdAir/modal_export_pdf') ?>",
            data: {
                dateStart: dateStart,
                dateEnd: dateEnd,
                jenisLaporan: jenisLaporan,
                statusLaporan: statusLaporan,
                unit: unit,
                kel: kel
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

    $('#jenisLaporan').change(function(e) {
        e.preventDefault();
        let jenisLaporan = $(this).val();
        loadPage(jenisLaporan);
        if (jenisLaporan == 'perUnit') {
            $('.filter-per-kel').hide();
            $('.filter-per-unit').show();
        } else if (jenisLaporan == 'perKel') {
            $('.filter-per-unit').hide();
            $('.filter-per-kel').show();
        }
    });

    function loadPage(page) {
        $.ajax({
            type: "get",
            url: "<?= base_url('Laporan/RekapDrdAir/loadPage') ?>/" + page,
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