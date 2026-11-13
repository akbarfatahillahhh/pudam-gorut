<?php
/**
 * Sidebar generik untuk kedua sistem (billing / e-kas).
 *
 * Struktur menu diambil dari App\Helpers\sistem_helper.php -> sistem_menu(),
 * jadi menu tidak lagi ditulis manual per partial dan tidak ada lagi menu
 * yang dobel di dua sistem.
 *
 * Variabel opsional:
 *   $sistem : paksa sistem tertentu ('billing'|'kas'). Kalau kosong,
 *             dipakai sistem_aktif() berdasarkan URI.
 */
$sistem = $sistem ?? sistem_aktif();
$level  = session('level');
$path   = strtolower(trim(service('request')->getUri()->getPath(), '/'));

$aktif = static function (string $prefix) use ($path): bool {
    foreach (explode('|', $prefix) as $p) {
        $p = strtolower(trim($p, '/'));

        if ($p !== '' && ($path === $p || strpos($path, $p . '/') === 0)) {
            return true;
        }
    }

    return false;
};
?>
<style>
    /* Ikon submenu pakai Tabler, samakan ukuran dengan menu utama. */
    .sidebar-nav .list-group-item .ti {
        font-size: 18px;
        vertical-align: -3px;
    }

    .sidebar-nav .list-group-item i {
        width: 18px;
        text-align: center;
    }
</style>
<aside class="left-sidebar border-dark">
    <div>
        <div class="brand-logo d-flex align-items-center justify-content-between">
            <a href="/home" class="text-nowrap logo-img">
                <img src="<?= base_url() ?>/logo/perumda-gorut.png" width="40" alt="PERUMDA GORUT" class="me-1" />
                <span class="h6 fw-bolder align-middle">PERUMDA</span>
                <span class="h6 fw-bolder align-middle">GORUT</span>
            </a>
            <div class="close-btn d-xl-none d-block sidebartoggler cursor-pointer" id="sidebarCollapse">
                <i class="ti ti-x fs-5 text-black"></i>
            </div>
        </div>
        <nav class="sidebar-nav scroll-sidebar" data-simplebar="">
            <ul id="sidebarnav">
                <?php foreach (sistem_menu($sistem) as $grup): ?>
                    <?php
                    // Data Master & Administrasi hanya untuk admin.
                    if (!empty($grup['admin']) && $level !== 'admin') {
                        continue;
                    }
                    ?>
                    <li class="nav-small-cap">
                        <i class="ti ti-dots nav-small-cap-icon fs-4"></i>
                        <span class="hide-menu"><?= esc($grup['judul']) ?></span>
                    </li>

                    <?php foreach ($grup['items'] as $item): ?>
                        <?php if (!empty($item['children'])): ?>
                            <?php $buka = $aktif($item['prefix'] ?? ''); ?>
                            <li class="sidebar-item">
                                <a class="sidebar-link pointer <?= $buka ? 'active' : '' ?>"
                                    data-bs-toggle="collapse" data-bs-target="#collapse-<?= esc(md5($item['label']), 'attr') ?>"
                                    aria-expanded="<?= $buka ? 'true' : 'false' ?>"
                                    aria-controls="collapse-<?= esc(md5($item['label']), 'attr') ?>">
                                    <span><i class="<?= esc($item['icon'], 'attr') ?>"></i></span>
                                    <span class="hide-menu"><?= esc($item['label']) ?></span>
                                </a>
                                <div class="collapse ps-3 <?= $buka ? 'show' : '' ?>" id="collapse-<?= esc(md5($item['label']), 'attr') ?>">
                                    <ul class="list-group list-group-flush">
                                        <?php foreach ($item['children'] as $anak): ?>
                                            <li class="list-group-item">
                                                <a href="<?= base_url($anak['url']) ?>"
                                                    class="pointer <?= $aktif($anak['prefix'] ?? $anak['url']) ? 'text-primary' : 'text-dark' ?>">
                                                    <i class="<?= esc($anak['icon'] ?? 'ti ti-point', 'attr') ?> pe-2"></i>
                                                    <?= esc($anak['label']) ?>
                                                </a>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            </li>
                        <?php else: ?>
                            <li class="sidebar-item" <?= !empty($item['id']) ? 'id="' . esc($item['id'], 'attr') . '"' : '' ?>>
                                <a class="sidebar-link <?= $aktif($item['prefix'] ?? $item['url']) ? 'active' : '' ?>"
                                    href="<?= base_url($item['url']) ?>" aria-expanded="false">
                                    <span><i class="<?= esc($item['icon'], 'attr') ?>"></i></span>
                                    <span class="hide-menu"><?= esc($item['label']) ?></span>
                                </a>
                            </li>
                        <?php endif; ?>
                    <?php endforeach; ?>
                <?php endforeach; ?>
            </ul>
        </nav>
    </div>
</aside>
