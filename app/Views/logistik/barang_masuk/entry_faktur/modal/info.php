<div class="modal fade" id="modal" tabindex="-1" aria-labelledby="modalLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content">
            <div class="modal-header border-bottom">
                <h1 class="modal-title fs-5" id="modalLabel">Info Entry <?= $title ?></h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="content-place">
                    <div class="mb-2">
                        <button class="btn btn-sm btn-danger" id="exportPdf">
                            <i class="fa fa-file-pdf"></i>
                            PDF
                        </button>
                        <button class="btn btn-sm btn-success" id="exportExcel">
                            <i class="fa fa-file-excel"></i>
                            Excel
                        </button>
                    </div>
                    <div class="row mx-0 mb-2">
                        <div class="col-4 fs-2 text-center text-black border border-dark">
                            NO FAKTUR
                            <br>
                            <strong><?= $faktur->no_faktur ?? '-' ?></strong>
                        </div>
                        <div class="col-4 fs-2 text-center text-black border border-dark">
                            TANGGAL
                            <br>
                            <strong><?= $faktur->tanggal ?? '-' ?></strong>
                        </div>
                        <div class="col-4 fs-2 text-center text-black border border-dark">
                            PENYEDIA
                            <br>
                            <strong><?= $faktur->penyedia ?? '-' ?></strong>
                        </div>
                    </div>
                    <div class="table-responsive" style="min-height: 200px; max-height: 400px; overflow-y: auto">
                        <table class="table table-sm table-bordered table-hover table-striped border-dark text-nowrap mb-0 align-middle">
                            <tr>
                                <th class="text-center align-middle text-black">#</th>
                                <th class="text-center align-middle text-black">BARANG</th>
                                <th class="text-center align-middle text-black">STOK AKHIR</th>
                                <th class="text-center align-middle text-black">QTY</th>
                                <th class="text-center align-middle text-black">TOTAL STOK</th>
                                <th class="text-center align-middle text-black">HARGA SATUAN</th>
                                <th class="text-center align-middle text-black">TOTAL HARGA</th>
                            </tr>
                            <?php $no = 1; ?>
                            <?php $totalStokAkhir = 0; ?>
                            <?php $totalStokDiterima = 0; ?>
                            <?php $totalStok = 0; ?>
                            <?php foreach ($data as $row) : ?>
                                <tr>
                                    <td class="text-center align-middle text-black fw-semibold"><?= $no++ ?></td>
                                    <td class="align-middle text-black fw-semibold"><?= $row->barang_nama ?></td>
                                    <td class="text-center align-middle text-black"><?= number_format($row->stok_awal, 0, '.', '.') ?></td>
                                    <td class="text-center align-middle text-black"><?= number_format($row->stok_diterima, 0, '.', '.') ?></td>
                                    <td class="text-center align-middle text-black"><?= number_format($row->jumlah_stok_akhir, 0, '.', '.') ?></td>
                                    <td class="text-end align-middle text-black fw-semibold fst-italic">Rp.<?= number_format($row->harga_satuan, 0, ',', '.') ?></td>
                                    <td class="text-end align-middle text-black fw-semibold fst-italic">Rp.<?= number_format($row->jumlah_harga, 0, ',', '.') ?></td>
                                </tr>
                                <?php $totalStokAkhir += $row->stok_awal; ?>
                                <?php $totalStokDiterima += $row->stok_diterima; ?>
                                <?php $totalStok += $row->jumlah_stok_akhir; ?>
                            <?php endforeach ?>
                            <tr>
                                <td colspan="2" class="text-uppercase fw-semibold text-black text-center">Jumlah</td>
                                <td class="fw-semibold text-black text-center"><?= number_format($totalStokAkhir, 0, '.', '.') ?? '0' ?></td>
                                <td class="fw-semibold text-black text-center"><?= number_format($totalStokDiterima, 0, '.', '.') ?? '0' ?></td>
                                <td class="fw-semibold text-black text-center"><?= number_format($totalStok, 0, '.', '.') ?? '0' ?></td>
                                <td class="fw-semibold text-black text-center"></td>
                                <td class="fw-semibold text-black text-end">Rp. <?= number_format($faktur->TOTAL_HARGA, 0, ',', '.') ?? '0' ?></td>
                            </tr>
                        </table>
                    </div>
                </div>
                <div id="pdf-place" style="display: none;">
                    <div class="mb-2">
                        <button class="btn btn-sm btn-dark" type="button" id="btnBackContent">
                            <i class="fa fa-long-arrow-left"></i>
                            Kembali
                        </button>
                    </div>
                    <div class="h-100 w-100">
                        <iframe src="" class="w-100" style="height: 90vh; overflow-y: auto;"></iframe>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        $('#exportPdf').click(function(e) {
            e.preventDefault();
            var no_faktur = '<?= $faktur->no_faktur ?>';

            $('#pdf-place').show();
            $('#content-place').hide();

            $('#pdf-place iframe').attr('src', '<?= base_url('Logistik/BarangMasuk/Penerimaan/export_pdf') ?>' + '/' + no_faktur);
        });

        $('#btnBackContent').click(function(e) {
            e.preventDefault();
            $('#pdf-place').hide();
            $('#content-place').show();
        });
    });
</script>