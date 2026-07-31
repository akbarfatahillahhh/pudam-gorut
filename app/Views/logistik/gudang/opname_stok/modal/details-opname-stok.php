<div class="modal fade" id="modal" tabindex="-1" aria-labelledby="modalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-bottom">
                <h1 class="modal-title fs-5 fw-semibold" id="modalLabel"><?= $referensi ?? '' ?></h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="content-place">
                    <div class="mb-2">
                        <button class="btn btn-sm btn-danger" type="button" id="exportPdf">
                            <i class="fa fa-file-pdf"></i>
                            PDF
                        </button>
                        <button class="btn btn-sm btn-success" id="exportExcel">
                            <i class="fa fa-file-excel"></i>
                            Excel
                        </button>
                    </div>
                    <table class="table table-bordered table-sm border border-dark">
                        <tbody>
                            <tr>
                                <td class="fw-semibold text-center text-black" width="50%">
                                    PENANGGUNG JAWAB
                                    <br>
                                    <?= $penanggung_jawab ?? '-' ?>
                                </td>
                                <td class="fw-semibold text-center text-black" width="50%">
                                    TANGGAL
                                    <br>
                                    <?= date('d/m/Y', strtotime($tanggal)) ?? '-' ?>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <div class="table-responsive" style="min-height: 200px; max-height: 400px; overflow-y: auto">
                        <table class="table table-sm table-hover table-bordered border border-dark" id="table-detail-stok-opname">
                            <thead class="bg-dark text-white">
                                <tr>
                                    <th class="text-center text-white py-1 fw-normal" width="5%">No</th>
                                    <th class="text-center text-white py-1 fw-normal" width="15%">Kode Barang</th>
                                    <th class="text-center text-white py-1 fw-normal">Nama Barang</th>
                                    <th class="text-center text-white py-1 fw-normal" width="15%">Stok Sebelumnya</th>
                                    <th class="text-center text-white py-1 fw-normal" width="15%">Stok Dicatat</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $no = 1; ?>
                                <?php foreach ($data as $detail) : ?>
                                    <tr>
                                        <th class="text-center py-1 text-black border-top border-dark" width="5%"><?= $no++ ?></th>
                                        <td class="text-center py-1 text-black border-top border-dark" width="15%"><?= $detail['barang_kode'] ?></td>
                                        <td class="py-1 text-black border-top border-dark"><?= $detail['barang_nama'] ?></td>
                                        <td class="text-center py-1 text-black border-top border-dark" width="15%"><?= number_format($detail['stok_sebelum'], 0, '.', '.') ?></td>
                                        <td class="text-center py-1 text-black border-top border-dark" width="15%"><?= number_format($detail['stok_diterima'], 0, '.', '.') ?></td>
                                    </tr>
                                <?php endforeach ?>
                            </tbody>
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
        $('#table-detail-stok-opname').DataTable({
            width: '100%',
            searching: false,
            paging: false
        });
    });

    $('#exportPdf').click(function(e) {
        e.preventDefault();
        var no_stok_opname = '<?= $referensi ?>';

        $('#pdf-place').show();
        $('#content-place').hide();

        $('#pdf-place iframe').attr('src', '<?= base_url('Logistik/Gudang/OpnameStok/export_pdf') ?>' + '/' + no_stok_opname);
    });

    $('#btnBackContent').click(function(e) {
        e.preventDefault();
        $('#pdf-place').hide();
        $('#content-place').show();
    });
</script>