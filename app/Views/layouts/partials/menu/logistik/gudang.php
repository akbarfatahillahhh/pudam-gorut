<?php $uri = current_url(true); ?>
<li class="sidebar-item">
    <a class="sidebar-link pointer <?= in_array($uri->getSegment(1), ['gudang']) ? 'active show' : '' ?>" data-bs-toggle="collapse" data-bs-target="#collapseGudang" aria-expanded="false" aria-controls="collapseGudang">
        <span>
            <i class="fas fa-boxes"></i>
        </span>
        <span class="hide-menu">Gudang</span>
        <i class="fas fa-check text-success"></i>
    </a>
    <div class="collapse ps-3 <?= in_array($uri->getSegment(1), ['gudang']) ? 'show' : '' ?>" id="collapseGudang">
        <ul class="list-group list-group-flush">
            <li class="list-group-item">
                <a class="pointer  <?= $uri->getSegment(2) == 'barang' ? 'text-primary' : 'text-dark' ?>" href="<?= base_url('gudang/barang') ?>">
                    <i class="fas fa-boxes-packing pe-2"></i>
                    Data Barang
                    <i class="fas fa-check text-success"></i>
                </a>
            </li>
            <li class="list-group-item">
                <a class="pointer  <?= $uri->getSegment(2) == 'cek-permintaan' ? 'text-primary' : 'text-dark' ?>" href="<?= base_url('gudang/cek-permintaan') ?>">
                    <i class="fas fa-clipboard-check pe-2"></i>
                    Cek Permintaan
                    <i class="fas fa-check text-success"></i>
                </a>
            </li>
            <li class="list-group-item">
                <a class="pointer  <?= $uri->getSegment(2) == 'opname-stok' ? 'text-primary' : 'text-dark' ?>" href="<?= base_url('gudang/opname-stok') ?>">
                    <i class="fas fa-clipboard-list  pe-2"></i>
                    Opname Stok
                    <i class="fas fa-check text-success"></i>
                </a>
            </li>
        </ul>
    </div>
</li>