<?php $uri = current_url(true); ?>
<li class="sidebar-item">
    <a class="sidebar-link pointer <?= in_array($uri->getSegment(2), ['set-kelompok-tarif', 'set-jenis-pelanggan', 'pelanggan']) ? 'active' : '' ?>" data-bs-toggle="collapse" data-bs-target="#collapseBS" aria-expanded="false" aria-controls="collapseBS">
        <span>
            <i class="fas fa-users"></i>
        </span>
        <span class="hide-menu">Master Pelanggan</span>
        <i class="fas fa-check text-success"></i>
    </a>
    <div class="collapse ps-3 <?= in_array($uri->getSegment(2), ['set-kelompok-tarif', 'set-jenis-pelanggan', 'pelanggan']) ? 'show' : '' ?>" id="collapseBS">
        <ul class="list-group list-group-flush">
            <li class="list-group-item" id="pelanggan_menu">
                <a class="pointer <?= $uri->getSegment(2) == 'pelanggan' ? 'text-primary' : 'text-dark' ?>" href="/master/pelanggan">
                    <i class="fas fa-cogs pe-2"></i>
                    Daftar Pelanggan
                    <i class="fas fa-check text-success"></i>
                </a>
            </li>
            <li class="list-group-item" id="kelompok_tarif_menu">
                <a class="pointer <?= $uri->getSegment(2) == 'set-kelompok-tarif' ? 'text-primary' : 'text-dark' ?>" href="/master/set-kelompok-tarif">
                    <i class="fas fa-cogs pe-2"></i>
                    Kelompok Tarif
                    <i class="fas fa-check text-success"></i>
                </a>
            </li>
            <li class="list-group-item" id="jenis_pelanggan_menu">
                <a class="pointer <?= $uri->getSegment(2) == 'set-jenis-pelanggan' ? 'text-primary' : 'text-dark' ?>" href="/master/set-jenis-pelanggan">
                    <i class="fas fa-users pe-2"></i>
                    Jenis Pelanggan
                    <i class="fas fa-check text-success"></i>
                </a>
            </li>
        </ul>
    </div>
</li>