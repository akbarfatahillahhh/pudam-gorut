<?php $uri = current_url(true); ?>
<li class="sidebar-item" id="absensi_menu">
    <a class="sidebar-link <?= ($uri->getSegment(1) == 'absensi' ? 'active' : '') ?>" href="/absensi" aria-expanded="false">
      <span>
            <i class="fa-solid fa-file-contract"></i>
        </span>
        <span class="hide-menu">Absensi</span>
        <i class="fas fa-times text-danger"></i>
    </a>
</li>