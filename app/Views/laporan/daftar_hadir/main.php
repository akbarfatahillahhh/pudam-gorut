<?= $this->extend('layouts/main_ekas') ?>
<?= $this->section('content') ?>

<div class="card border border-dark">
    <div class="card-body p-4">
        <h5 class="card-title fw-semibold mb-4"><?= esc($title) ?></h5>

        <div class="row">
            <div class="col-lg-2 mb-2">
                <label class="form-label">Bulan</label>
                <select class="form-select" id="filterBulan">
                    <?php foreach ($daftarBulan as $key => $value) : ?>
                        <option value="<?= $key ?>" <?= $key == date('m') ? 'selected' : '' ?>>
                            <?= $value ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-lg-2 mb-2">
                <label class="form-label">Tahun</label>
                <select class="form-select" id="filterTahun">
                    <?php for ($i = date('Y'); $i >= date('Y') - 5; $i--) : ?>
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
            <div class="col-lg-2 mb-2 d-flex align-items-end">
                <button type="button" class="btn btn-sm btn-primary w-100" id="btnLihat">
                    <i class="ti ti-eye"></i>
                    Lihat
                </button>
            </div>
        </div>

        <div class="mb-2">
            <button class="btn btn-sm btn-danger" id="exportPdf">
                <i class="ti ti-file-type-pdf"></i>
                PDF
            </button>
        </div>

        <div class="border border-dark rounded p-3" id="areaLaporan">
            <div class="text-center py-5">
                <i class="ti ti-calendar-month text-dark" style="font-size: 3rem;"></i>
                <h5 class="text-dark mb-1">PRATINJAU DAFTAR HADIR</h5>
                <p class="text-muted mb-0">Tentukan Bulan, Tahun, dan Unit Kerja lalu klik tombol Lihat.</p>
            </div>
        </div>
    </div>
</div>

<div class="modal-place"></div>

<div class="modal fade" id="modalPdf" tabindex="-1" aria-labelledby="modalPdfLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered" style="height: 90vh;">
        <div class="modal-content" style="height: 90vh;">
            <div class="modal-header border-bottom">
                <h1 class="modal-title fs-5" id="modalPdfLabel">DAFTAR HADIR KARYAWAN</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="h-100 w-100">
                    <iframe id="framePdf" src="" class="h-100 w-100 border-0"></iframe>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
<?= $this->section('script') ?>

<script>
    $(document).ready(function() {
        $('#filterBulan, #filterTahun').select2();

        $('#selectUnit').select2({
            width: '100%',
            allowClear: true,
            placeholder: '[ Pilih Unit Kerja ]',
            ajax: {
                url: "<?= base_url('GetData/getTitikAbsen') ?>",
                dataType: 'json',
                delay: 250,
                data: function(params) {
                    return {
                        search: params.term
                    };
                },
                processResults: function(data) {
                    var units = $.map(data, function(item) {
                        return {
                            id: item.id,
                            text: item.text
                        };
                    });
                    units.unshift({
                        id: 'all',
                        text: 'SEMUA UNIT'
                    });
                    return {
                        results: units
                    };
                }
            }
        });
    });

    $('#btnLihat').click(function(e) {
        e.preventDefault();
        getDataDaftarHadir();
    });

    $('#filterBulan, #filterTahun, #selectUnit').change(function() {
        getDataDaftarHadir();
    });

    function getDataDaftarHadir() {
        var bulan = $('#filterBulan').val();
        var tahun = $('#filterTahun').val();
        var unit = $('#selectUnit').val();

        $.ajax({
            type: "get",
            url: "<?= base_url('Laporan/DaftarHadir/data') ?>",
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
                    var kepala = '<div class="mb-3">' +
                        '<div class="fs-5 fw-semibold text-uppercase">' + response.info.judul + '</div>' +
                        '<div class="text-muted">Periode: ' + response.info.periode + '</div>' +
                        '<div class="text-muted">Unit Kerja: ' + response.info.unit + '</div>' +
                        '</div>';

                    var tabel = '<div class="table-responsive" style="max-height: 550px; overflow: auto;">' +
                        '<table class="table table-sm table-bordered border-dark mb-0 align-middle">' +
                        '<thead class="text-black" id="thead-absensi"></thead>' +
                        '<tbody class="text-black" id="tbody-absensi"></tbody>' +
                        '</table>' +
                        '</div>';

                    $('#areaLaporan').html(kepala + tabel);
                    $('#thead-absensi').html(response.thead);
                    $('#tbody-absensi').html(response.tbody);
                } else {
                    Swal.fire('Peringatan', response.message, 'warning');
                }
            },
        });
    }

    $('#exportPdf').click(function(e) {
        e.preventDefault();

        var bulan = $('#filterBulan').val();
        var tahun = $('#filterTahun').val();
        var unit = $('#selectUnit').val();

        if (!bulan || !tahun || !unit) {
            Swal.fire('Peringatan', 'Pastikan Bulan, Tahun dan Unit Kerja tidak boleh kosong!', 'warning');
            return;
        }

        $('#framePdf').attr('src', "<?= base_url('laporan/daftar-hadir/pdf') ?>/" + bulan + "/" + tahun + "/" + unit);
        bootstrap.Modal.getOrCreateInstance(document.getElementById('modalPdf')).show();
    });
</script>
<?= $this->endSection() ?>
