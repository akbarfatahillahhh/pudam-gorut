<?php function penyebut($nilai)
{
    $nilai = (int) abs($nilai);
    $huruf = array("", "SATU", "DUA", "TIGA", "EMPAT", "LIMA", "ENAM", "TUJUH", "DELAPAN", "SEMBILAN", "SEPULUH", "SEBELAS");
    $temp = "";
    if ($nilai < 12) {
        $temp = " " . $huruf[$nilai];
    } else if ($nilai < 20) {
        $temp = penyebut($nilai - 10) . " BELAS";
    } else if ($nilai < 100) {
        $temp = penyebut(intdiv($nilai, 10)) . " PULUH" . penyebut($nilai % 10);
    } else if ($nilai < 200) {
        $temp = " SERATUS" . penyebut($nilai - 100);
    } else if ($nilai < 1000) {
        $temp = penyebut(intdiv($nilai, 100)) . " RATUS" . penyebut($nilai % 100);
    } else if ($nilai < 2000) {
        $temp = " SERIBU" . penyebut($nilai - 1000);
    } else if ($nilai < 1000000) {
        $temp = penyebut(intdiv($nilai, 1000)) . " RIBU" . penyebut($nilai % 1000);
    } else if ($nilai < 1000000000) {
        $temp = penyebut(intdiv($nilai, 1000000)) . " JUTA" . penyebut($nilai % 1000000);
    } else if ($nilai < 1000000000000) {
        $temp = penyebut(intdiv($nilai, 1000000000)) . " MILYAR" . penyebut($nilai % 1000000000);
    } else if ($nilai < 1000000000000000) {
        $temp = penyebut(intdiv($nilai, 1000000000000)) . " TRILIYUN" . penyebut($nilai % 1000000000000);
    }
    return $temp;
}

function terbilang($nilai)
{
    // Dibulatkan ke integer: tabel t_tagihan menyimpan jlh_tagihan/denda
    // sebagai varchar, jadi bisa masuk sebagai string berisi angka desimal.
    $nilai = (int) round((float) $nilai);

    if ($nilai < 0) {
        $hasil = "- " . trim(penyebut($nilai));
    } else {
        $hasil = trim(penyebut($nilai));
    }

    // Rapikan spasi ganda yang muncul dari gabungan " PULUH"/" RIBU" dll.
    return trim(preg_replace('/\s+/', ' ', $hasil));
}
