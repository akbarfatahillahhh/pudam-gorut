<?php

if (!function_exists('tanggal_angka_ke_text')) {
    function tanggal_angka_ke_text($tanggal)
    {
        // Format tanggal yang diharapkan: YYYY-MM-DD
        $bulan = [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember',
        ];

        // Pisahkan tanggal menjadi tahun, bulan, dan hari
        $tanggalParts = explode('-', $tanggal);

        if (count($tanggalParts) !== 3) {
            return 'Format tanggal tidak valid';
        }

        $tahun = $tanggalParts[0];
        $bulanIndex = (int)$tanggalParts[1];
        $hari = (int)$tanggalParts[2];

        // Pastikan bulan valid
        if (!isset($bulan[$bulanIndex])) {
            return 'Bulan tidak valid';
        }

        return $hari . ' ' . $bulan[$bulanIndex] . ' ' . $tahun;
    }
}
