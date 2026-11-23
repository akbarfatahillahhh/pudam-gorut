<div class="modal fade" id="modalRincian" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-xl modal-dialog-centered" style="height:90vh;">
        <div class="modal-content" style="height:90vh;">
            <div class="modal-header border-bottom">
                <div>
                    <h5 class="modal-title fw-semibold">Rincian Penagihan</h5>
                    <div class="fs-2 text-muted"><?= esc($awal) ?> s/d <?= esc($akhir) ?></div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive" style="max-height:65vh;overflow:auto;">
                    <table class="table table-sm table-bordered border-dark align-middle mb-0">
                        <thead class="text-black fs-2">
                            <tr>
                                <th class="text-center" width="4%">#</th>
                                <th class="text-center" width="12%">TGL BAYAR</th>
                                <th class="text-center" width="10%">REKENING</th>
                                <th>PELANGGAN</th>
                                <th width="16%">WILAYAH</th>
                                <th class="text-center" width="11%">PERIODE</th>
                                <th class="text-center" width="12%">JUMLAH</th>
                            </tr>
                        </thead>
                        <tbody class="text-black fs-2">
                            <?php if (empty($rows)): ?>
                                <tr>
                                    <td colspan="7" class="text-center">Tidak ada tagihan pada periode ini.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($rows as $i => $r): ?>
                                    <tr>
                                        <td class="text-center"><?= $i + 1 ?></td>
                                        <td class="text-center">
                                            <?= $r['tgl_bayar'] ? date('d/m/Y', strtotime($r['tgl_bayar'])) : '-' ?>
                                        </td>
                                        <td class="text-center"><?= esc($r['no_rek'] ?? '-') ?></td>
                                        <td>
                                            <b><?= esc($r['no_sambungan'] ?? '-') ?></b>
                                            <span class="text-uppercase"><?= esc($r['nama_pelanggan'] ?? '-') ?></span>
                                        </td>
                                        <td><?= esc(trim(($r['kecamatan'] ?? '') . ' / ' . ($r['kelurahan'] ?? ''), ' /')) ?></td>
                                        <td class="text-center">
                                            <?= $r['bulan_tagihan'] ? date('M Y', strtotime($r['bulan_tagihan'])) : '-' ?>
                                        </td>
                                        <td class="text-end">Rp <?= number_format((float) $r['jlh_tagihan'], 0, ',', '.') ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                        <?php if (! empty($rows)): ?>
                            <tfoot class="text-black fs-2 fw-semibold">
                                <tr>
                                    <td colspan="6" class="text-end">Total</td>
                                    <td class="text-end">Rp <?= number_format($total, 0, ',', '.') ?></td>
                                </tr>
                            </tfoot>
                        <?php endif; ?>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
