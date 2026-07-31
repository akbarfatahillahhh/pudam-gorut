<?php $uri = current_url(true); ?>
<li class="sidebar-item">
    <a class="sidebar-link pointer <?= in_array($uri->getSegment(2), ['keluar', 'riwayat']) ? 'active' : '' ?>"
        data-bs-toggle="collapse" data-bs-target="#collapseEkasPengeluaran" aria-expanded="false"
        aria-controls="collapseEkasPengeluaran">
        <i class="fa fa-arrow-left"></i>
        <span>Kas Keluar</span>
        <i class="fas fa-times text-danger"></i>
    </a>
    <div class="collapse ps-3 <?= in_array($uri->getSegment(2), ['keluar', 'riwayat']) ? 'show' : '' ?>"
        id="collapseEkasPengeluaran">
        <ul class="list-group list-group-flush">
            <li class="list-group-item" id="">
                <a href="<?= base_url('e-kas/keluar') ?>"
                    class="pointer <?= $uri->getSegment(2) == 'keluar' ? 'text-primary' : 'text-dark' ?>">
                    <i class="fa-solid fa-money-bill-transfer"></i>
                    Kas Pengeluaran
                    <i class="fas fa-times text-danger"></i>
                </a>
            </li>
            <li class="list-group-item">
                <a href="<?= base_url('e-kas/riwayat') ?>"
                    class="pointer <?= $uri->getSegment(2) == 'riwayat' ? 'text-primary' : 'text-dark' ?>">
                    <i class="fas fa-history pe-2"></i>
                    Riwayat Kas Pengeluaran
                    <i class="fas fa-times text-danger"></i>
                </a>
            </li>
        </ul>
    </div>
</li>