<aside class="left-sidebar border-dark">
    <div>
        <div class="brand-logo d-flex align-items-center justify-content-between">
            <a href="/home" class="text-nowrap logo-img">
                <img src="<?= base_url() ?>/logo/perumda-gorut.png" width="40" alt="PERUMDA GORUT" class="me-1" />
                <span class="h6 fw-bolder align-middle">PERUMDA</span>
                <span class="h6 fw-bolder align-middle">GORUT</span>
            </a>
            <div class="close-btn d-xl-none d-block sidebartoggler cursor-pointer" id="sidebarCollapse">
                <i class="fas fa-times fs-5 text-black"></i>
            </div>
        </div>
        <nav class="sidebar-nav scroll-sidebar" data-simplebar="">
            <ul id="sidebarnav">
                <li class="nav-small-cap">
                    <i class="ti ti-dots nav-small-cap-icon fs-4"></i>
                    <span class="hide-menu">Home</span>
                </li>
                <?= $this->include('layouts/partials/menu/dashboard'); ?>

                <li class="nav-small-cap">
                    <i class="ti ti-dots nav-small-cap-icon fs-4"></i>
                    <span class="hide-menu">MASTER DATA</span>
                </li>
                <?= $this->include('layouts/partials/menu/master/ekas/menu'); ?>

                <li class="nav-small-cap">
                    <i class="ti ti-dots nav-small-cap-icon fs-4"></i>
                    <span class="hide-menu">AKUN</span>
                </li>
                <?= $this->include('layouts/partials/menu/akun'); ?>
                <?= $this->include('layouts/partials/menu/absensi/menu'); ?>

                <li class="nav-small-cap">
                    <i class="ti ti-dots nav-small-cap-icon fs-4"></i>
                    <span class="hide-menu">Logistik</span>
                </li>
                <?= $this->include('layouts/partials/menu/logistik/menu') ?>

                <li class="nav-small-cap">
                    <i class="ti ti-dots nav-small-cap-icon fs-4"></i>
                    <span class="hide-menu">Pelanggan</span>
                </li>
                <?= $this->include('layouts/partials/menu/pelanggan') ?>

                <li class="nav-small-cap">
                    <i class="ti ti-dots nav-small-cap-icon fs-4"></i>
                    <span class="hide-menu">KAS</span>
                </li>
                <?= $this->include('layouts/partials/menu/kas/menu'); ?>

                <li class="nav-small-cap">
                    <i class="ti ti-dots nav-small-cap-icon fs-4"></i>
                    <span class="hide-menu">Laporan</span>
                </li>
                <?= $this->include('layouts/partials/menu/laporan') ?>
            </ul>
        </nav>
    </div>
</aside>