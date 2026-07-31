<?php $uri = current_url(true); ?>
<li class="sidebar-item" id="pelanggan_menu" style="display: none">
    <a class="sidebar-link <?= ($uri->getSegment(2) == 'pelanggan' ? 'active' : '') ?>" href="/pelanggan" aria-expanded="false">
        <span>
            <i class="fas fa-users"></i>
        </span>
        <span class="hide-menu">Pelanggan</span>
        <i class="fas fa-times text-danger"></i>
    </a>
</li>