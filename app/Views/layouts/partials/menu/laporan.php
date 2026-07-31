<?php $uri = current_url(true); ?>
<li class="sidebar-item">
    <a class="sidebar-link  <?= $uri->getSegment(2) == 'drd-air' ? 'active' : '' ?>"
        href="/laporan/drd-air" aria-expanded="false">
        <span>
            <i class="fa-solid fa-chart-column"></i>
        </span>
        <span class="hide-menu">DRD Air</span>
        <i class="fas fa-check text-success"></i>
    </a>
</li>
<li class="sidebar-item">
    <a class="sidebar-link  <?= $uri->getSegment(2) == 'rekap-drd-air' ? 'active' : '' ?>"
        href="/laporan/rekap-drd-air" aria-expanded="false">
        <span>
            <i class="fa-solid fa-chart-column"></i>
        </span>
        <span class="hide-menu">DRD Unit/Jenis</span>
        <i class="fas fa-check text-success"></i>
    </a>
</li>
<li class="sidebar-item">
    <a class="sidebar-link <?= $uri->getSegment(2) == 'rincian-piutang-perunit' ? 'active' : '' ?>"
        href="/laporan/rincian-piutang-perunit" aria-expanded="false">
        <span>
            <i class="fa-solid fa-chart-column"></i>
        </span>
        <span class="hide-menu">Rincian Piutang Perunit</span>
        <i class="fas fa-check text-success"></i>
    </a>
</li>
<li class="sidebar-item">
    <a class="sidebar-link <?= $uri->getSegment(2) == 'pelanggan-duplikat' ? 'active' : '' ?>"
        href="/laporan/pelanggan-duplikat" aria-expanded="false">
        <span>
            <i class="fa-solid fa-chart-column"></i>
        </span>
        <span class="hide-menu">Pelanggan Duplikat</span>
        <i class="fas fa-check text-success"></i>
    </a>
</li>
<li class="sidebar-item">
    <a class="sidebar-link <?= $uri->getSegment(2) == 'rekapitulasi-piutang' ? 'active' : '' ?>"
        href="/laporan/rekapitulasi-piutang" aria-expanded="false">
        <span>
            <i class="fa-solid fa-chart-column"></i>
        </span>
        <span class="hide-menu">Rekapitulas Piutang</span>
        <i class="fas fa-check text-success"></i>
    </a>
</li>
<li class="sidebar-item">
    <a class="sidebar-link <?= $uri->getSegment(2) == 'rekap-persediaan' ? 'active' : '' ?>"
        href="/laporan/rekap-persediaan" aria-expanded="false">
        <span>
            <i class="fa-solid fa-chart-column"></i>
        </span>
        <span class="hide-menu">Rekap Persediaan</span>
        <i class="fas fa-times text-danger"></i>
    </a>
</li>