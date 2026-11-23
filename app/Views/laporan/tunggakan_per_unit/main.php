<?= $this->extend('layouts/main_billing') ?>
<?= $this->section('content') ?>

<div class="card border border-dark">
    <div class="card-body p-4">
        <h5 class="card-title fw-semibold mb-3"><?= esc($title) ?></h5>
        <p class="text-muted fs-2 mb-3">
            Daftar tagihan per pelanggan untuk satu tahun. Bisa disaring per unit,
            kelurahan, jenis pelanggan, status bayar, dan status pelanggan.
        </p>

        <div class="row g-2 mb-3">
            <div class="col-lg-2 col-6">
                <label class="form-label">Tahun</label>
                <select id="tpTahun" class="form-select form-select-sm">
                    <?php foreach ($tahun as $th): ?>
                        <option value="<?= $th ?>" <?= $th === (int) date('Y') ? 'selected' : '' ?>><?= $th ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-lg-3 col-6">
                <label class="form-label">Unit</label>
                <select id="tpUnit" class="form-select form-select-sm">
                    <option value="0">[ Semua Unit ]</option>
                </select>
            </div>
            <div class="col-lg-3 col-6">
                <label class="form-label">Kelurahan/Desa</label>
                <select id="tpKelurahan" class="form-select form-select-sm">
                    <option value="0">[ Semua Kelurahan ]</option>
                </select>
            </div>
            <div class="col-lg-4 col-6">
                <label class="form-label">Jenis Pelanggan</label>
                <select id="tpJenis" class="form-select form-select-sm">
                    <option value="0">[ Semua Jenis ]</option>
                </select>
            </div>
            <div class="col-lg-2 col-6">
                <label class="form-label">Status Bayar</label>
                <select id="tpStatus" class="form-select form-select-sm">
                    <option value="all">Keseluruhan</option>
                    <option value="0">Belum Dibayar</option>
                    <option value="1">Sudah Dibayar</option>
                </select>
            </div>
            <div class="col-lg-2 col-6">
                <label class="form-label">Status Pelanggan</label>
                <select id="tpPelanggan" class="form-select form-select-sm">
                    <option value="all">Semua</option>
                    <option value="aktif">Aktif</option>
                    <option value="nonaktif">Tidak Aktif</option>
                </select>
            </div>
            <div class="col-lg-2 col-6 d-flex align-items-end">
                <button type="button" class="btn btn-sm btn-primary w-100" id="tpTampil">
                    <i class="ti ti-search"></i> Tampilkan
                </button>
            </div>
        </div>

        <div class="mb-2">
            <button class="btn btn-sm btn-danger" id="tpPdf" disabled>
                <i class="fa fa-file-pdf"></i> PDF
            </button>
        </div>

        <div id="tpKosong" class="text-center py-5">
            <i class="ti ti-file-search text-dark" style="font-size:3rem;"></i>
            <h6 class="text-dark mb-1">DAFTAR RINCIAN TUNGGAKAN</h6>
            <p class="text-muted mb-0">Tentukan filter lalu klik Tampilkan.</p>
        </div>

        <div id="tpHasil" style="display:none;">
            <div class="mb-2">
                <div class="fs-5 fw-semibold text-uppercase" id="tpJudul">-</div>
                <div class="text-muted fs-2" id="tpInfo">-</div>
            </div>

            <div class="table-responsive" style="max-height: 58vh; overflow:auto;">
                <table id="tableTunggakan" class="table table-sm table-bordered border-dark align-middle mb-0">
                    <thead class="text-black fs-2">
                        <tr>
                            <th class="text-center" width="4%">#</th>
                            <th class="text-center" width="12%">SAMBUNGAN</th>
                            <th>NAMA PELANGGAN</th>
                            <th class="text-center" width="8%">REKENING</th>
                            <th class="text-center" width="11%">HARGA AIR</th>
                            <th class="text-center" width="9%">BEA. ADM</th>
                            <th class="text-center" width="11%">JUMLAH</th>
                            <th class="text-center" width="10%">BULAN</th>
                            <th class="text-center" width="9%">STATUS</th>
                        </tr>
                    </thead>
                    <tbody class="text-black fs-2"></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('script') ?>
<script>
(function() {
    let tabel = null;

    function isiSelect(sel, url, placeholder, params) {
        $.ajax({
            type: 'get',
            url: url,
            data: params || {},
            dataType: 'json',
            success: function(list) {
                if (!Array.isArray(list)) {
                    return;
                }

                $(sel).html('<option value="0">' + placeholder + '</option>');

                $.each(list, function(i, item) {
                    $(sel).append('<option value="' + item.id + '">' + item.text + '</option>');
                });

                $(sel).select2({ width: '100%' });
            },
        });
    }

    isiSelect('#tpUnit', "<?= base_url('Laporan/TunggakanPerUnit/unit') ?>", '[ Semua Unit ]');
    isiSelect('#tpJenis', "<?= base_url('Laporan/TunggakanPerUnit/jenis_pelanggan') ?>", '[ Semua Jenis ]');
    isiSelect('#tpKelurahan', "<?= base_url('Laporan/TunggakanPerUnit/kelurahan') ?>", '[ Semua Kelurahan ]');

    $('#tpUnit').on('change', function() {
        isiSelect(
            '#tpKelurahan',
            "<?= base_url('Laporan/TunggakanPerUnit/kelurahan') ?>",
            '[ Semua Kelurahan ]',
            { id_kec: $(this).val() }
        );
    });

    function filter() {
        return {
            tahun: $('#tpTahun').val(),
            unit: $('#tpUnit').val(),
            kelurahan: $('#tpKelurahan').val(),
            jenis: $('#tpJenis').val(),
            status: $('#tpStatus').val(),
            pelanggan: $('#tpPelanggan').val(),
        };
    }

    $('#tpTampil').click(function() {
        $('#tpKosong').hide();
        $('#tpHasil').show();

        const f = filter();

        if (tabel) {
            tabel.destroy();
        }

        tabel = $('#tableTunggakan').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                type: 'post',
                url: "<?= base_url('Laporan/TunggakanPerUnit/datatable') ?>",
                data: function(d) {
                    Object.assign(d, filter());
                },
            },
            columns: [
                { data: null, class: 'text-center align-middle', searchable: false, orderable: false },
                { data: 'no_sambungan', class: 'text-center align-middle' },
                { data: 'nama_pelanggan', class: 'align-middle text-uppercase' },
                { data: 'no_rek', class: 'text-center align-middle' },
                { data: 'hargaAir', class: 'text-end align-middle', searchable: false },
                { data: 'by_admin', class: 'text-end align-middle', searchable: false },
                { data: 'jlh_tagihan', class: 'text-end align-middle fw-semibold', searchable: false },
                { data: 'bulan_tagihan', class: 'text-center align-middle', searchable: false },
                { data: 'status', class: 'text-center align-middle', searchable: false },
            ],
            order: [[1, 'asc']],
            pageLength: 50,
            language: {
                emptyTable: 'Tidak ada tagihan pada filter ini.',
                zeroRecords: 'Tagihan tidak ditemukan.',
                processing: 'Memuat...',
                search: 'Cari:',
                lengthMenu: 'Tampil _MENU_ baris',
                info: 'Menampilkan _START_ sampai _END_ dari _TOTAL_ tagihan',
                infoEmpty: 'Tidak ada data',
                paginate: { previous: 'Sebelumnya', next: 'Berikutnya' },
            },
            drawCallback: function() {
                const info = this.api().page.info();

                this.api().column(0).nodes().each(function(cell, i) {
                    cell.innerHTML = info.start + i + 1;
                });

                $('#tpInfo').text(
                    info.recordsDisplay.toLocaleString('id-ID') + ' tagihan · tahun ' + f.tahun
                );
                $('#tpPdf').prop('disabled', info.recordsDisplay === 0);
            },
        });
    });

    $('#tpPdf').click(function() {
        const f = filter();

        const url = "<?= base_url('Laporan/TunggakanPerUnit/export_pdf') ?>" +
            '?tahun=' + f.tahun +
            '&unit=' + f.unit +
            '&kelurahan=' + f.kelurahan +
            '&jenis=' + f.jenis +
            '&status=' + f.status +
            '&pelanggan=' + f.pelanggan;

        // Tanya jumlah baris dulu (endpoint ringan, tanpa membuat PDF).
        // Tanpa ini pengguna hanya melihat teks mentah 413 di tab baru.
        $.ajax({
            type: 'get',
            url: "<?= base_url('Laporan/TunggakanPerUnit/cek_jumlah') ?>",
            data: f,
            dataType: 'json',
            success: function(res) {
                if (res.status !== 'success' || !res.boleh) {
                    Swal.fire(
                        'Data Terlalu Banyak',
                        (res.jumlah || 0).toLocaleString('id-ID') + ' baris melebihi batas ' +
                        (res.batas || 0).toLocaleString('id-ID') + '. Persempit filter (unit, kelurahan, jenis, atau status).',
                        'warning'
                    );
                    return;
                }

                window.open(url, '_blank');
            },
            error: function() {
                window.open(url, '_blank');
            },
        });
    });
})();
</script>
<?= $this->endSection() ?>
