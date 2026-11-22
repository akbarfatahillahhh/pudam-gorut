<div class="modal fade" id="modalRekening" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-bottom">
                <h5 class="modal-title fw-semibold">Rekening Pelanggan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive" style="max-height:60vh;overflow:auto;">
                    <table class="table table-sm table-bordered border-dark align-middle mb-0">
                        <thead class="text-black fs-2">
                            <tr>
                                <th class="text-center" width="12%">REKENING</th>
                                <th class="text-center" width="18%">PERIODE</th>
                                <th class="text-center" width="20%">JUMLAH</th>
                                <th class="text-center" width="14%">STATUS</th>
                                <th class="text-center" width="20%">TGL BAYAR</th>
                            </tr>
                        </thead>
                        <tbody class="text-black fs-2">
                            <?php if (empty($rows)): ?>
                                <tr>
                                    <td colspan="5" class="text-center">Belum ada rekening.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($rows as $r): ?>
                                    <tr>
                                        <td class="text-center"><?= esc($r['no_rek'] ?? '-') ?></td>
                                        <td class="text-center">
                                            <?= $r['bulan_tagihan'] ? date('M Y', strtotime($r['bulan_tagihan'])) : '-' ?>
                                        </td>
                                        <td class="text-end">Rp <?= number_format((float) $r['jlh_tagihan'], 0, ',', '.') ?></td>
                                        <td class="text-center">
                                            <?= ($r['status'] ?? '0') === '1'
                                                ? '<span class="badge bg-success">Lunas</span>'
                                                : '<span class="badge bg-danger">Menunggak</span>' ?>
                                        </td>
                                        <td class="text-center">
                                            <?= $r['tgl_bayar'] ? date('d/m/Y', strtotime($r['tgl_bayar'])) : '-' ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
