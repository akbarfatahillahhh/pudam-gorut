<div class="tab-pane fade" id="tab-pembayaran" role="tabpanel">
    <div class="row g-2 mb-2">
        <div class="col-lg-2 col-6">
            <label class="form-label">Bulan</label>
            <select id="pbBulan" class="form-select form-select-sm">
                <option value="ALL">Semua</option>
                <?php
                $namaBulan = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
                              'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
                for ($i = 1; $i <= 12; $i++): ?>
                    <option value="<?= $i ?>" <?= (int) date('n') === $i ? 'selected' : '' ?>><?= $namaBulan[$i] ?></option>
                <?php endfor; ?>
            </select>
        </div>
        <div class="col-lg-2 col-6">
            <label class="form-label">Tahun</label>
            <select id="pbTahun" class="form-select form-select-sm">
                <?php for ($i = (int) date('Y'); $i >= 2009; $i--): ?>
                    <option value="<?= $i ?>"><?= $i ?></option>
                <?php endfor; ?>
            </select>
        </div>
        <div class="col-lg-3 col-6">
            <label class="form-label">Kelurahan/Desa</label>
            <select id="pbKelurahan" class="form-select form-select-sm">
                <option value="0">[ Semua Kelurahan ]</option>
            </select>
        </div>
        <div class="col-lg-2 col-6 d-flex align-items-end">
            <button type="button" class="btn btn-sm btn-primary w-100" id="pbTampil">
                <i class="ti ti-search"></i> Tampilkan
            </button>
        </div>
        <div class="col-lg-3 d-flex align-items-end justify-content-end">
            <button type="button" class="btn btn-sm btn-success" id="pbBayar" disabled>
                <i class="fa fa-money-bill"></i> Lunasi Terpilih (<span id="pbJumlah">0</span>)
            </button>
        </div>
    </div>

    <div class="border rounded bg-light px-3 py-2 mb-2 fs-2 d-flex justify-content-between">
        <span>Total tagihan terpilih</span>
        <b id="pbTotal">Rp 0</b>
    </div>

    <div class="table-responsive" style="max-height: 55vh; overflow: auto;">
        <table id="tablePembayaran" class="table table-sm table-bordered border-dark align-middle mb-0">
            <thead class="text-black fs-2">
                <tr>
                    <th class="text-center" width="3%">
                        <input type="checkbox" id="pbPilihSemua" class="form-check-input">
                    </th>
                    <th class="text-center" width="10%">NO SAMBUNGAN</th>
                    <th>PELANGGAN</th>
                    <th width="14%">KELURAHAN</th>
                    <th class="text-center" width="8%">REKENING</th>
                    <th class="text-center" width="12%">JUMLAH</th>
                    <th class="text-center" width="8%">DENDA</th>
                    <th class="text-center" width="12%">TOTAL</th>
                </tr>
            </thead>
            <tbody class="text-black fs-2"></tbody>
        </table>
    </div>
</div>

<script>
(function() {
    let tabelPembayaran = null;

    function rupiah(n) {
        return 'Rp ' + Number(n || 0).toLocaleString('id-ID');
    }

    function hitungTerpilih() {
        let total = 0;
        let jumlah = 0;

        $('.pilih-tagihan:checked').each(function() {
            total += Number($(this).data('total') || 0);
            jumlah++;
        });

        $('#pbTotal').text(rupiah(total));
        $('#pbJumlah').text(jumlah);
        $('#pbBayar').prop('disabled', jumlah === 0);
    }

    $.ajax({
        type: 'get',
        url: "<?= base_url('Billing/Tagihan_data/kelurahan') ?>",
        dataType: 'json',
        success: function(list) {
            if (!Array.isArray(list)) {
                return;
            }

            $.each(list, function(i, item) {
                $('#pbKelurahan').append('<option value="' + item.id + '">' + item.text + '</option>');
            });

            $('#pbKelurahan').select2({
                width: '100%',
                dropdownParent: $('#tab-pembayaran'),
            });
        },
    });

    function muat() {
        if (tabelPembayaran) {
            tabelPembayaran.ajax.reload();
        }
    }

    tabelPembayaran = $('#tablePembayaran').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            type: 'post',
            url: "<?= base_url('Billing/Tagihan/tagihan_belum_lunas') ?>",
            data: function(d) {
                d.bulan = $('#pbBulan').val();
                d.tahun = $('#pbTahun').val();
                d.kelurahan = $('#pbKelurahan').val();
            },
        },
        columns: [
            { data: 'aksi', class: 'text-center align-middle', searchable: false, orderable: false },
            { data: 'no_sambungan', class: 'text-center align-middle' },
            { data: 'nama_pelanggan', class: 'align-middle text-uppercase' },
            {
                data: null,
                class: 'align-middle',
                searchable: false,
                render: function(data, type, row) {
                    return [row.kecamatan, row.kelurahan].filter(Boolean).join(' / ') || '-';
                },
            },
            { data: 'no_rek', class: 'text-center align-middle' },
            {
                data: 'jlh_tagihan',
                class: 'text-end align-middle',
                searchable: false,
                render: function(n) { return rupiah(n); },
            },
            {
                data: 'denda',
                class: 'text-end align-middle',
                searchable: false,
                render: function(n) { return Number(n) > 0 ? rupiah(n) : '-'; },
            },
            {
                data: 'total',
                class: 'text-end align-middle fw-semibold',
                searchable: false,
                render: function(n) { return rupiah(n); },
            },
        ],
        order: [[1, 'asc']],
        pageLength: 25,
        language: {
            emptyTable: 'Tidak ada tagihan belum lunas pada filter ini.',
            zeroRecords: 'Tidak ada tagihan.',
            processing: 'Memuat...',
            search: 'Cari:',
            lengthMenu: 'Tampil _MENU_ baris',
            info: 'Menampilkan _START_ sampai _END_ dari _TOTAL_ tagihan',
            infoEmpty: 'Tidak ada data',
            paginate: { previous: 'Sebelumnya', next: 'Berikutnya' },
        },
        drawCallback: function() {
            hitungTerpilih();
        },
    });

    $('#pbTampil').click(function() { muat(); });
    $('#pbBulan, #pbTahun, #pbKelurahan').on('change', muat);

    $(document).on('change', '.pilih-tagihan', hitungTerpilih);

    $('#pbPilihSemua').on('change', function() {
        $('.pilih-tagihan').prop('checked', this.checked);
        hitungTerpilih();
    });

    $('#pbBayar').click(function() {
        const items = [];

        $('.pilih-tagihan:checked').each(function() {
            items.push({ id_pemakaian: $(this).val(), tgl_bayar: '<?= date('Y-m-d') ?>' });
        });

        if (items.length === 0) {
            return;
        }

        Swal.fire({
            title: 'Konfirmasi Pelunasan',
            html: 'Lunasi <b>' + items.length + '</b> tagihan senilai <b>' + $('#pbTotal').text() + '</b>?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, Lunasi',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#198754',
        }).then(function(r) {
            if (!r.isConfirmed) {
                return;
            }

            $.ajax({
                type: 'post',
                url: "<?= base_url('Billing/Tagihan/simpan_pembayaran') ?>",
                data: { items: items },
                dataType: 'json',
                success: function(res) {
                    if (res.status !== 'success') {
                        Swal.fire('Gagal', res.msg, 'error');
                        return;
                    }

                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: res.msg,
                        showConfirmButton: false,
                        timer: 1800,
                    });

                    $('#pbPilihSemua').prop('checked', false);
                    muat();
                },
                error: function() {
                    Swal.fire('Gagal', 'Pelunasan gagal diproses.', 'error');
                },
            });
        });
    });
})();
</script>
