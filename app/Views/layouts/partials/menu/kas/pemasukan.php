<?php $uri = current_url(true); ?>
<li class="sidebar-item">
    <a class="sidebar-link pointer <?= in_array($uri->getSegment(2), ['air', 'non-air']) ? 'active' : '' ?>"
        data-bs-toggle="collapse" data-bs-target="#collapseEkasP" aria-expanded="false" aria-controls="collapseEkasP">
        <i class="fa fa-arrow-right"></i>
        <span>Kas Masuk</span>
        <i class="fas fa-times text-danger"></i>
    </a>
    <div class="collapse ps-3 <?= in_array($uri->getSegment(2), ['air', 'non-air']) ? 'show' : '' ?>" id="collapseEkasP">
        <ul class="list-group list-group-flush">
            <li class="list-group-item">
                <a href="<?= base_url('e-kas/entry') ?>"
                    class="pointer <?= $uri->getSegment(2) == 'perkiraan' ? 'text-primary' : 'text-dark' ?>">
                    <i class="fa-solid fa-droplet"></i>
                    Penerimaan Kas Air
                    <i class="fas fa-check text-success"></i>
                </a>
            </li>
            <li class="list-group-item">
                <a href="<?= base_url('e-kas/riwayat') ?>"
                    class="pointer <?= $uri->getSegment(2) == 'jabatan' ? 'text-primary' : 'text-dark' ?>">
                    <i class="fa-solid fa-droplet-slash"></i>
                    Penerimaan Kas Non Air
                    <i class="fa fa-check text-success"></i>
                </a>
            </li>
        </ul>
    </div>
</li>