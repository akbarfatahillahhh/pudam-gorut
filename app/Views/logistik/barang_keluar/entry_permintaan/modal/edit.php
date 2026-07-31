<div class="modal fade" id="modal" tabindex="-1" aria-labelledby="modalLabel" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content">
            <div class="modal-header border-bottom">
                <h1 class="modal-title fs-5" id="modalLabel">Detail <?= $title ?></h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">

                <div id="content">
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

                    <div class="row mx-0">
                        <div class="col text-black small py-1 border border-dark text-center">
                            NO. PERMINTAAN
                            <br>
                            <strong><?= $logistik_permintaan['no_permintaan'] ?></strong>
                        </div>
                        <div class="col text-black small py-1 border border-dark text-center">
                            KARYAWAN
                            <br>
                            <strong><?= $logistik_permintaan['penerima'] ?></strong>
                        </div>
                        <div class="col text-black small py-1 border border-dark text-center">
                            TANGGAL PERMINTAAN
                            <br>
                            <strong><?= $logistik_permintaan['tanggal'] ?></strong>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table id="table" class="table table-sm table-bordered table-striped border-dark text-nowrap mb-0 align-middle mt-2">
                            <thead>
                                <tr>
                                    <th class="text-center text-black" width="3%">#</th>
                                    <th class="text-center text-black" width="40%">Barang</th>
                                    <th class="text-center text-black" width="5%">Satuan</th>
                                    <th class="text-center text-black" width="10%">Stok Akhir</th>
                                    <th class="text-center text-black" width="10%">Jml. Permintaan</th>
                                    <th class="text-center text-black" width="10%">Jml. Diberikan</th>
                                    <th class="text-center text-black" width="10%">Status</th>
                                </tr>
                            </thead>
                            <tbody id="tableBody">
                                <?php $totalStokDiminta = 0 ?>
                                <?php $totalStokDiberikan = 0 ?>
                                <?php foreach ($logistik_permintaan_tampung as $key => $value) : ?>
                                    <tr>
                                        <td class="text-center text-black"><?= ($key + 1) ?></td>
                                        <td class="text-black"><?= $value['barang_nama'] ?></td>
                                        <td class="text-center text-black"><?= $value['satuan'] ?></td>
                                        <td class="text-center text-black"><?= number_format($value['stok_akhir'], 0, ',', '.') ?></td>
                                        <td class="text-center text-black"><?= number_format($value['stok_diminta'], 0, ',', '.') ?></td>
                                        <td class="text-center text-black"><?= number_format($value['stok_diberikan'], 0, ',', '.') ?></td>
                                        <td class="text-center text-black">
                                            <?php if ($value['status'] == 'Y') : ?>
                                                <span class="badge fs-1 bg-success">DISETUJUI</span>
                                            <?php elseif ($value['status'] == 'N') : ?>
                                                <span class="badge fs-1 bg-danger">DITOLAK</span>
                                            <?php else : ?>
                                                <span class="badge fs-1 bg-warning">PENDING</span>
                                            <?php endif ?>
                                        </td>
                                    </tr>
                                    <?php $totalStokDiminta += $value['stok_diminta'] ?>
                                    <?php $totalStokDiberikan += $value['stok_diberikan'] ?>
                                <?php endforeach; ?>
                                <tr>
                                    <td colspan="4" class="text-center text-black fw-bolder">Total Keseluruhan</td>
                                    <td class="text-center text-black fw-bolder"><?= number_format($totalStokDiminta, 0, ',', '.') ?></td>
                                    <td class="text-center text-black fw-bolder"><?= number_format($totalStokDiberikan, 0, ',', '.') ?></td>
                                    <td></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div id="pdf" style="display: none">
                    <div class="mb-2">
                        <button class="btn btn-sm btn-dark" id="btnBackContent">
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
            var no_permintaan = '<?= $logistik_permintaan['no_permintaan'] ?>';
            $('#pdf').show();
            $('#content').hide();
            $('#pdf iframe').attr('src', '<?= base_url('Logistik/BarangKeluar/Permintaan/export_pdf') ?>' + '/' + no_permintaan);
        });

        $('#btnBackContent').click(function(e) {
            e.preventDefault();
            $('#pdf').hide();
            $('#content').show();
        });
    });
</script>