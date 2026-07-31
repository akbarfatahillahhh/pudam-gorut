<?= $this->extend('layouts/default') ?>
<?= $this->section('content') ?>

<div class="row">

    <div class="col-lg-4">
        <div class="card overflow-hidden border border-dark">
            <div class="card-body p-4">
                <h5 class="card-title mb-8 fw-semibold text-center">
                    Total Rekening
                </h5>
                <div class="row align-items-center">
                    <div class="col-12 text-center">
                        <h4 class="fw-semibold mb-3 totalRekening">0</h4>
                        <div class="d-flex align-items-center justify-content-center">
                            <div class="me-4">
                                <span class="round-8 bg-primary rounded-circle me-2 d-inline-block"></span>
                                <span class="fs-2 text-black"><?= date('01 M Y') ?></span>
                            </div>
                            <div>
                                <span class="round-8 bg-success rounded-circle me-2 d-inline-block"></span>
                                <span class="fs-2 text-black"><?= date('d M Y') ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card overflow-hidden border border-dark">
            <div class="card-body p-4">
                <h5 class="card-title mb-8 fw-semibold text-center">
                    Total Meter Di Catat
                </h5>
                <div class="row align-items-center">
                    <div class="col-12 text-center">
                        <h4 class="fw-semibold mb-3 totalMeterDiCatat">0</h4>
                        <div class="d-flex align-items-center justify-content-center">
                            <div class="me-4">
                                <span class="round-8 bg-primary rounded-circle me-2 d-inline-block"></span>
                                <span class="fs-2 text-black"><?= date('01 M Y') ?></span>
                            </div>
                            <div>
                                <span class="round-8 bg-success rounded-circle me-2 d-inline-block"></span>
                                <span class="fs-2 text-black"><?= date('d M Y') ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card overflow-hidden border border-dark">
            <div class="card-body p-4">
                <h5 class="card-title mb-8 fw-semibold text-center">
                    Total Di Tagih
                </h5>
                <div class="row align-items-center">
                    <div class="col-12 text-center">
                        <h4 class="fw-semibold mb-3 totalDiTagih">Rp. 0</h4>
                        <div class="d-flex align-items-center justify-content-center">
                            <div class="me-4">
                                <span class="round-8 bg-primary rounded-circle me-2 d-inline-block"></span>
                                <span class="fs-2 text-black"><?= date('01 M Y') ?></span>
                            </div>
                            <div>
                                <span class="round-8 bg-success rounded-circle me-2 d-inline-block"></span>
                                <span class="fs-2 text-black"><?= date('d M Y') ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<div>
    <div class="card overflow-hidden border border-dark">
        <div class="card-body p-4">
            <div class="border-bottom border-dark pb-3">
                <ul class="nav nav-pills nav-justified">
                    <li class="nav-item">
                        <button type="button" class="nav-link active tab-page" data-page="lpp_penagih" aria-current="page">LPP PENAGIH</button>
                    </li>
                    <li class="nav-item">
                        <button type="button" class="nav-link tab-page" data-page="lpp_bms_fastpay">LPP BMS FASTPAY</button>
                    </li>
                    <li class="nav-item">
                        <button type="button" class="nav-link tab-page" data-page="catat_meter">CATAT METER</button>
                    </li>
                    <li class="nav-item">
                        <button type="button" class="nav-link tab-page" data-page="lpp_unit">LPP UNIT</button>
                    </li>
                    <li class="nav-item">
                        <button type="button" class="nav-link tab-page" data-page="laporan_aktivitas_pcm">LAPORAN AKTIFITAS PCM</button>
                    </li>
                </ul>
            </div>

            <div class="pt-3" id="page-dashboard"></div>

        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('script') ?>
<script>
    $(document).ready(function() {
        loadDataForCard();
        loadPage('lpp_penagih');

        $('.tab-page').click(function(e) {
            e.preventDefault();
            var page = $(this).data('page');
            loadPage(page).then(() => {
                $('.tab-page').removeClass('active');
                $(this).addClass('active');
            })
        });
    });


    async function loadPage(page) {
        try {
            const response = await $.ajax({
                type: "get",
                url: "<?= base_url('Home/page/') ?>/" + page,
                dataType: "json",
            });
            $('#page-dashboard').html(response.page);
        } catch (error) {
            console.error(error);
        }
    }

    function loadDataForCard() {
        $.ajax({
            type: "get",
            url: "<?= base_url('Home/get_data_for_card') ?>",
            dataType: "json",
            success: function(response) {
                $('.totalRekening').html(formatIdr(response.total_rekening));
                $('.totalMeterDiCatat').html(formatIdr(response.total_catat_meter));
                $('.totalDiTagih').html('Rp. ' + formatIdr(response.total_di_tagih));
            }
        });
    }

    function formatIdr(number) {
        return new Intl.NumberFormat('id-ID', {
            style: 'decimal',
            minimumFractionDigits: 0
        }).format(number);
    }
</script>
<?= $this->endSection() ?>