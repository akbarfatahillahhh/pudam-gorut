<?= $this->extend('layouts/default') ?>
<?= $this->section('content') ?>
<div class="card border border-dark">
    <div class="card-body p-4">
        <div class="row">
            <div class="col-6">
                <h5 class="card-title fw-semibold mb-4">Faktur Pembelian</h5>
            </div>
            <div class="col-6 text-end">
                <button type="button" class="btn btn-sm btn-primary" id="btnAdd">
                    <i class="fa fa-plus-circle"></i>
                    PENERIMAAN BARU
                </button>
            </div>
        </div>
        <div class="row mb-2">
            <div class="col-lg-3 mb-2">
                <label for="" class="form-label">Periode</label>
                <input type="date" name="" id="tglAwal" class="form-control" value="<?= date('Y-m-01') ?>">
            </div>
            <div class="col-lg-3 mb-2">
                <label for="" class="form-label">S/D</label>
                <input type="date" name="" id="tglAkhir" class="form-control" value="<?= date('Y-m-d') ?>">
            </div>
        </div>

        <div class="table-responsive" style="height: 500px; overflow-y: auto;">
            <table id="table-penerimaan" class="table table-sm table-bordered table-hover border-dark text-nowrap mb-0 align-middle w-100">
                <thead class="text-black fs-4">
                    <tr>
                        <th class="text-center align-middle fs-2">NO. FAKTUR</th>
                        <th class="text-center align-middle fs-2">BARANG</th>
                        <th class="text-center align-middle fs-2">QTY</th>
                        <th class="text-center align-middle fs-2">SATUAN</th>
                        <th class="text-center align-middle fs-2">HARGA SATUAN</th>
                        <th class="text-center align-middle fs-2">JUMLAH</th>
                        <th class="text-center align-middle fs-2">#</th>
                    </tr>
                </thead>
                <tbody class="text-black"></tbody>
            </table>
        </div>
    </div>
</div>
<div class="modal-place"></div>
<?= $this->endSection() ?>
<?= $this->section('script') ?>


<script>
    $(document).ready(function() {
        getData();
    });

    $('#tglAwal, #tglAkhir').change(function(e) {
        e.preventDefault();
        var tglAwal = $('#tglAwal').val();
        var tglAkhir = $('#tglAkhir').val();
        getData(tglAwal, tglAkhir);
    })

    $('#btnAdd').click(function(e) {
        e.preventDefault();
        $.ajax({
            type: "get",
            url: "<?= base_url('Logistik/BarangMasuk/Penerimaan/new') ?>",
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
    });

    async function getData(tglAwal, tglAkhir) {
        $.ajax({
            type: "GET",
            url: "<?= base_url('Logistik/BarangMasuk/Penerimaan/data') ?>",
            data: {
                tglAwal: tglAwal,
                tglAkhir: tglAkhir,
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
                $('#table-penerimaan tbody').html("");
                if (Array.isArray(response) && response.length > 0) {
                    var row = "";
                    var html = "";
                    htmlEnd = `<tr><td class="text-center bg-black text-white" colspan="100%"></td></tr>`;
                    $.each(response, function(indexInArray, value) {
                        if (row != value.no_faktur) {
                            if (row != "") {
                                html += htmlEnd
                            }
                            row = value.no_faktur;
                            html += `
                                <tr class="fw-bold">
                                    <td class="text-center bg-dark text-white border-white align-middle fs-2" rowspan="${value.TOTAL_BARANG}">
                                        ${value.no_faktur}
                                    </td>
                                    <td class="text-center bg-dark text-white border-white align-middle fs-2 text-uppercase" colspan="5">
                                        <div class="row">
                                            <div class="col-7 text-center">
                                                TANGGAL PENERIMAAN : ${new Intl.DateTimeFormat('id-ID', { day: '2-digit', month: 'long', year: 'numeric' }).format(new Date(value.tanggal))}
                                            </div>
                                            <div class="col-5 text-center">
                                                TOTAL : ${new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0, maximumFractionDigits: 0 }).format(value.TOTAL_HARGA)}
                                            </div>
                                        </div>
                                    </td>

                                    <td class="text-center bg-dark text-white">
                                        <div class="dropdown">
                                            <button class="btn btn-sm " type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                <i class="fa fa-ellipsis-v text-white"></i>
                                            </button>
                                            <ul class="dropdown-menu py-0">
                                                <li>
                                                <li>
                                                    <a class="dropdown-item text-black" href="javascript:void(0)" onclick="info('${value.no_faktur}')">
                                                        <i class="fa fa-info-circle pe-1 text-info"></i>
                                                        Detail
                                                    </a>
                                                </li>
                                                    <a class="dropdown-item text-black" href="javascript:void(0)" onclick="deleteData('${value.no_faktur}','null')">
                                                        <i class="fa fa-trash pe-1 text-danger"></i>
                                                        Hapus
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                            `;
                        }
                        html += `<tr class="fw-bold">
                                    <td class="text-center align-middle py-1">+</td>
                                    <td class="text-dark align-middle py-1 ps-2">${value.BARANG}</td>
                                    <td class="text-dark align-middle py-1 text-center">${value.QTY}</td>
                                    <td class="text-dark align-middle py-1 text-uppercase text-center">${value.SATUAN}</td>
                                    <td class="text-dark align-middle py-1 text-end">${new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0, maximumFractionDigits: 0 }).format(value.HARGA)}</td>
                                    <td class="text-dark align-middle py-1 text-end">${new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0, maximumFractionDigits: 0 }).format(value.JUMLAH_HARGA)}</td>
                                    <td class="text-dark align-middle py-1 text-center">
                                        <div class='dropdown visually-hidden'>
                                            <button class='btn btn-sm' type='button' data-bs-toggle='dropdown' aria-expanded='false'>
                                                <i class='fa fa-ellipsis-v text-black'></i>
                                            </button>
                                            <ul class='dropdown-menu py-0'>
                                                <li>
                                                    <a class="dropdown-item text-black " href="javascript:void(0)" onclick="deleteData('${value.no_faktur}','${value.id}')"><i class="fa-solid text-danger fa-trash"></i>
                                                    Hapus
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>`;
                    });
                    $('#table-penerimaan tbody').append(html)
                } else if (Array.isArray(response) && response.length == 0) {
                    $('#table-penerimaan tbody').html("<tr><td colspan='7' class='text-center bg-dark text-white'>Data Tidak Ada</td></tr>");
                }
            }
        });
    }

    function info(no_faktur) {
        $.ajax({
            type: "GET",
            url: "<?= base_url('Logistik/BarangMasuk/Penerimaan/info') ?>",
            data: {
                id: no_faktur,
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

    function deleteData(no_faktur, id) {
        Swal.fire({
            title: 'Apakah anda yakin?',
            text: "Anda tidak akan bisa mengembalikan data ini!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    type: "post",
                    url: "<?= base_url('Logistik/BarangMasuk/Penerimaan/delete') ?>",
                    data: {
                        no_faktur: no_faktur,
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
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: response.message,
                            showConfirmButton: false,
                            timer: 1500
                        }).then(() => {
                            location.reload();
                        });
                    }
                });
            } else {
                return false;
            }
        })
    }
</script>
<?= $this->endSection() ?>