<?= $this->extend('layouts/main_billing') ?>
<?= $this->section('content') ?>

<div class="card border border-dark">
    <div class="card-body p-4">
        <h5 class="card-title fw-semibold mb-3"><?= esc($title) ?></h5>
        <p class="text-muted fs-2 mb-3">
            Daftar nomor rekening yang dipakai lebih dari satu pelanggan pada periode yang sama.
        </p>

        <div class="row g-2 mb-3">
            <div class="col-lg-2 col-6">
                <label class="form-label">Bulan</label>
                <select id="dupBulan" class="form-select form-select-sm">
                    <option value="ALL">Semua</option>
                    <?php
                    $namaBulan = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
                                  'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
                    for ($i = 1; $i <= 12; $i++): ?>
                        <option value="<?= $i ?>" <?= (int) date('n') === $i ? 'selected' : '' ?>><?= $namaBulan[$i] ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="col-lg-2 col-6">
                <label class="form-label">Tahun</label>
                <select id="dupTahun" class="form-select form-select-sm">
                    <option value="ALL">Semua</option>
                    <?php for ($i = (int) date('Y'); $i >= 2009; $i--): ?>
                        <option value="<?= $i ?>"><?= $i ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="col-lg-2 col-6 d-flex align-items-end">
                <button type="button" class="btn btn-sm btn-primary w-100" id="dupTampil">
                    <i class="ti ti-search"></i> Tampilkan
                </button>
            </div>
        </div>

        <div class="table-responsive">
            <table id="tableDuplikat" class="table table-sm table-bordered border-dark align-middle mb-0">
                <thead class="text-black fs-2">
                    <tr>
                        <th class="text-center" width="4%">#</th>
                        <th class="text-center" width="14%">NO REKENING</th>
                        <th width="20%">PERIODE</th>
                        <th class="text-center" width="12%">JUMLAH</th>
                        <th class="text-center" width="12%">#</th>
                    </tr>
                </thead>
                <tbody class="text-black fs-2"></tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal-place"></div>

<?= $this->endSection() ?>

<?= $this->section('script') ?>
<script>
    $(document).ready(function() {
        $('#tableDuplikat').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                type: 'post',
                url: "<?= base_url('Billing/Tagihan/data_duplikat') ?>",
                data: function(d) {
                    d.bulan = $('#dupBulan').val();
                    d.tahun = $('#dupTahun').val();
                },
            },
            columns: [
                { data: null, class: 'text-center align-middle', searchable: false },
                { data: 'no_rek', class: 'text-center align-middle' },
                { data: 'periode', class: 'align-middle', searchable: false },
                { data: 'jumlah', class: 'text-center align-middle fw-semibold' },
                { data: 'aksi', class: 'text-center align-middle', searchable: false, orderable: false },
            ],
            order: [[3, 'desc']],
            pageLength: 25,
            language: {
                emptyTable: 'Tidak ada nomor rekening duplikat.',
                zeroRecords: 'Tidak ada data.',
                processing: 'Memuat...',
                search: 'Cari:',
                lengthMenu: 'Tampil _MENU_ baris',
                info: 'Menampilkan _START_ sampai _END_ dari _TOTAL_ rekening',
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

        $('#dupTampil, #dupBulan, #dupTahun').on('change click', function() {
            $('#tableDuplikat').DataTable().ajax.reload();
        });
    });

    function lihatDuplikat(noRek, bulan, tahun) {
        $.ajax({
            type: 'get',
            url: "<?= base_url('Billing/Tagihan/rincian_duplikat') ?>",
            data: { no_rek: noRek, bulan: bulan, tahun: tahun },
            dataType: 'json',
            success: function(res) {
                if (res.status !== 'success') {
                    Swal.fire('Gagal', 'Rincian tidak bisa diambil.', 'error');
                    return;
                }

                $('.modal-place').html(res.html);
                bootstrap.Modal.getOrCreateInstance(document.getElementById('modalDuplikat')).show();
            },
            error: function() {
                Swal.fire('Gagal', 'Rincian tidak bisa diambil.', 'error');
            },
        });
    }
</script>
<?= $this->endSection() ?>
