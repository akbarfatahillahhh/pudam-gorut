<div class="modal fade" id="modalLoket" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-xl modal-dialog-centered" style="height: 90vh;">
        <div class="modal-content" style="height: 90vh;">
            <div class="modal-header border-bottom">
                <h5 class="modal-title fw-semibold">
                    <?= esc($pelanggan->no_sambungan) ?> &mdash; <?= strtoupper(esc($pelanggan->nama_pelanggan)) ?>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col-lg-6">
                        <div class="border border-dark rounded p-2 h-100">
                            <div class="fs-2 text-muted">Alamat</div>
                            <div class="fw-semibold"><?= esc($pelanggan->alamat ?? '-') ?></div>
                            <div class="fs-2 text-muted mt-1">
                                <?= esc($pelanggan->kecamatan ?? '-') ?> &mdash; <?= esc($pelanggan->kelurahan ?? '-') ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="border border-dark rounded p-2 h-100">
                            <div class="fs-2 text-muted">Golongan</div>
                            <div class="fw-semibold">
                                <?= esc($pelanggan->kd_jenis ?? '-') ?> &mdash;
                                <?= esc($pelanggan->jenis_pelanggan ?? '-') ?>
                            </div>
                            <div class="fs-2 text-muted mt-1"><?= esc($pelanggan->kelompok_jasa ?? '-') ?></div>
                        </div>
                    </div>
                </div>

                <div class="row g-2 mb-2">
                    <div class="col-lg-2 col-6">
                        <label class="form-label">Bulan</label>
                        <select id="filterBulan" class="form-select form-select-sm">
                            <option value="ALL" selected>Semua</option>
                            <?php
                            $namaBulan = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
                                          'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
                            for ($i = 1; $i <= 12; $i++): ?>
                                <option value="<?= $i ?>"><?= $namaBulan[$i] ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="col-lg-2 col-6">
                        <label class="form-label">Tahun</label>
                        <select id="filterTahun" class="form-select form-select-sm">
                            <option value="ALL" selected>Semua</option>
                            <?php for ($i = (int) date('Y'); $i >= 2009; $i--): ?>
                                <option value="<?= $i ?>"><?= $i ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="col-lg-3 col-6">
                        <label class="form-label">Status</label>
                        <select id="filterStatus" class="form-select form-select-sm">
                            <option value="ALL" selected>Semua</option>
                            <option value="0">Menunggak</option>
                            <option value="1">Lunas</option>
                        </select>
                    </div>
                </div>

                <div class="table-responsive" style="max-height: 52vh; overflow: auto;">
                    <table id="tableTagihanLoket" class="table table-sm table-bordered border-dark align-middle mb-0">
                        <thead class="text-black fs-2">
                            <tr>
                                <th class="text-center" width="4%">#</th>
                                <th class="text-center" width="8%">REKENING</th>
                                <th width="14%">PERIODE</th>
                                <th class="text-center" width="12%">JUMLAH</th>
                                <th class="text-center" width="10%">DENDA</th>
                                <th class="text-center" width="10%">STATUS</th>
                                <th class="text-center" width="14%">#</th>
                            </tr>
                        </thead>
                        <tbody class="text-black fs-2"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        $('#tableTagihanLoket').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                type: 'post',
                url: "<?= base_url('Billing/Loket/data_tagihan') ?>",
                data: function(d) {
                    d.id_pelanggan = <?= (int) $id_pelanggan ?>;
                    d.bulan = $('#filterBulan').val();
                    d.tahun = $('#filterTahun').val();
                    d.status = $('#filterStatus').val();
                },
            },
            columns: [
                { data: null, class: 'text-center align-middle', searchable: false },
                { data: 'no_rek', class: 'text-center align-middle' },
                { data: 'bulan_tagihan', class: 'align-middle', searchable: false },
                { data: 'jlh_tagihan', class: 'text-center align-middle', searchable: false },
                { data: 'denda', class: 'text-center align-middle', searchable: false },
                { data: 'status', class: 'text-center align-middle', searchable: false },
                { data: 'aksi', class: 'text-center align-middle', searchable: false, orderable: false },
            ],
            order: [[2, 'desc']],
            pageLength: 25,
            searching: false,
            language: {
                emptyTable: 'Tidak ada tagihan.',
                zeroRecords: 'Tagihan tidak ditemukan.',
                processing: 'Memuat...',
                info: 'Menampilkan _START_ sampai _END_ dari _TOTAL_ tagihan',
                infoEmpty: 'Tidak ada data',
                paginate: { previous: 'Sebelumnya', next: 'Berikutnya' },
                lengthMenu: 'Tampil _MENU_ baris',
            },
            drawCallback: function() {
                const info = this.api().page.info();
                this.api().column(0).nodes().each(function(cell, i) {
                    cell.innerHTML = info.start + i + 1;
                });
            },
        });

        $('#filterBulan, #filterTahun, #filterStatus').change(function() {
            $('#tableTagihanLoket').DataTable().ajax.reload(null, false);
        });
    });
</script>
