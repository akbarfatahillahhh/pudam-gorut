<?= $this->extend('layouts/default') ?>
<?= $this->section('content') ?>

<div class="card border border-dark">
    <div class="card-body p-4">
        <div class="row">
            <div class="col-6">
                <h5 class="card-title fw-semibold mb-4"><?= $title ?></h5>
            </div>
            <div class="col-6 text-end">
                <button type="button" class="btn btn-sm btn-warning" id="btnAdd">
                    <i class="fa fa-plus-circle"></i>
                    Absensi Hari Ini
                </button>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-2 mb-2">
                <label class="form-label">Bulan</label>
                <select class="form-select" id="filterBulan">
                    <?php foreach ($bulanNama as $key => $value) : ?>
                        <option value="<?= $key ?>" <?= $key == date('m') ? 'selected' : '' ?>>
                            <?= $value ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-lg-2 mb-2">
                <label class="form-label">Tahun</label>
                <select class="form-select" id="filterTahun">
                    <option value="">[ Pilih Tahun]</option>
                    <?php for ($i = date('Y') - 5; $i <= date('Y'); $i++) : ?>
                        <option value="<?= $i ?>" <?= $i == date('Y') ? 'selected' : '' ?>><?= $i ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="col-lg-3 mb-2">
                <label class="form-label">Unit Kerja</label>
                <select class="form-select" id="selectUnit">
                    <option value="all" selected>SEMUA UNIT</option>
                </select>
            </div>
        </div>

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

        <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
            <table id="table" class="table table-sm table-bordered border-dark p-1 mb-0 align-middle">
                <thead class="text-black fs-2" id="thead-absensi"></thead>
                <tbody class="text-black" id="tbody-absensi"></tbody>
            </table>
        </div>

    </div>
</div>

<div class="modal-place"></div>

<?= $this->endSection() ?>
<?= $this->section('script') ?>

<script>
    $(document).ready(function() {
        $('#filterBulan, #filterTahun').select2();
        getDataAbsensi()
    });

    $('#filterBulan, #filterTahun, #selectUnit').change(function() {
        getDataAbsensi()
    });

    function getDataAbsensi() {
        var bulan = $('#filterBulan').val();
        var tahun = $('#filterTahun').val();
        var unit = $('#selectUnit').val();
        $.ajax({
            type: "get",
            url: "<?= base_url('Absensi/getAbsensi') ?>",
            data: {
                bulan: bulan,
                tahun: tahun,
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
                if (response.status == "sukses") {
                    $('#thead-absensi').html(response.thead);
                    $('#tbody-absensi').html(response.tbody);
                }
            },
        });
    }

    $('#btnAdd').click(function(e) {
        e.preventDefault();
        $.ajax({
            type: "get",
            url: "<?= base_url('Absensi/newUnit') ?>",
            dataType: "json",
            success: function(response) {
                $('.modal-place').html(response.html);
                $('#modal').modal('show');
            },
        });
    })

    $('#selectUnit').select2({
        width: '100%',
        allowClear: true,
        ajax: {
            url: "<?= base_url('GetData/getUnitByTitikKoordinat') ?>",
            dataType: 'json',
            delay: 250,
            processResults: function(data) {
                let units = $.map(data, function(item) {
                    return {
                        id: item.id,
                        text: item.text
                    };
                });
                units.unshift({
                    id: 'all',
                    text: 'SEMUA UNIT',
                    selected: true
                });
                return {
                    results: units
                };
            }
        }
    });

    $('#exportPdf').click(function(e) {
        e.preventDefault();
        var bulan = $('#filterBulan').val();
        var tahun = $('#filterTahun').val();
        var unit = $('#selectUnit').val();
        $.ajax({
            type: "get",
            url: "<?= base_url('Absensi/modal_export_pdf') ?>",
            data: {
                bulan: bulan,
                tahun: tahun,
                unit: unit
            },
            dataType: "json",
            success: function(response) {
                $('.modal-place').html(``).html(response.html);
                $('.modal-place #modal').modal('show');
            }
        });
    });
</script>
<?= $this->endSection() ?>