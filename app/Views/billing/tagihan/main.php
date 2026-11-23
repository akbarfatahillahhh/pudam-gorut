<?= $this->extend('layouts/main_billing') ?>
<?= $this->section('content') ?>

<div class="card border border-dark">
    <div class="card-body p-4">
        <h5 class="card-title fw-semibold mb-3"><?= esc($title) ?></h5>

        <ul class="nav nav-tabs border-dark mb-3" id="tabTagihan" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active rounded-0" data-bs-toggle="tab" data-bs-target="#tab-pemakaian" type="button">
                    <i class="ti ti-gauge"></i> Entry Pemakaian
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link rounded-0" data-bs-toggle="tab" data-bs-target="#tab-pembayaran" type="button">
                    <i class="ti ti-cash-banknote"></i> Entry Pembayaran
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link rounded-0" data-bs-toggle="tab" data-bs-target="#tab-cetak" type="button">
                    <i class="ti ti-printer"></i> Cetak Tagihan
                </button>
            </li>
        </ul>

        <div class="tab-content">
            <?= $this->include('billing/tagihan/page/pemakaian') ?>
            <?= $this->include('billing/tagihan/page/pembayaran') ?>
            <?= $this->include('billing/tagihan/page/cetak') ?>
        </div>
    </div>
</div>

<div class="modal-place"></div>

<?= $this->endSection() ?>
