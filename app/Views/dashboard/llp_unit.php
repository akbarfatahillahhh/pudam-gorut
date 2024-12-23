<div>
    <div class="row">
        <div class="col-lg-2">
            <label for="" class="form-label">Periode</label>
            <input type="date" name="" id="" class="form-control" value="<?= date('Y-m-01') ?>">
        </div>
        <div class="col-lg-2">
            <label for="" class="form-label">S/D</label>
            <input type="date" name="" id="" class="form-control" value="<?= date('Y-m-d') ?>">
        </div>
        <div class="col-lg-3">
            <label for="" class="form-label">Unit</label>
            <select name="" id="" class="form-select">
                <option value="">Semua Unit</option>
            </select>
        </div>
    </div>
    <div class="mt-4">
        <div class="btn-group mb-2">
            <button type="button" class="btn btn-sm btn-dark rounded me-1">
                Rekapan LLP Unit
            </button>
            <button type="button" class="btn btn-sm btn-outline-dark rounded me-1">
                Rekapan LLP Bulan Berjalan
            </button>
            <button type="button" class="btn btn-sm btn-outline-dark rounded me-1">
                Rekapan LLP Bulan Tunggakan
            </button>
            <button type="button" class="btn btn-sm btn-outline-dark rounded me-1">
                Rekapan LLP Air & Non Air
            </button>
        </div>
        <div class="border border-dark text-center">
            <h6 class="mb-0 border-bottom border-dark py-1">Rekapan LLP Unit</h6>
            <h5 class="mb-0 py-4 ">PREVIEW LAPORAN</h5>
        </div>
    </div>
</div>