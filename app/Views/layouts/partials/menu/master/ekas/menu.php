<?php $uri = current_url(true); ?>
<li class="sidebar-item">
    <a class="sidebar-link pointer <?= in_array($uri->getSegment(2), ['perkiraan', 'jabatan', 'pokok', 'pegawai']) ? 'active' : '' ?>" data-bs-toggle="collapse" data-bs-target="#collapseEkas" aria-expanded="false" aria-controls="collapseEkas">
        <i class="fas fa-box"></i>
        <span class="hide-menu">Master Data</span>
        <i class="fas fa-check text-success"></i>
    </a>
    <div class="collapse ps-3 <?= in_array($uri->getSegment(2), ['perkiraan', 'jabatan', 'pokok', 'pegawai']) ? 'show' : '' ?>" id="collapseEkas">
        <ul class="list-group list-group-flush">
            <li class="list-group-item" id="perkiraan_menu" style="display: none-off">
                <a href="<?= base_url('master/perkiraan') ?>" class="pointer <?= $uri->getSegment(2) == 'perkiraan' ? 'text-primary' : 'text-dark' ?>">
                    <i class="fas fa-box pe-2"></i>
                    Perkiraan
                    <i class="fas fa-check text-success"></i>
                </a>
            </li>
            <li class="list-group-item" id="jabatan_menu"  style="display: none-off">
                <a href="<?= base_url('master/jabatan') ?>" class="pointer <?= $uri->getSegment(2) == 'jabatan' ? 'text-primary' : 'text-dark' ?>">
                    <i class="fas fa-box pe-2"></i>
                    Jabatan
                    <i class="fas fa-check text-success"></i>
                </a>
            </li>
            <li class="list-group-item" id="pokok_menu"  style="display: none-off">
                <a href="<?= base_url('master/pokok') ?>" class="pointer <?= $uri->getSegment(2) == 'pokok' ? 'text-primary' : 'text-dark' ?>">
                    <i class="fas fa-box pe-2"></i>
                    Tugas Pokok
                    <i class="fas fa-check text-success"></i>
                </a>
            </li>
            <li class="list-group-item" id="data_pegawai_menu"  style="display: none-off">
                <a href="<?= base_url('master/pegawai') ?>" class="pointer <?= $uri->getSegment(2) == 'pegawai' ? 'text-primary' : 'text-dark' ?>">
                    <i class="fas fa-box pe-2"></i>
                    Data Pegawai
                    <i class="fas fa-check text-success"></i>
                </a>
            </li>
        </ul>
    </div>
</li>