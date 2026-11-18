<?php
/**
 * Utilitas JS bersama halaman Slip Gaji.
 *
 * Di-include di awal setiap sub-halaman supaya partial tetap mandiri kalau
 * dimuat sendiri lewat SlipGaji/page. Definisi dijaga agar tidak dobel saat
 * ketiganya dirender bersamaan di halaman tab.
 */
?>
<script>
    window.slipGajiEscape = window.slipGajiEscape || function(value) {
        return $('<div>').text(value === null || value === undefined ? '' : value).html();
    };

    window.slipGajiRupiah = window.slipGajiRupiah || function(value) {
        return (Number(value) || 0).toLocaleString('id-ID');
    };

    // DataTable di tab tersembunyi dihitung dengan lebar 0. Sesuaikan ulang
    // begitu tabnya tampil supaya kolom tidak menumpuk. Handler hanya dipasang
    // sekali walau partial ini di-include tiga kali.
    if (!window.slipGajiAdjustTabel) {
        window.slipGajiAdjustTabel = true;
        $(document).on('shown.bs.tab', 'button[data-bs-toggle="tab"]', function() {
            $($.fn.dataTable.tables({
                visible: true,
                api: true
            })).each(function() {
                this.columns.adjust();
            });
        });
    }
</script>
