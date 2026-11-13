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
                <h5 class="card-title mb-3 fw-semibold">Menu Cepat E-Kas</h5>
                <div class="d-flex flex-wrap gap-2">
                    <a href="<?= base_url('e-kas/keluar') ?>" class="btn btn-sm btn-primary">
                        <i class="fa-solid fa-money-bill-transfer me-1"></i> Kas Pengeluaran
                    </a>
                    <a href="<?= base_url('e-kas/riwayat') ?>" class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-history me-1"></i> Riwayat Kas
                    </a>
                    <a href="<?= base_url('faktur-pembelian') ?>" class="btn btn-sm btn-outline-dark">
                        <i class="fa-solid fa-right-to-bracket me-1"></i> Faktur Pembelian
                    </a>
                    <a href="<?= base_url('gudang/opname-stok') ?>" class="btn btn-sm btn-outline-dark">
                        <i class="fas fa-clipboard-list me-1"></i> Opname Stok
                    </a>
                    <a href="<?= base_url('laporan/rekap-persediaan') ?>" class="btn btn-sm btn-outline-dark">
                        <i class="fa-solid fa-chart-column me-1"></i> Rekap Persediaan
                    </a>
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
