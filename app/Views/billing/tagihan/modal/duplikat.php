<div class="modal fade" id="modalDuplikat" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-bottom">
                <h5 class="modal-title fw-semibold">Rekening <?= esc($no_rek) ?> &mdash; <?= count($rows) ?> pelanggan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive" style="max-height: 65vh; overflow:auto;">
                    <table class="table table-sm table-bordered border-dark align-middle mb-0">
                        <thead class="text-black fs-2">
                            <tr>
                                <th class="text-center" width="4%">#</th>
                                <th class="text-center" width="14%">NO SAMBUNGAN</th>
                                <th>PELANGGAN</th>
                                <th width="24%">ALAMAT</th>
                                <th class="text-center" width="14%">TAGIHAN</th>
                                <th class="text-center" width="10%">STATUS</th>
                            </tr>
                        </thead>
                        <tbody class="text-black fs-2">
                            <?php foreach ($rows as $i => $r): ?>
                                <tr>
                                    <td class="text-center"><?= $i + 1 ?></td>
                                    <td class="text-center"><?= esc($r['no_sambungan'] ?? '-') ?></td>
                                    <td class="text-uppercase"><?= esc($r['nama_pelanggan'] ?? '-') ?></td>
                                    <td><?= esc($r['alamat'] ?? '-') ?></td>
                                    <td class="text-end">Rp <?= number_format((float) $r['jlh_tagihan'], 0, ',', '.') ?></td>
                                    <td class="text-center">
                                        <?= ($r['status'] ?? '0') === '1'
                                            ? '<span class="badge bg-success">Lunas</span>'
                                            : '<span class="badge bg-danger">Menunggak</span>' ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
