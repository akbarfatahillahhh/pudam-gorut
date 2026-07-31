<div>
    <div class="row">
        <div class="col-lg-2">
            <label for="" class="form-label">Bulan</label>
            <select id="aktifitas_pcm-bulan" class="form-select">
                <option value="">[ Pilih Bulan ]</option>
                <?php $daftarBulan = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember']; ?>
                <?php foreach ($daftarBulan as $key => $value) : ?>
                    <option value="<?= $key ?>" <?= ($key == (date('m') - 1)) ? 'selected' : '' ?>><?= $value ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-lg-2">
            <label for="" class="form-label">Tahun</label>
            <select id="aktifitas_pcm-tahun" class="form-select">
                <option value="">[ Pilih Tahun ]</option>
                <?php for ($i = date('Y'); $i >= 2010; $i--): ?>
                    <option value="<?= $i ?>" <?= ($i == date('Y')) ? 'selected' : '' ?>><?= $i ?></option>
                <?php endfor; ?>
            </select>
        </div>
        <div class="col-lg-3">
            <label for="" class="form-label">Petugas</label>
            <select id="aktifitas_pcm-petugas" class="form-select">
                <option value="">[ Pilih Petugas ]</option>
            </select>
        </div>
        <div class="col-lg-3">
            <label for="" class="form-label">Lokasi</label>
            <select id="aktifitas_pcm-lokasi" class="form-select">
                <option value="all" selected>Semua Lokasi</option>
            </select>
        </div>
        <div class="col-lg-2">
            <label for="" class="form-label">Status Verifikasi</label>
            <select id="aktifitas_pcm-status" class="form-select">
                <option value="all">Semua</option>
                <option value="1">Tercatat</option>
                <option value="0">Belum dicatat</option>
            </select>
        </div>
    </div>

    <div class="mb-2 mt-3">
        <div class="row">
            <div class="col-2">
                <button class="btn btn-sm btn-danger" id="exportPdf">
                    <i class="fa fa-file-pdf"></i>
                    PDF
                </button>
                <button class="btn btn-sm btn-success" id="exportExcel">
                    <i class="fa fa-file-excel"></i>
                    Excel
                </button>
            </div>
            <div class="col"></div>
            <div class="col-3">
                <div class="input-group input-group-sm">
                    <input type="text" class="form-control" id="aktifitas_pcm-searchPelanggan" placeholder="Cari Pelanggan...">
                    <button class="btn btn-dark" type="button" id="aktifitas_pcm-btnSearch">
                        <i class="fa fa-search"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="">
        <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
            <table class="table table-sm table-bordered table-hover border-dark mb-0" id="aktifitas_pcm-table">
                <thead>
                    <tr>
                        <th class="text-center align-middle text-black">#</th>
                        <th class="text-center align-middle text-black">No. Sambung</th>
                        <th class="text-center align-middle text-black">Nama Pelanggan</th>
                        <th class="text-center align-middle text-black">Kecamatan</th>
                        <th class="text-center align-middle text-black">Desa/Kelurahan</th>
                        <th class="text-center align-middle text-black">Golongan</th>
                        <th class="text-center align-middle text-black">Angka Meter</th>
                        <th class="text-center align-middle text-black">Tgl. Pencatatan</th>
                        <th class="text-center align-middle text-black">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="text-center align-middle py-4" colspan="100%">DATA KOSONG</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal-place"></div>

<script>
    $(function () {
        $('#aktifitas_pcm-bulan, #aktifitas_pcm-tahun').select2();
        get_select_petugas();

        $('#aktifitas_pcm-bulan, #aktifitas_pcm-tahun, #aktifitas_pcm-petugas, #aktifitas_pcm-lokasi, #aktifitas_pcm-status').change(function (e) { 
            e.preventDefault();
            getDataPCM();
        });
        
        $('#aktifitas_pcm-btnSearch').click(function (e) { 
            e.preventDefault();
            if ($('#aktifitas_pcm-searchPelanggan').val() == '') {
                return false;
            }
            getDataPCM();
        });

        $('#aktifitas_pcm-searchPelanggan').keyup(function (e) { 
            if ($('#aktifitas_pcm-searchPelanggan').val() == '') {
                getDataPCM();
            }
        });


        $('#exportPdf').click(function (e) { 
            e.preventDefault();
            var bulan = $('#aktifitas_pcm-bulan').find('option:selected').val();
            var tahun = $('#aktifitas_pcm-tahun').find('option:selected').val();
            var petugas = $('#aktifitas_pcm-petugas').find('option:selected').val();
            var lokasi = $('#aktifitas_pcm-lokasi').find('option:selected').val();
            var status = $('#aktifitas_pcm-status').find('option:selected').val();
            var searchPelanggan = $('#aktifitas_pcm-searchPelanggan').val();

            if (bulan == '') {
                Swal.fire('Maaf!', 'Bulan harus dipilih', 'warning');
                return false;
            }

            if (tahun == '') {
                Swal.fire('Maaf!', 'Tahun harus dipilih', 'warning');
                return false;
            }

            if (petugas == '' || petugas == 'all') {
                Swal.fire('Maaf!', 'Petugas harus dipilih', 'warning');
                return false;
            }
            
            $.ajax({
                type: "post",
                url: "<?= base_url('Home/modal_export_pdf') ?>",
                data: {
                    bulan: bulan,
                    tahun: tahun,
                    petugas: petugas,
                    lokasi: lokasi,
                    status: status,
                    keyword: searchPelanggan,
                    route: 'aktifitas_pcm'
                },
                dataType: "json",
                success: function (response) {
                    $('.modal-place').html(response.page);
                    $('#modal').modal('show');
                }
            });
        });
    });

    function get_select_petugas() {
        $.ajax({
            type: "get",
            url: "<?= base_url('GetData/getPCM') ?>",
            dataType: "json",
            success: function (response) {
                $.each(response, function (idx, value) { 
                    $('#aktifitas_pcm-petugas').append(`<option value="${value.id}" class="text-uppercase">${value.text}</option>`);
                    $('#aktifitas_pcm-petugas').select2();
                });
            }
        });
    }

    function getDataPCM() {
        var bulan = $('#aktifitas_pcm-bulan').find('option:selected').val();
        var tahun = $('#aktifitas_pcm-tahun').find('option:selected').val();
        var petugas = $('#aktifitas_pcm-petugas').find('option:selected').val();
        var lokasi = $('#aktifitas_pcm-lokasi').find('option:selected').val();
        var status = $('#aktifitas_pcm-status').find('option:selected').val();
        var searchPelanggan = $('#aktifitas_pcm-searchPelanggan').val();

        if (bulan == '') {
            Swal.fire('Maaf!', 'Bulan harus dipilih', 'warning');
            return false;
        }

        if (tahun == '') {
            Swal.fire('Maaf!', 'Tahun harus dipilih', 'warning');
            return false;
        }

        if (petugas == '' || petugas == 'all') {
            Swal.fire('Maaf!', 'Petugas harus dipilih', 'warning');
            return false;
        }

        $.ajax({
            type: "get",
            url: "<?= base_url('Home/data_laporan_aktifitas_pcm') ?>",
            data: {
                bulan: bulan,
                tahun: tahun,
                petugas: petugas,
                lokasi: lokasi,
                keyword: searchPelanggan,
                status: status,
            },
            dataType: "json",
            beforeSend: function() {
                Swal.fire({
                    icon: 'warning',
                    title: 'Loading...',
                    html: 'Sedang Diproses',
                    showConfirmButton: false,
                    allowOutsideClick: false,
                    onBeforeOpen: () => {
                        Swal.showLoading()
                    }
                });
            },
            complete: function() {
                Swal.close();
            },
            success: function (response) {
                $('#aktifitas_pcm-table tbody').html(response.html);

                $('#aktifitas_pcm-lokasi').html(`<option value="all">Semua Lokasi</option>`);
                $.each(response.dataKelurahan, function (index, value) { 
                     $('#aktifitas_pcm-lokasi').append(`<option value="${value.id_kel}">${value.kecamatan} - ${value.kelurahan}</option>`);
                });
                $('#aktifitas_pcm-lokasi').select2();
            }
        })
    }
</script>