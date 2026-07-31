<?php $uri = current_url(true); ?>
<li class="sidebar-item" id="akun_menu">
    <a class="sidebar-link <?= ($uri->getSegment(2) == 'akun' ? 'active' : '') ?>" href="/master/akun" aria-expanded="false">
        <span>
            <i class="fas fa-user-cog"></i>
        </span>
        <span class="hide-menu">User/Akun</span>
        <i class="fas fa-check text-success"></i>
    </a>
</li>