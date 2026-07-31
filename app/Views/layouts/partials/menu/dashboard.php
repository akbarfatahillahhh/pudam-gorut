<?php $uri = current_url(true); ?>
<li class="sidebar-item" id="dashboard_menu">
    <a class="sidebar-link <?= ($uri->getSegment(1) == 'home' ? 'active' : '') ?>" href="/home" aria-expanded="false">
        <span>
            <i class="ti ti-layout-dashboard"></i>
            <i class="fas fa-home"></i>
        </span>
        <span class="hide-menu">Dashboard</span>
        <i class="fas fa-times text-danger"></i>
    </a>
</li>