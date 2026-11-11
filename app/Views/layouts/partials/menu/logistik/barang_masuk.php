<?php $uri = current_url(true); ?>
<li class="sidebar-item" id="faktur_pembelian_menu">
    <a class="sidebar-link <?= ($uri->getSegment(1) == 'faktur-pembelian' ? 'active' : '') ?>" href="/faktur-pembelian" aria-expanded="false">
        <span>
            <i class="fa-solid fa-right-to-bracket"></i>
        </span>
        Faktur Pembelian
        <i class="fas fa-check text-success"></i>
    </a>
</li>