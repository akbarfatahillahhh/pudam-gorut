<?= $this->extend('layouts/main_billing') ?>
<?= $this->section('content') ?>

<div class="card border border-dark">
    <div class="card-body p-4">
        <h5 class="card-title fw-semibold mb-3"><?= esc($title) ?></h5>

        <div class="row g-2 mb-3">
            <div class="col-lg-3 col-6">
                <label class="form-label">Periode</label>
                <input type="date" id="bsAwal" class="form-control form-control-sm" value="<?= date('Y-m-01') ?>">
            </div>
            <div class="col-lg-3 col-6">
                <label class="form-label">S/D</label>
                <input type="date" id="bsAkhir" class="form-control form-control-sm" value="<?= date('Y-m-d') ?>">
            </div>
            <div class="col-lg-2 col-6 d-flex align-items-end">
                <button type="button" class="btn btn-sm btn-primary w-100" id="bsMuat">
                    <i class="ti ti-refresh"></i> Muat
                </button>
            </div>
        </div>

        <ul class="nav nav-tabs border-dark mb-3" id="tabBilling" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active rounded-0" data-bs-toggle="tab" data-bs-target="#tab-penagih" type="button" id="tabBtnPenagih">
                    <i class="ti ti-users"></i> Penagih
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link rounded-0" data-bs-toggle="tab" data-bs-target="#tab-bms" type="button" id="tabBtnBms">
                    <i class="ti ti-device-mobile"></i> BMS FastPay
                </button>
            </li>
        </ul>

        <div class="tab-content">
            <div class="tab-pane fade show active" id="tab-penagih" role="tabpanel">
                <div class="table-responsive" style="max-height:60vh;overflow:auto;">
                    <table id="tablePenagih" class="table table-sm table-bordered border-dark align-middle mb-0">
                        <thead class="text-black fs-2">
                            <tr>
                                <th class="text-center" width="4%">#</th>
                                <th width="18%">PETUGAS</th>
                                <th width="12%">UNIT</th>
                                <th class="text-center" width="12%">PELANGGAN AKTIF</th>
                                <th class="text-center" width="10%">DITAGIH</th>
                                <th class="text-center" width="14%">NILAI</th>
                                <th class="text-center" width="12%">#</th>
                            </tr>
                        </thead>
                        <tbody class="text-black fs-2"></tbody>
                    </table>
                </div>
            </div>

            <div class="tab-pane fade" id="tab-bms" role="tabpanel">
                <div class="d-flex justify-content-between align-items-end mb-2">
                    <div class="fs-2 text-muted">Transaksi penagihan lewat Bima Sakti FastPay.</div>
                    <div class="fs-2">Total: <b id="bmsTotal">Rp 0</b></div>
                </div>

                <div class="table-responsive" style="max-height:55vh;overflow:auto;">
                    <table id="tableBms" class="table table-sm table-bordered border-dark align-middle mb-0">
                        <thead class="text-black fs-2">
                            <tr>
                                <th class="text-center" width="4%">#</th>
                                <th class="text-center" width="12%">TGL BAYAR</th>
                                <th class="text-center" width="11%">REKENING</th>
                                <th>PELANGGAN</th>
                                <th width="16%">WILAYAH</th>
                                <th class="text-center" width="11%">PERIODE</th>
                                <th class="text-center" width="12%">JUMLAH</th>
                            </tr>
                        </thead>
                        <tbody class="text-black fs-2"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal-place"></div>

<?= $this->endSection() ?>

<?= $this->section('script') ?>
<script>
(function() {
    let tabelPenagih = null;
    let tabelBms = null;

    function rupiah(n) {
        return 'Rp ' + Number(n || 0).toLocaleString('id-ID');
    }

    function filter() {
        return {
            awal: $('#bsAwal').val(),
            akhir: $('#bsAkhir').val(),
        };
    }

    function muatPenagih() {
        if (tabelPenagih) {
            tabelPenagih.destroy();
        }

        tabelPenagih = $('#tablePenagih').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                type: 'post',
                url: "<?= base_url('Billing/BillingSystem/penagih') ?>",
                data: function(d) {
                    Object.assign(d, filter());
                },
            },
            columns: [
                { data: null, class: 'text-center align-middle', searchable: false, orderable: false },
                {
                    data: null,
                    class: 'align-middle',
                    render: function(data, type, row) {
                        return '<b>' + (row.nama_lengkap ?? '-') + '</b>' +
                            '<div class="fs-1 text-muted">' + (row.username ?? '') + '</div>';
                    },
                },
                { data: 'kecamatan', class: 'align-middle', searchable: false },
                { data: 'pelanggan_unit', class: 'text-center align-middle', searchable: false },
                { data: 'ditagih', class: 'text-center align-middle fw-semibold', searchable: false },
                {
                    data: 'nilai',
                    class: 'text-end align-middle fw-semibold',
                    searchable: false,
                    render: function(n) { return rupiah(n); },
                },
                { data: 'aksi', class: 'text-center align-middle', searchable: false, orderable: false },
            ],
            order: [[1, 'asc']],
            pageLength: 25,
            language: {
                emptyTable: 'Tidak ada petugas penagih.',
                zeroRecords: 'Tidak ada data.',
                processing: 'Memuat...',
                search: 'Cari:',
                lengthMenu: 'Tampil _MENU_ baris',
                info: 'Menampilkan _START_ sampai _END_ dari _TOTAL_ petugas',
                infoEmpty: 'Tidak ada data',
                paginate: { previous: 'Sebelumnya', next: 'Berikutnya' },
            },
            drawCallback: function() {
                const info = this.api().page.info();

                this.api().column(0).nodes().each(function(cell, i) {
                    cell.innerHTML = info.start + i + 1;
                });
            },
        });
    }

    function muatBms() {
        if (tabelBms) {
            tabelBms.destroy();
        }

        tabelBms = $('#tableBms').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                type: 'post',
                url: "<?= base_url('Billing/BillingSystem/bms') ?>",
                data: function(d) {
                    Object.assign(d, filter());
                },
                dataSrc: function(json) {
                    // Total seluruh baris dikirim server lewat header
                    // X-Total-Nilai; menjumlahkan json.data hanya akan
                    // menghitung halaman yang sedang tampil.
                    return json.data;
                },
            },
            columns: [
                { data: null, class: 'text-center align-middle', searchable: false, orderable: false },
                { data: 'tgl_bayar', class: 'text-center align-middle', searchable: false },
                { data: 'no_rek', class: 'text-center align-middle' },
                {
                    data: null,
                    class: 'align-middle',
                    render: function(data, type, row) {
                        return '<b>' + (row.no_sambungan ?? '-') + '</b> ' +
                            '<span class="text-uppercase">' + (row.nama_pelanggan ?? '-') + '</span>';
                    },
                },
                {
                    data: null,
                    class: 'align-middle',
                    searchable: false,
                    render: function(data, type, row) {
                        return [row.kecamatan, row.kelurahan].filter(Boolean).join(' / ') || '-';
                    },
                },
                { data: 'bulan_tagihan', class: 'text-center align-middle', searchable: false },
                {
                    data: 'jlh_tagihan',
                    class: 'text-end align-middle fw-semibold',
                    searchable: false,
                    render: function(n) { return rupiah(n); },
                },
            ],
            order: [[1, 'desc']],
            pageLength: 25,
            language: {
                emptyTable: 'Tidak ada transaksi BMS pada periode ini.',
                zeroRecords: 'Tidak ada data.',
                processing: 'Memuat...',
                search: 'Cari:',
                lengthMenu: 'Tampil _MENU_ baris',
                info: 'Menampilkan _START_ sampai _END_ dari _TOTAL_ transaksi',
                infoEmpty: 'Tidak ada data',
                paginate: { previous: 'Sebelumnya', next: 'Berikutnya' },
            },
            drawCallback: function() {
                const info = this.api().page.info();

                this.api().column(0).nodes().each(function(cell, i) {
                    cell.innerHTML = info.start + i + 1;
                });

            },
        });
    }

    function muatRingkasanBms() {
        $.ajax({
            type: 'get',
            url: "<?= base_url('Billing/BillingSystem/bms_ringkasan') ?>",
            data: filter(),
            dataType: 'json',
            success: function(res) {
                if (res.status !== 'success') {
                    return;
                }

                $('#bmsTotal').text(
                    rupiah(res.total) + ' · ' + Number(res.jumlah).toLocaleString('id-ID') + ' transaksi'
                );
            },
        });
    }

    muatPenagih();
    muatBms();
    muatRingkasanBms();

    $('#bsMuat').click(function() {
        muatPenagih();
        muatBms();
        muatRingkasanBms();
    });

    $('#tabBtnBms').on('shown.bs.tab', function() {
        tabelBms.columns.adjust();
    });

    window.lihatRincian = function(idSession, nama) {
        const f = filter();

        $.ajax({
            type: 'get',
            url: "<?= base_url('Billing/BillingSystem/rincian_penagih') ?>",
            data: { id_session: idSession, awal: f.awal, akhir: f.akhir },
            dataType: 'json',
            success: function(res) {
                if (res.status !== 'success') {
                    Swal.fire('Gagal', 'Rincian tidak bisa diambil.', 'error');
                    return;
                }

                $('.modal-place').html(res.html);
                bootstrap.Modal.getOrCreateInstance(document.getElementById('modalRincian')).show();
            },
            error: function() {
                Swal.fire('Gagal', 'Rincian tidak bisa diambil.', 'error');
            },
        });
    };
})();
</script>
<?= $this->endSection() ?>
