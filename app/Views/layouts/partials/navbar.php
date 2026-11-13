<?php
$sistemAktif = sistem_aktif();
$sistemLain  = sistem_lain();
$daftarSistem = sistem_daftar();
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
                <li class="nav-item me-3 d-none d-md-block">
                    <span class="badge bg-dark fs-2 text-uppercase">
                        <?= esc($daftarSistem[$sistemAktif]['label'] ?? $sistemAktif) ?>
                    </span>
                </li>
                <?php foreach ($sistemLain as $sistem): ?>
                    <li class="nav-item me-3">
                        <a class="btn btn-sm btn-outline-primary" href="<?= sistem_home_url($sistem) ?>">
                            <i class="<?= esc($daftarSistem[$sistem]['icon'] ?? 'fas fa-right-left', 'attr') ?> me-1"></i>
                            <?= esc($daftarSistem[$sistem]['label'] ?? $sistem) ?>
                        </a>
                    </li>
                <?php endforeach; ?>
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
