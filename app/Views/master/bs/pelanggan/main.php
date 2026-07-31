<?= $this->extend('layouts/default') ?>

<?= $this->section('content') ?>



<div class="card border border-dark">
    <div class="card-body p-4">
        <div class="row">
            <div class="col-6">
                <h5 class="card-title fw-semibold mb-4">Master <?= $title ?></h5>
            </div>
            <div class="col-6 text-end">

                <div class="dropdown open visually-hidden">
                    <button class="btn btn-primary dropdown-toggle" type="button" id="triggerId" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="fa fa-plus-circle"></i> Tambah
                    </button>
                    <div class="dropdown-menu" aria-labelledby="triggerId">
                        <button class="dropdown-item text-black" id="btnAdd-off">
                            <i class="fa fa-user-plus text-primary pe-2"></i>
                            Pelanggan Baru
                        </button>
                        <button onclick="pasangMeter()" class="dropdown-item text-black" href="#">
                            <i class="fa fa-user-cog text-warning pe-2"></i>
                            Pelanggan Belum Terpasang Meter
                        </button>
                    </div>
                </div>

                <button type="button" class="btn btn-sm btn-primary" id="btnAdd">
                    <i class="fa fa-user-plus pe-1"></i>
                    Pelanggan Baru
                </button>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-sm-6 col-md-4 col-lg-3 mb-2">
                <label for="" class="form-label">Filter Pelanggan</label>
                <select name="" id="filter-pelanggan" class="form-control">
                    <option value="wilayah" selected>Berdasarkan Wilayah</option>
                    <option value="kelompok">Berdasarkan Kelompok</option>
                    <option value="golongan">Berdasarkan Golongan</option>
                </select>
            </div>
            <div class="col-sm-6 col-md-4 col-lg-3 mb-2 filter-wilayah">
                <label for="" class="form-label">Kecamatan</label>
                <select name="" id="kecamatan" class="form-control">
                    <option value="ALL">[ Pilih Kecamatan ]</option>
                </select>
            </div>
            <div class="col-sm-6 col-md-4 col-lg-3 mb-2 filter-wilayah">
                <label for="" class="form-label">Kelurahan/Desa</label>
                <select name="" id="kelurahan" class="form-control">
                    <option value="ALL">[ Pilih Kelurahan/Desa ]</option>
                </select>
            </div>
            <div class="col-sm-6 col-md-4 col-lg-3 mb-2 filter-kelompok">
                <label for="" class="form-label">Kelompok</label>
                <select name="" id="kelompok" class="form-control">
                    <option value="ALL">[ Pilih Kelompok ]</option>
                </select>
            </div>
            <div class="col-sm-6 col-md-4 col-lg-3 mb-2 filter-golongan">
                <label for="" class="form-label">Golongan</label>
                <select name="" id="golongan" class="form-control">
                    <option value="ALL">[ Pilih Golongan ]</option>
                </select>
            </div>
        </div>

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

        <div class="table-responsive">
            <table id="table" class="table table-sm table-bordered table-hover table-striped border-dark text-nowrap mb-0 align-middle">
                <thead>
                    <tr>
                        <th class="border-top border-dark">#</th>
                        <th class="border-top border-dark">No. Sambungan</th>
                        <th class="border-top border-dark">Pelanggan</th>
                        <th class="border-top border-dark">Kecamatan</th>
                        <th class="border-top border-dark">Kelurahan/Desa</th>
                        <th class="border-top border-dark">Status Aktif</th>
                        <th class="border-top border-dark">Status. Khusus</th>
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
    $('#exportPdf').click(function(e) {
        e.preventDefault();
        $.ajax({
            type: "get",
            url: "<?= base_url('Master/Pelanggan/modal_export_pdf') ?>",
            dataType: "json",
            success: function(response) {
                $('.modal-place').html(``).html(response.html);
                $('.modal-place #modal').modal('show');
            }
        });
    });
</script>

<script>
    function pasangMeter() {
        $.ajax({
            type: "get",
            url: "<?= base_url('/master/pasang-meter') ?>",
            dataType: "json",
            success: function(response) {
                $('.container-fluid').html(response.html);
            }
        });
    }
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
</script>

<script>
    $('#btnAdd').click(function(e) {
        e.preventDefault();
        $.ajax({
            type: "get",
            url: "<?= base_url('/Master/Pelanggan/new') ?>",
            dataType: "json",
            success: function(response) {
                $('.modal-place').html("").html(response.html);
                $('.modal-place #modal').modal('show');

            }
        });
    });

    function editStatusAktif(id) {
        Swal.fire({
            title: 'Ubah Status Aktif ?',
            text: "Apakah anda yakin ingin mengubah status Aktif Pelanggan ?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, Ubah!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    type: "get",
                    url: "<?= base_url('/Master/Pelanggan/editStatus') ?>",
                    data: {
                        id_pelanggan: id,
                    },
                    dataType: "json",
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                title: 'Berhasil!',
                                text: response.message,
                                icon: 'success'
                            });
                            $('#table').DataTable().ajax.reload();
                        }
                    }
                });
            }
        })
    }

    function filtering(data) {
        $.ajax({
            type: "post",
            url: "<?= base_url('GetData/Filtering') ?>",
            data: {
                data: data
            },
            dataType: "json",
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
                url: "<?= base_url('Master/Pelanggan/data') ?>",
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
                    data: 'status_aktif',
                    class: 'text-center align-middle py-1 text-black',
                    searchable: false,
                    render: function(data, type, row) {
                        return data == 1 ? '<span class="badge bg-success rounded py-1 fs-2" style="cursor: pointer;" onclick="editStatusAktif(' + row.id_pelanggan + ')" >Aktif</span>' : '<span class="badge bg-danger rounded py-1 fs-2" style="cursor: pointer;" onclick="editStatusAktif(' + row.id_pelanggan + ')" >Non Aktif</span>';
                    }
                },
                {
                    data: 'pelanggan_khusus',
                    class: 'text-center align-middle py-1 text-black',
                    searchable: false,
                    render: function(data, type, row) {
                        return data == 'Y' ? '<span  class="badge bg-success rounded py-1 fs-2" onclick="editKhusus(' + row.id_pelanggan + ')" style="cursor: pointer;">Khusus</span>' : '<span class="badge bg-danger rounded py-1 fs-2" onclick="editKhusus(' + row.id_pelanggan + ')" style="cursor: pointer;">Tidak</span>'
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

    function editKhusus(id) {
        Swal.fire({
            title: 'Ubah Status Pelanggan Khusus ?',
            text: "Apakah anda yakin ingin mengubah status Pelanggan Khusus ?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, Ubah!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    type: "get",
                    url: "<?= base_url('/Master/Pelanggan/editKhusus') ?>",
                    data: {
                        id_pelanggan: id,
                    },
                    dataType: "json",
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                title: 'Berhasil!',
                                text: response.message,
                                icon: 'success'
                            });
                            $('#table').DataTable().ajax.reload(null, false);
                        }
                    }
                });
            }
        })
    }

    function edit(id) {
        $.ajax({
            type: "get",
            url: "<?= base_url('Master/Pelanggan/edit') ?>",
            data: {
                id: id
            },
            dataType: "json",
            success: function(response) {
                $('.modal-place').html("").html(response.html);
                $('.modal-place #modal').modal('show');
            }
        });
    }

    function pcm(id) {
        $.ajax({
            type: "get",
            url: "<?= base_url('Master/Pelanggan/pcm') ?>",
            data: {
                id: id
            },
            dataType: "json",
            success: function(response) {
                $('.modal-place').html("").html(response.html);
                $('.modal-place #modal').modal('show');
            }
        });
    }

    function hapus(id_pelanggan) {
        Swal.fire({
            title: 'Apakah anda yakin?',
            text: "Data akan dihapus secara permanen!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, Hapus!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    type: "post",
                    url: "<?= base_url('Master/Pelanggan/delete') ?>",
                    data: {
                        id_pelanggan: id_pelanggan
                    },
                    dataType: "json",
                    success: function(response) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: response.message,
                            showConfirmButton: false,
                            timer: 1500
                        })
                        $('#table').DataTable().ajax.reload(null, false);
                    }
                });
            }
        })
    }
</script>

<?= $this->endSection() ?>