<div class="modal fade" id="modal" tabindex="-1"  data-bs-backdrop="static" aria-labelledby="modalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-bottom">
                <h1 class="modal-title fs-5" id="modalLabel"><?= $NAMA ?></h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <table class="table table-sm table-hover table-bordered border-dark text-black">
                    <tbody>
                        <tr>
                            <th scope="row" width="25%">NIK</th>
                            <td>: <?= $NIK ?></td>
                        </tr>
                        <tr>
                            <th scope="row" width="25%">Nama</th>
                            <td>: <?= $NAMA ?></td>
                        </tr>
                        <tr>
                            <th scope="row" width="25%">Tempat Lahir</th>
                            <td>: <?= $TEMPAT_LAHIR ?></td>
                        </tr>
                        <tr>
                            <th scope="row" width="25%">Tanggal Lahir</th>
                            <td>: <?= $TGL_LAHIR ?></td>
                        </tr>
                        <tr>
                            <th scope="row" width="25%">Kelamin</th>
                            <td>: <?= ($KELAMIN == 'L' ? 'Laki-laki' : 'Perempuan') ?? '-' ?></td>
                        </tr>
                        <tr>
                            <th scope="row" width="25%">Alamat</th>
                            <td>: <?= $ALAMAT ?></td>
                        </tr>
                        <tr>
                            <th scope="row" width="25%">Status</th>
                            <td>: <?= $STATUS ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>