<?= $this->extend('layouts/default') ?>

<?= $this->section('content') ?>


<div class="card border border-dark">
    <div class="card-body p-4">
        <h5 class="card-title fw-semibold mb-4">Daftar <?= $title ?> (Aktif)</h5>

        <div class="row mb-3">
            <div class="col-lg-3">
                <label for="" class="form-label">Filter Pelanggan</label>
                <select name="" id="filter-pelanggan" class="form-control">
                    <option value="wilayah" selected>Berdasarkan Wilayah</option>
                    <option value="kelompok">Berdasarkan Kelompok</option>
                    <option value="golongan">Berdasarkan Golongan</option>
                </select>
            </div>
            <div class="col-lg-3 filter-wilayah">
                <label for="" class="form-label">Kecamatan</label>
                <select name="" id="kecamatan" class="form-control">
                    <option value="ALL">[ Pilih Kecamatan ]</option>
                </select>
            </div>
            <div class="col-lg-3 filter-wilayah">
                <label for="" class="form-label">Kelurahan/Desa</label>
                <select name="" id="kelurahan" class="form-control">
                    <option value="ALL">[ Pilih Kelurahan/Desa ]</option>
                </select>
            </div>
            <div class="col-lg-3 filter-kelompok">
                <label for="" class="form-label">Kelompok</label>
                <select name="" id="kelompok" class="form-control">
                    <option value="ALL">[ Pilih Kelompok ]</option>
                </select>
            </div>
            <div class="col-lg-3 filter-golongan">
                <label for="" class="form-label">Golongan</label>
                <select name="" id="golongan" class="form-control">
                    <option value="ALL">[ Pilih Golongan ]</option>
                </select>
            </div>
        </div>

        <div class="table-responsive">
            <table id="table" class="table table-sm table-bordered table-hover table-striped border-dark text-nowrap mb-0 align-middle">
                <thead>
                    <tr>
                        <th class="border-top border-dark">#</th>
                        <th class="border-top border-dark">No. Sambungan</th>
                        <th class="border-top border-dark">Pelanggan</th>
                        <th class="border-top border-dark">Kecamatan</th>
                        <th class="border-top border-dark">Kelurahan/Desa</th>
                        <th class="border-top border-dark">Khusus</th>
                        <th class="border-top border-dark">Kelompok</th>
                        <th class="border-top border-dark">Golongan</th>
                        <th class="border-top border-dark">#</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>
<div class="modal-place"></div>

<?= $this->endSection() ?>

<?= $this->section('script') ?>

<script>
    $('#filter-pelanggan').change(function(e) {
        e.preventDefault();
        var filterPelanggan = $(this).val();
        if (filterPelanggan == 'wilayah') {
            $('.filter-wilayah').show();
            $('.filter-kelompok, .filter-golongan').hide();
            filtering(filterPelanggan);
        } else if (filterPelanggan == 'kelompok') {
            $('.filter-kelompok').show();
            $('.filter-wilayah, .filter-golongan').hide();
            filtering(filterPelanggan);
        } else if (filterPelanggan == 'golongan') {
            $('.filter-golongan').show();
            $('.filter-wilayah, .filter-kelompok').hide();
            filtering(filterPelanggan);
        }
    });

    $('#filter-pelanggan, #kecamatan, #kelurahan, #kelompok, #golongan').change(function(e) {
        e.preventDefault();
        $('#table').DataTable().ajax.reload(null, false);
    });

    function filtering(data) {
        $.ajax({
            type: "post",
            url: "<?= base_url('GetData/Filtering') ?>",
            data: {
                data: data
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
            success: function(response) {
                if (data === 'wilayah') {
                    if (response.kecamatan) {
                        $('#kecamatan').html(`<option value="ALL">[ Pilih Kecamatan ]</option>`);
                        $.each(response.kecamatan, function(index, value) {
                            $('#kecamatan').append(`<option value="${value.id}">${value.text}</option>`);
                        });
                        $('#kecamatan').select2();
                    }
                } else if (data === 'kelompok') {
                    if (response.kelompok) {
                        $('#kelompok').html(`<option value="ALL">[ Pilih Kelompok ]</option>`);
                        $.each(response.kelompok, function(index, value) {
                            $('#kelompok').append(`<option value="${value.id}">${value.text}</option>`);
                        });
                        $('#kelompok').select2();
                    }
                } else if (data === 'golongan') {
                    if (response.golongan) {
                        $('#golongan').html(`<option value="ALL">[ Pilih Golongan ]</option>`);
                        $.each(response.golongan, function(index, value) {
                            $('#golongan').append(`<option value="${value.id}">${value.text}</option>`);
                        });
                        $('#golongan').select2();
                    }
                }
            }
        });
    }

    $('#kecamatan').change(function(e) {
        e.preventDefault();
        var filterKelurahan = $(this).find('option:selected').val();
        $.ajax({
            type: "post",
            url: "<?= base_url('GetData/FilterKelurahan') ?>",
            data: {
                id: filterKelurahan
            },
            dataType: "json",
            success: function(response) {
                $('#kelurahan').html(`<option value="ALL">[ Pilih Kelurahan/Desa ]</option>`);
                $.each(response.kelurahan, function(index, value) {
                    $('#kelurahan').append(`<option value="${value.id}">${value.text}</option>`);
                });
                $('#kelurahan').select2();
            }
        });
    })

    $(document).ready(function() {
        var filterPelanggan = $('#filter-pelanggan').find('option:selected').val();
        filtering(filterPelanggan);

        $('.filter-kelompok, .filter-golongan').hide();

        $('#table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: "<?= base_url('Pelanggan/data') ?>",
                type: 'post',
                data: function(d) {
                    d.filterPelanggan = $('#filter-pelanggan').find('option:selected').val();
                    d.filterKecamatan = $('#kecamatan').find('option:selected').val();
                    d.filterKelurahan = $('#kelurahan').find('option:selected').val();
                    d.filterKelompok = $('#kelompok').find('option:selected').val();
                    d.filterGolongan = $('#golongan').find('option:selected').val();
                }
            },
            columns: [{
                    class: 'text-center align-middle py-1 text-dark',
                    searchable: false,
                    render: function(row, type, set, meta) {
                        return meta.row + meta.settings._iDisplayStart + 1;
                    }
                },
                {
                    data: 'no_sambungan',
                    class: 'text-center align-middle py-1 text-black',
                },
                {
                    data: 'nama_pelanggan',
                    class: 'align-middle py-1 text-black',
                },
                {
                    data: 'kecamatan',
                    class: 'text-center align-middle py-1 text-black',
                    searchable: false,
                },
                {
                    data: 'kelurahan',
                    class: 'text-center align-middle py-1 text-black',
                    searchable: false,
                },
                {
                    data: 'pelanggan_khusus',
                    class: 'text-center align-middle py-0 text-black',
                    searchable: false,
                    render: function(data, type, row) {
                        return data == 'Y' ? '<i class="fas fa-check text-success py-0 h5 mb-0"></i>' : '<i class="fas fa-times text-danger py-0 h5 mb-0"></i>'
                    }
                },
                {
                    data: 'kelompok_jasa',
                    class: 'text-center align-middle py-1 text-black',
                    searchable: false,
                },
                {
                    data: 'golongan',
                    class: 'text-center align-middle py-1 text-black',
                    searchable: false,
                },
                {
                    data: 'action',
                    class: 'text-center align-middle py-1 text-black',
                    searchable: false,
                },
            ]
        });
    });

    function details(id) {
        $.ajax({
            type: "get",
            url: "<?= base_url('Pelanggan/details') ?>",
            data: {
                id: id
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
            success: function(response) {
                $('.modal-place').html("").html(response.html);
                $('.modal-place #modal').modal('show');
            }
        });
    }
</script>

<?= $this->endSection() ?>