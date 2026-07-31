<?= $this->extend('layouts/default') ?>
<?= $this->section('content') ?>

<div class="card border border-dark">
    <div class="card-body p-4">
        <h5 class="card-title fw-semibold mb-4"><?= $title ?></h5>
        <div class="row">

            <div class="col-lg-3 col-sm-12 mb-1">
                <div class="card">
                    <div class="card-header border border-dark py-2 text-center">
                        <h6 class="card-title fw-semibold mb-0 fs-3">Daftar Permintaan</h6>
                    </div>
                    <div class="card-body px-0 py-0">
                        <input type="search" class="form-control border-right border-left border-bottom-0 border-top-0 rounded-0" id="search" placeholder="Cari permintaan...">
                        <div class="overflow-y-auto h-75 border border-dark">
                            <div id="card-content">
                                <div id="loader" style="display: none ; display: block" class="d-flex align-items-center justify-content-center h-100">
                                    <div class="spinner-border text-dark" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-9 col-sm-12">
                <div class="card">
                    <div class="card-header border border-dark py-2 text-center">
                        <h6 class="card-title fw-semibold mb-0 fs-3">Detail Permintaan</h6>
                    </div>
                    <div class="card-body py-2 px-3 border-bottom border-start border-end border-dark">
                        <div id="detail-none">
                            <div class="alert alert-warning alert-dismissible fade text-center py-3 mb-0 show" role="alert">
                                <span>Silahkan pilih permintaan yang akan di verifikasi</span>
                            </div>
                        </div>
                        <div id="detail-table" class="d-none">
                            <div class="row mb-2">
                                <div class="col-lg-4">
                                    <button class="btn btn-outline-dark btn-sm d-none h-100" id="back">
                                        <i class="fa fa-arrow-left"></i> Kembali
                                    </button>
                                </div>
                                <div class="col-lg-8 text-end">
                                    <button class="btn btn-sm btn-danger" id="btnCancel" onclick="ubahStatus(this,'N')" title="Batalkan Permintaan">
                                        <i class="fa fa-times-square"></i>
                                        Batalkan
                                    </button>
                                    <button class="btn btn-sm btn-warning" id="btnDraft" onclick="ubahStatus(this,'P')" title="Simpan Draft Permintaan">
                                        <i class="fa fa-save"></i>
                                        Simpan Draft
                                    </button>
                                    <button class="btn btn-sm btn-success" id="btnAccept" onclick="ubahStatus(this,'Y')" title="Selesaikan Permintaan">
                                        <i class="fa fa-check"></i>
                                        Selesai
                                    </button>
                                    <button class="btn btn-sm btn-dark" id="btnExportPdf" title="Terima Permintaan">
                                        <i class="fa fa-file-pdf"></i>
                                        PDF
                                    </button>
                                </div>
                            </div>
                            <div class="row mx-0 mb-2">
                                <div class="col-6 text-center border border-dark text-black small">
                                    NO. PERMINTAAN
                                    <br>
                                    <b id="no_permintaan">-</b>
                                </div>
                                <div class="col-6 text-center border border-dark text-black small">
                                    PENANGGUNG JAWAB
                                    <br>
                                    <b id="penerima">-</b>
                                </div>
                            </div>
                            <div class="table-responsive overflow-y-auto h-75">
                                <table class="table table-sm table-hover table-bordered border border-dark mb-0" id="table">
                                    <thead class="text-black fs-4 bg-dark text-white">
                                        <tr>
                                            <td class="text-center align-middle border-top border-dark fs-2">#</td>
                                            <td class="text-center align-middle border-top border-dark fs-2">NAMA BARANG</td>
                                            <td class="text-center align-middle border-top border-dark fs-2">SATUAN</td>
                                            <td class="text-center align-middle border-top border-dark fs-2">STOK TERSEDIA</td>
                                            <td class="text-center align-middle border-top border-dark fs-2">JML. DIMINTA</td>
                                            <td class="text-center align-middle border-top border-dark fs-2">
                                                JML. DIBERIKAN
                                                <i class="fa fa-edit text-success ps-1"></i>
                                            </td>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td class="text-center align-middle py-2 text-black" colspan="100%">Tidak Ada Data</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal-place"></div>

<?= $this->endSection() ?>
<?= $this->section('script') ?>
<script>
    $(document).ready(function() {
        ambilPermintaan();
    })

    $('#search').keyup(function() {
        var value = $(this).val();
        ambilPermintaan(value);
    })
    $('#back').click(function() {
        $('#detail-none').removeClass('d-none');
        $('#detail-table').addClass('d-none');
        $(this).addClass('d-none');
    });

    function ubahStatus(element, status) {
        var no_permintaan = $(element).attr('data-permintaan');
        Swal.fire({
            title: 'Apakah anda yakin?',
            text: "Ubah Status Permintaan",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '<?= base_url('logistik/gudang/CekPermintaan/ubah_status') ?>',
                    method: 'POST',
                    data: {
                        no_permintaan: no_permintaan,
                        status: status
                    },
                    dataType: 'json',
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
                        if (response.status == "success") {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil',
                                text: response.message,
                            });
                            ambilPermintaan();
                            cekPermintaan(no_permintaan)
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal',
                                text: response.message,
                            });
                            ambilPermintaan();
                            cekPermintaan(no_permintaan)
                        }
                    }
                })
            }
        })

    }

    function ambilPermintaan(value) {
        $.ajax({
            url: '<?= base_url('logistik/gudang/CekPermintaan/ambil_permintaan') ?>',
            method: 'GET',
            data: {
                search: value
            },
            dataType: 'json',
            beforeSend: function() {
                if (value == '' || value == null || value == undefined) {
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
                }
            },
            complete: function() {
                Swal.close();
            },
            success: function(response) {
                if (response.status == "success") {
                    $('#card-content').html('');
                    if (Array.isArray(response.data) && response.data.length > 0) {
                        $.each(response.data, function(indexInArray, items) {
                            $('#card-content').append(`
                               <div class="card card-body border border-dark shadow-none mx-2 my-2 p-2 pointer" style="box-shadow: 0 0 10px rgba(189, 189, 189, 0.93);" onclick="cekPermintaan('${items.no_permintaan}')" onmouseover="this.style.backgroundColor='rgba(0, 0, 0, 0.2)'; this.style.color='#ffff'; this.style.transform='scale(1.01)';" onmouseout="this.style.backgroundColor=''; this.style.color=''; this.style.transform='scale(1)';">
                                        <div class="row mx-0 mb-1 border-bottom border-dark">
                                            <div class="col-6 align-middle mb-1">
                                                <span class="fs-2 text-black fw-semibold text-uppercase d-block pb-1">${items.no_permintaan}</span>
                                            </div>
                                            <div class="col-6 align-middle text-end mb-1">
                                                <span class="badge fs-1 fw-bolder py-1 bg-${items.status === 'Y' ? 'success' : items.status === 'N' ? 'danger' : items.status === 'P' ? 'warning' : 'warning'}">
                                                    ${items.status === 'Y' ? '<i class="fas fa-check"></i>' : items.status === 'N' ? '<i class="fas fa-times"></i>' : items.status === 'P' ? '<i class="fas fa-history"></i>' : '<i class="fas fa-history"></i>' }
                                                </span>
                                            </div>
                                        </div>
                                        <span class="fs-2 fw-semibold text-uppercase text-black text-center d-block">${items.penerima}</span>
                                        <p class="fs-2 fw-semibold text-uppercase text-black text-center mb-0">
                                        ${new Intl.DateTimeFormat('id-ID', { day: '2-digit', month: 'long', year: 'numeric' }).format(new Date(items.tanggal))}
                                        </p>
                                </div>`);
                        });
                    } else {
                        $('#card-content').append(`
                            <div class="text-center py-2">
                                <span class="h6 fw-semibold text-uppercase text-center">Tidak Ada Data</span>
                            </div>
                        `);
                    }
                }
            }
        });
    }

    function cekPermintaan(id) {
        $('#detail-none').addClass('d-none');
        $('#detail-table').removeClass('d-none');
        $('#back').removeClass('d-none');
        $.ajax({
            url: '<?= base_url('logistik/gudang/CekPermintaan/ambil_detail_permintaan') ?>',
            method: 'GET',
            dataType: 'json',
            data: {
                id: id
            },
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
                if (response.status == "success") {
                    if (Array.isArray(response.data)) {
                        var rowCount = 0;
                        $('#table tbody').empty();
                        $.each(response.data, function(indexInArray, items) {
                            $('#no_permintaan').text(items.no_permintaan);
                            $('#penerima').text(items.penerima);

                            if (items.status == 'Y') {
                                $('#btnCancel, #btnDraft, #btnAccept').hide();
                                $('#btnExportPdf').show();
                            } else {
                                $('#btnCancel, #btnDraft, #btnAccept').show();
                                $('#btnExportPdf').hide();
                            }

                            $('#btnCancel').attr('data-permintaan', items.no_permintaan);
                            $('#btnDraft').attr('data-permintaan', items.no_permintaan);
                            $('#btnAccept').attr('data-permintaan', items.no_permintaan);
                            $('#btnExportPdf').attr('data-permintaan', items.no_permintaan);

                            var contenteditable = (items.status == 'Y') ? 'readonly' : 'true';
                            var textColor = (items.status == 'Y') ? 'text-black' : 'text-success';

                            $('#table tbody').append(`
                                <tr class="text-black">
                                    <td class="text-center text-black align-middle">${++rowCount}</td>
                                    <td class="text-center text-black align-middle">${items.barang_nama}</td>
                                    <td class="text-center text-black align-middle">${items.satuan}</td>
                                    <td class="text-center text-black align-middle">${items.stok_akhir}</td>
                                    <td class="text-center text-black align-middle">${items.stok_diberikan}</td>
                                    <td class="text-center align-middle ${textColor} fw-bolder" contenteditable="${contenteditable}" onblur="updateValue(this,'${items.no_permintaan}','${items.barang_id}', $(this).text())">${items.verifikasi_stok == 0 ? 0 : items.verifikasi_stok}</td>
                                </tr>
                            `);
                        });
                    }
                }
            }
        });
    }

    $('#btnExportPdf').click(function(e) {
        e.preventDefault();
        var no_permintaan = $(this).attr('data-permintaan');
        $.ajax({
            type: "get",
            url: "<?= base_url('Logistik/Gudang/CekPermintaan/modal_export_pdf') ?>",
            data: {
                no_permintaan: no_permintaan
            },
            dataType: "json",
            success: function(response) {
                $('.modal-place').html(``).html(response.html);
                $('.modal-place #modal').modal('show');
            }
        });
    });

    function updateValue(element, no_permintaan, barang_id, value) {
        if (!/^\d+$/.test(value) && value != '') {
            $(element).text('');
            Swal.fire({
                icon: 'warning',
                title: 'Perhatian!!',
                text: 'Inputan Harus Angka',
            });
        } else {
            Swal.fire({
                title: 'Verifikasi Stok',
                text: "Apakah anda yakin ingin memverifikasi stok ini?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya',
                cancelButtonText: 'Tidak'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '<?= base_url('Logistik/Gudang/CekPermintaan/master') ?>',
                        method: 'POST',
                        dataType: 'json',
                        data: {
                            no_permintaan: no_permintaan,
                            barang_id: barang_id,
                            verifikasi_stok: value
                        },
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
                            const notyf = new Notyf();
                            if (response.status == "success") {
                                notyf.success({
                                    message: "Berhasil verifikasi stok",
                                    duration: 3000,
                                    position: {
                                        x: "right",
                                        y: "top"
                                    },
                                    icons: {
                                        success: {
                                            className: "fa fa-check",
                                            color: "#26b99a"
                                        }
                                    }
                                });
                                cekPermintaan(no_permintaan);
                            } else if (response.status == "error") {
                                notyf.error({
                                    message: "Stok Tidak Mencukupi",
                                    duration: 3000,
                                    position: {
                                        x: "right",
                                        y: "top"
                                    },
                                    icons: {
                                        success: {
                                            className: "fa fa-times",
                                            color: "#26b99a"
                                        }
                                    }
                                });
                                cekPermintaan(no_permintaan);
                            }
                        }
                    });
                } else {
                    cekPermintaan(no_permintaan);
                }
            })
        }
    }
</script>
<?= $this->endSection() ?>