<?php $uri = current_url(true); ?>
<li class="sidebar-item">
    <a class="sidebar-link pointer <?= in_array($uri->getSegment(2), ['kabupaten', 'kecamatan', 'kelurahan']) ? 'active' : '' ?>" data-bs-toggle="collapse" data-bs-target="#collapseWilayah" aria-expanded="false" aria-controls="collapseWilayah">
        <span>
            <i class="fas fa-location-dot"></i>
        </span>
        <span class="hide-menu">Master Wilayah</span>
        <i class="fas fa-check text-success"></i>
    </a>
    <div class="collapse ps-3 <?= in_array($uri->getSegment(2), ['kabupaten', 'kecamatan', 'kelurahan']) ? 'show' : '' ?>" id="collapseWilayah">
        <ul class="list-group list-group-flush">
            <li class="list-group-item" id="kabupaten_menu">
                <a href="/master/kabupaten" class="pointer <?= ($uri->getSegment(2) == 'kabupaten') ? 'text-primary' : 'text-dark' ?>">
                    <i class="fas fa-location-dot pe-2"></i>
                    Kabupaten
                    <i class="fas fa-check text-success"></i>
                </a>
            </li>
            <li class="list-group-item" id="kecematan_menu">
                <a href="/master/kecamatan" class="pointer <?= ($uri->getSegment(2) == 'kecamatan') ? 'text-primary' : 'text-dark' ?>">
                    <i class="fas fa-location-dot pe-2"></i>
                    Kecamatan
                    <i class="fas fa-check text-success"></i>
                </a>
            </li>
            <li class="list-group-item" id="kelurahan_menu">
                <a href="/master/kelurahan" class="pointer <?= ($uri->getSegment(2) == 'kelurahan') ? 'text-primary' : 'text-dark' ?>">
                    <i class="fas fa-location-dot pe-2"></i>
                    Kelurahan
                    <i class="fas fa-check text-success"></i>
                </a>
            </li>
        </ul>
    </div>
</li>