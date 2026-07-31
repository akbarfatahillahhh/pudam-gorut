<?php $uri = current_url(true); ?>
<li class="sidebar-item d-none" id="bank_menu">
    <a class="sidebar-link <?= ($uri->getSegment(2) == 'bank' ? 'active' : '') ?>" href="/master/bank"
        aria-expanded="false">
        <span>
            <i class="fas fa-bank"></i>
        </span>
        <span class="hide-menu">Master Bank</span>
        <i class="fas fa-check text-success"></i>
    </a>
</li>