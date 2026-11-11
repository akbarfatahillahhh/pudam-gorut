<div class="modal fade" id="modal" tabindex="-1" aria-labelledby="modalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered h-95">
        <div class="modal-content h-95">
            <div class="modal-header border-bottom">
                <h1 class="modal-title fs-5" id="modalLabel"><?= $no_permintaan ?></h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="h-100 w-100">
                    <iframe src="<?= base_url('Logistik/Gudang/CekPermintaan/export_pdf') ?>/<?= $no_permintaan ?>" class="h-100 w-100"></iframe>
                </div>
            </div>
        </div>
    </div>
</div>