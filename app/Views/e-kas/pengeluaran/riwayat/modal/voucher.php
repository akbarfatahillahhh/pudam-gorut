<div class="modal fade" id="modal" tabindex="-1" aria-labelledby="modalLabel" aria-hidden="true"
    data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-<?= $color ?> border-bottom">
                <h1 class="modal-title fs-5 text-white text-uppercase" id="modalLabel">Voucher <?= $res->NOMOR ?></h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body h-100">
                <iframe src="<?= base_url('E_Kas/Pengeluaran/EntryKas_Keluar/voucer_pdf') ?>/<?= $nomor ?>"
                    class="w-100" style="height: calc(100vh - 220px); border: none;"></iframe>
            </div>
        </div>
    </div>
</div>