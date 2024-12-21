<div class="modal fade" id="modal" tabindex="-1" aria-labelledby="modalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-bottom">
                <h1 class="modal-title fs-5" id="modalLabel">Tambah <?= $title ?></h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="form-insert">
                    <div class="row">
                        <div class="col-lg-5 mb-2">
                            <label for="" class="form-label">Grup Perkiraan</label>
                            <select name="" id="" class="form-control">
                                <option value="">[ Pilih Grup Perkiraan ]</option>
                            </select>
                        </div>
                        <div class="col-lg-7 mb-2">
                            <label for="" class="form-label">Perkiraan</label>
                            <input type="text" class="form-control" placeholder="Perkiraan">
                        </div>

                        <div class="col-lg-4 mb-2">
                            <label for="" class="form-label">Nomor Perkiraan</label>
                            <input type="text" class="form-control" placeholder="Nomor">
                        </div>
                        <div class="col-lg-5 mb-2">
                            <label for="" class="form-label">Normal</label>
                            <select name="" id="" class="form-control">
                                <option value="">[ Pilih Normal ]</option>
                            </select>
                        </div>
                        <div class="col-lg-3 mb-2">
                            <label for="" class="form-label">Status</label>
                            <select name="" id="" class="form-control">
                                <option value="">[ Pilih Status ]</option>
                            </select>
                        </div>
                    </div>
                    <div class="border-top text-end pt-3 mt-4">
                        <button type="submit" class="btn btn-sm btn-success">
                            <i class="fa fa-save"></i>
                            Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>