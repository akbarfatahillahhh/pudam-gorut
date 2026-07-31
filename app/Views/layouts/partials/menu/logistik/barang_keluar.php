<?php $uri = current_url(true); ?>
<li class="sidebar-item" id="entry_permintaan_menu">
    <a class="sidebar-link <?= ($uri->getSegment(2) == 'permintaan' ? 'active' : '') ?>" href="/barang-keluar/permintaan" aria-expanded="false">
        <span>
            <i class="fa-solid fa-right-from-bracket"></i>
        </span>
        Entry Permintaan
        <i class="fas fa-check text-success"></i>
    </a>
</li>