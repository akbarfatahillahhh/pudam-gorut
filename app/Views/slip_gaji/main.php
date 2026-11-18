<?= $this->extend('layouts/main_ekas') ?>
<?= $this->section('content') ?>

<div class="card border border-dark">
    <div class="card-body p-4">
        <div class="row mb-3">
            <div class="col-12">
                <h5 class="card-title fw-semibold mb-1"><?= esc($title) ?></h5>
                <p class="text-muted mb-0 fs-2">
                    Rekap gaji pegawai per bulan beserta master tunjangan dan potongan.
                </p>
            </div>
        </div>

        <ul class="nav nav-tabs w-100 border-0 mb-3 p-0" id="tabSlipGaji" role="tablist">
            <li class="nav-item px-1" role="presentation" style="width: 33.3333%">
                <button class="nav-link w-100 border border-dark active text-uppercase rounded" id="tab-gaji-btn"
                    data-bs-toggle="tab" data-bs-target="#tab-gaji" type="button" role="tab"
                    aria-controls="tab-gaji" aria-selected="true">
                    Gaji Karyawan
                </button>
            </li>
            <li class="nav-item px-1" role="presentation" style="width: 33.3333%">
                <button class="nav-link w-100 border border-dark text-uppercase rounded" id="tab-tunjangan-btn"
                    data-bs-toggle="tab" data-bs-target="#tab-tunjangan" type="button" role="tab"
                    aria-controls="tab-tunjangan" aria-selected="false">
                    Master Tunjangan
                </button>
            </li>
            <li class="nav-item px-1" role="presentation" style="width: 33.3333%">
                <button class="nav-link w-100 border border-dark text-uppercase rounded" id="tab-potongan-btn"
                    data-bs-toggle="tab" data-bs-target="#tab-potongan" type="button" role="tab"
                    aria-controls="tab-potongan" aria-selected="false">
                    Master Potongan
                </button>
            </li>
        </ul>

        <div class="tab-content border border-dark rounded" id="tabSlipGajiContent">
            <div class="tab-pane fade show active" id="tab-gaji" role="tabpanel" aria-labelledby="tab-gaji-btn">
                <?= $this->include('slip_gaji/page/gaji', ['bulanNama' => $bulanNama]) ?>
            </div>
            <div class="tab-pane fade" id="tab-tunjangan" role="tabpanel" aria-labelledby="tab-tunjangan-btn">
                <?= $this->include('slip_gaji/page/tunjangan') ?>
            </div>
            <div class="tab-pane fade" id="tab-potongan" role="tabpanel" aria-labelledby="tab-potongan-btn">
                <?= $this->include('slip_gaji/page/potongan') ?>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
