<?= $this->extend('layouts/main_ekas') ?>
<?= $this->section('content') ?>

<div class="row">

    <div class="col-lg-3">
        <div class="card overflow-hidden border border-dark">
            <div class="card-body p-4">
                <h5 class="card-title mb-8 fw-semibold text-center">
                    Voucher Kas Keluar
                </h5>
                <div class="row align-items-center">
                    <div class="col-12 text-center">
                        <h4 class="fw-semibold mb-3 totalVoucher">0</h4>
                        <div class="d-flex align-items-center justify-content-center">
                            <div class="me-4">
                                <span class="round-8 bg-primary rounded-circle me-2 d-inline-block"></span>
                                <span class="fs-2 text-black"><?= date('01 M Y') ?></span>
                            </div>
                            <div>
                                <span class="round-8 bg-success rounded-circle me-2 d-inline-block"></span>
                                <span class="fs-2 text-black"><?= date('d M Y') ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-3">
        <div class="card overflow-hidden border border-dark">
            <div class="card-body p-4">
                <h5 class="card-title mb-8 fw-semibold text-center">
                    Voucher Bulan Ini
                </h5>
                <div class="row align-items-center">
                    <div class="col-12 text-center">
                        <h4 class="fw-semibold mb-3 totalVoucherBulanIni">0</h4>
                        <div class="d-flex align-items-center justify-content-center">
                            <div class="me-4">
                                <span class="round-8 bg-primary rounded-circle me-2 d-inline-block"></span>
                                <span class="fs-2 text-black"><?= date('01 M Y') ?></span>
                            </div>
                            <div>
                                <span class="round-8 bg-success rounded-circle me-2 d-inline-block"></span>
                                <span class="fs-2 text-black"><?= date('d M Y') ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-3">
        <div class="card overflow-hidden border border-dark">
            <div class="card-body p-4">
                <h5 class="card-title mb-8 fw-semibold text-center">
                    Data Barang
                </h5>
                <div class="row align-items-center">
                    <div class="col-12 text-center">
                        <h4 class="fw-semibold mb-3 totalBarang">0</h4>
                        <div class="d-flex align-items-center justify-content-center">
                            <div class="me-4">
                                <span class="round-8 bg-primary rounded-circle me-2 d-inline-block"></span>
                                <span class="fs-2 text-black"><?= date('01 M Y') ?></span>
                            </div>
                            <div>
                                <span class="round-8 bg-success rounded-circle me-2 d-inline-block"></span>
                                <span class="fs-2 text-black"><?= date('d M Y') ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-3">
        <div class="card overflow-hidden border border-dark">
            <div class="card-body p-4">
                <h5 class="card-title mb-8 fw-semibold text-center">
                    Permintaan Menunggu
                </h5>
                <div class="row align-items-center">
                    <div class="col-12 text-center">
                        <h4 class="fw-semibold mb-3 totalPermintaan">0</h4>
                        <div class="d-flex align-items-center justify-content-center">
                            <div class="me-4">
                                <span class="round-8 bg-primary rounded-circle me-2 d-inline-block"></span>
                                <span class="fs-2 text-black"><?= date('01 M Y') ?></span>
                            </div>
                            <div>
                                <span class="round-8 bg-success rounded-circle me-2 d-inline-block"></span>
                                <span class="fs-2 text-black"><?= date('d M Y') ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<div class="row">
    <div class="col-12">
        <div class="card overflow-hidden border border-dark">
            <div class="card-body p-4">
                <div class="mb-3">
                    <h5 class="card-title mb-0 fw-semibold">Menu Cepat</h5>
                    <span class="fs-2 text-muted">Pintasan ke pekerjaan yang sering dipakai</span>
                </div>

                <div class="rounded border border-dark overflow-hidden">
                    <?php
                    // Semua pintasan seragam, tidak ada yang disorot.
                    // Tiap item: url, label, keterangan, ikon.
                    $pintasan = [
                        ['url' => 'ekas/keluar', 'label' => 'Kas Pengeluaran', 'ket' => 'Catat voucher pengeluaran kas harian', 'icon' => 'ti ti-cash-banknote'],
                        ['url' => 'ekas/riwayat', 'label' => 'Riwayat Kas', 'ket' => 'Telusuri voucher yang sudah dibuat', 'icon' => 'ti ti-history'],
                        ['url' => 'ekas/faktur-pembelian', 'label' => 'Faktur Pembelian', 'ket' => 'Input penerimaan barang masuk', 'icon' => 'ti ti-file-invoice'],
                        ['url' => 'ekas/gudang/opname-stok', 'label' => 'Opname Stok', 'ket' => 'Hitung dan sesuaikan stok gudang', 'icon' => 'ti ti-clipboard-list'],
                        ['url' => 'ekas/laporan/rekap-persediaan', 'label' => 'Rekap Persediaan', 'ket' => 'Laporan posisi persediaan', 'icon' => 'ti ti-chart-pie'],
                    ];
                    ?>

                    <?php foreach ($pintasan as $i => $item): ?>
                        <a href="<?= base_url($item['url']) ?>"
                            class="d-flex align-items-center gap-3 p-3 text-decoration-none text-black
                                <?= $i < count($pintasan) - 1 ? 'border-bottom border-dark' : '' ?>">
                            <span class="d-flex align-items-center justify-content-center rounded bg-light flex-shrink-0"
                                style="width: 38px; height: 38px;">
                                <i class="<?= esc($item['icon'], 'attr') ?> fs-5"></i>
                            </span>
                            <span class="flex-grow-1">
                                <span class="d-block fw-semibold"><?= esc($item['label']) ?></span>
                                <span class="d-block fs-2 text-muted"><?= esc($item['ket']) ?></span>
                            </span>
                            <i class="ti ti-chevron-right fs-4 text-muted flex-shrink-0"></i>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('script') ?>
<script>
    $(document).ready(function() {
        $.ajax({
            type: "get",
            url: "<?= base_url('Home/get_data_for_card_ekas') ?>",
            dataType: "json",
            success: function(response) {
                $('.totalVoucher').html(formatIdr(response.total_voucher));
                $('.totalVoucherBulanIni').html(formatIdr(response.total_voucher_bulan_ini));
                $('.totalBarang').html(formatIdr(response.total_barang));
                $('.totalPermintaan').html(formatIdr(response.total_permintaan));
            },
            error: function() {
                // Biarkan angka 0 kalau data belum tersedia.
            }
        });
    });

    function formatIdr(number) {
        return new Intl.NumberFormat('id-ID', {
            style: 'decimal',
            minimumFractionDigits: 0
        }).format(number || 0);
    }
</script>
<?= $this->endSection() ?>
