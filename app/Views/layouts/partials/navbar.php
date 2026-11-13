<?php
$sistemAktif  = sistem_aktif();
$sistemLain   = sistem_lain();
$daftarSistem = sistem_daftar();
$labelAktif   = $daftarSistem[$sistemAktif]['label'] ?? $sistemAktif;
$iconAktif    = $daftarSistem[$sistemAktif]['icon'] ?? 'fas fa-layer-group';
?>
<header class="app-header">
    <nav class="navbar navbar-expand-lg navbar-light">
        <ul class="navbar-nav">
            <li class="nav-item d-block d-xl-none">
                <a class="nav-link sidebartoggler nav-icon-hover" id="headerCollapse" href="javascript:void(0)">
                    <i class="fas fa-bars"></i>
                </a>
            </li>
        </ul>
        <div class="navbar-collapse justify-content-end px-0" id="navbarNav">
            <ul class="navbar-nav flex-row ms-auto align-items-center justify-content-end">
                <?php if (empty($sistemLain)): ?>
                    <?php // Hanya punya satu sistem: tampilkan penanda statis saja. ?>
                    <li class="nav-item me-3">
                        <span class="badge bg-dark fs-2 text-uppercase px-3 py-2">
                            <i class="<?= esc($iconAktif, 'attr') ?> me-1"></i>
                            <?= esc($labelAktif) ?>
                        </span>
                    </li>
                <?php else: ?>
                    <li class="nav-item dropdown me-3">
                        <a class="nav-link d-flex align-items-center gap-1 px-2" href="javascript:void(0)"
                            id="dropdownSistem" data-bs-toggle="dropdown" aria-expanded="false" title="Ganti modul">
                            <span class="badge bg-dark fs-2 text-uppercase px-3 py-2">
                                <i class="<?= esc($iconAktif, 'attr') ?> me-1"></i>
                                <?= esc($labelAktif) ?>
                            </span>
                            <i class="ti ti-chevron-down fs-4 text-black"></i>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end border-dark" aria-labelledby="dropdownSistem">
                            <li class="px-3 pt-2 pb-1">
                                <span class="fs-2 text-muted text-uppercase fw-semibold">Modul</span>
                            </li>
                            <li>
                                <a class="dropdown-item d-flex align-items-center gap-2 text-black"
                                    href="<?= sistem_home_url($sistemAktif) ?>">
                                    <i class="<?= esc($iconAktif, 'attr') ?>"></i>
                                    <span class="flex-grow-1"><?= esc($labelAktif) ?></span>
                                    <i class="fas fa-check text-success"></i>
                                </a>
                            </li>
                            <?php foreach ($sistemLain as $sistem): ?>
                                <li>
                                    <a class="dropdown-item d-flex align-items-center gap-2 text-black"
                                        href="<?= sistem_home_url($sistem) ?>">
                                        <i class="<?= esc($daftarSistem[$sistem]['icon'] ?? 'fas fa-right-left', 'attr') ?>"></i>
                                        <span class="flex-grow-1"><?= esc($daftarSistem[$sistem]['label'] ?? $sistem) ?></span>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </li>
                <?php endif; ?>
                <span class="text-black text-uppercase fw-bolder"><?= session('nama_lengkap') ?></span>
                <li class="nav-item dropdown">
                    <a class="nav-link nav-icon-hover" href="javascript:void(0)" id="drop2" data-bs-toggle="dropdown"
                        aria-expanded="false">
                        <img src="<?= base_url('assets/images/profile/user-1.jpg') ?>" alt="" width="35" height="35" class="rounded-circle">
                    </a>
                    <div class="dropdown-menu dropdown-menu-end dropdown-menu-animate-up border-dark" aria-labelledby="drop2">
                        <div class="message-body">
                            <a href="javascript:void(0)" class="d-flex align-items-center gap-2 dropdown-item text-black">
                                <i class="ti ti-user fs-6"></i>
                                <p class="mb-0 fs-3">My Profile</p>
                            </a>
                            <a href="/logout" class="btn btn-outline-danger mx-3 mt-2 d-block">Logout</a>
                        </div>
                    </div>
                </li>
            </ul>
        </div>
    </nav>
</header>
