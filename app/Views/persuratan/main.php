<?= $this->extend('layouts/main_ekas') ?>
<?= $this->section('content') ?>

<div class="card border border-dark">
    <div class="card-body p-4">
        <div class="row mb-3">
            <div class="col-lg-8">
                <h5 class="card-title fw-semibold mb-1"><?= esc($title) ?></h5>
                <p class="text-muted mb-0 fs-2">
                    Daftar pengajuan izin, cuti, dan sakit pegawai beserta status verifikasinya.
                </p>
            </div>
            <div class="col-lg-4 text-end d-flex align-items-center justify-content-end">
                <button type="button" class="btn btn-sm btn-primary" id="btnPengajuan">
                    <i class="ti ti-mail-forward"></i> Buat Pengajuan
                </button>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-sm table-bordered border-dark text-nowrap mb-0 align-middle" id="datapengajuan"
                style="width: 100%;">
                <thead class="bg-dark">
                    <tr>
                        <th class="text-center align-middle text-white" width="3%">NO</th>
                        <th class="text-center align-middle text-white">TANGGAL</th>
                        <th class="text-center align-middle text-white">NAMA PEGAWAI</th>
                        <th class="text-center align-middle text-white">PENGAJUAN</th>
                        <th class="text-center align-middle text-white">TMT DIAJUKAN</th>
                        <th class="text-center align-middle text-white">RENCANA MASUK</th>
                        <th class="text-center align-middle text-white">KETERANGAN</th>
                        <th class="text-center align-middle text-white">STATUS</th>
                        <th class="text-center align-middle text-white" width="8%">AKSI</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td colspan="9" class="text-center">Tidak Ada Data</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal-place"></div>
<?= $this->endSection() ?>

<?= $this->section('script') ?>
<script>
/**
 * Bootstrap 5 di proyek ini dimuat tanpa plugin jQuery ($.fn.modal tidak
 * tersedia), jadi modal dibuka lewat API bootstrap langsung.
 */
function tampilModalPersuratan() {
    var el = document.getElementById('modal');
    if (!el) {
        return;
    }
    if (window.bootstrap && bootstrap.Modal) {
        bootstrap.Modal.getOrCreateInstance(el).show();
    } else if ($.fn.modal) {
        $(el).modal('show');
    }
}

function tutupModalPersuratan() {
    var el = document.getElementById('modal');
    if (!el) {
        return;
    }
    if (window.bootstrap && bootstrap.Modal) {
        bootstrap.Modal.getOrCreateInstance(el).hide();
    } else if ($.fn.modal) {
        $(el).modal('hide');
    }
}

$(document).ready(function() {
    $('#datapengajuan').DataTable({
        processing: true,
        serverSide: true,
        order: [
            [1, 'desc']
        ],
        ajax: {
            type: 'GET',
            url: "<?= base_url('Persuratan/data_pengajuan') ?>"
        },
        columns: [{
                data: null,
                class: 'text-center align-middle py-1 text-dark',
                orderable: false,
                searchable: false,
                defaultContent: '',
                render: function(data, type, row, meta) {
                    return meta.row + meta.settings._iDisplayStart + 1;
                }
            },
            {
                data: 'posting',
                class: 'text-center align-middle py-1 text-dark'
            },
            {
                data: 'nama_peg',
                class: 'text-start align-middle py-1 text-dark'
            },
            {
                data: 'jenis',
                class: 'text-center align-middle py-1 text-dark text-uppercase'
            },
            {
                data: 'terhitung_mulai',
                class: 'text-center align-middle py-1 text-dark'
            },
            {
                data: 'mulai_masuk',
                class: 'text-center align-middle py-1 text-dark'
            },
            {
                data: 'keterangan',
                class: 'text-start align-middle py-1 text-dark'
            },
            {
                data: 'persetujuan',
                class: 'text-center align-middle py-1 text-dark',
                render: function(data) {
                    if (data === 'Y') {
                        return '<span class="badge bg-success">Diterima</span>';
                    }
                    if (data === 'T') {
                        return '<span class="badge bg-danger">Ditolak</span>';
                    }
                    return '<span class="badge bg-primary">Pengajuan</span>';
                }
            },
            {
                data: 'aksi',
                class: 'text-center align-middle py-1 text-dark',
                orderable: false,
                searchable: false
            }
        ]
    });
});

function lihat(id) {
    $.ajax({
        type: 'POST',
        url: "<?= base_url('Persuratan/modal_verfak') ?>",
        data: {
            id: id
        },
        dataType: 'json',
        success: function(response) {
            if (response.status !== 'success') {
                Swal.fire('Gagal', response.message, 'error');
                return;
            }
            $('.modal-place').html(response.data);
            tampilModalPersuratan();
        },
        error: function() {
            Swal.fire('Gagal', 'Data pengajuan tidak dapat dimuat', 'error');
        }
    });
}

$('#btnPengajuan').click(function() {
    $.ajax({
        type: 'POST',
        url: "<?= base_url('Persuratan/modalsuratkeluar') ?>",
        dataType: 'json',
        success: function(response) {
            if (response.status !== 'success') {
                Swal.fire('Gagal', response.message, 'error');
                return;
            }
            $('.modal-place').html(response.data);
            tampilModalPersuratan();
        },
        error: function() {
            Swal.fire('Gagal', 'Form pengajuan tidak dapat dimuat', 'error');
        }
    });
});
</script>
<?= $this->endSection() ?>
