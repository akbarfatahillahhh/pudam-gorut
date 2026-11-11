<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title></title>
    <style>
        body {
            font-size: 11px !important;
            font-family: sans-serif !important;
        }

        .text-left {
            text-align: left !important;
        }

        .text-right {
            text-align: right !important;
        }

        .text-center {
            text-align: center !important;
        }

        .table table,
        .table td,
        .table th {
            border: 1px solid !important;
        }

        .table td,
        .table th {
            padding: 5px;
        }

        .table-no-border table {
            width: 100% !important;
            border-collapse: collapse !important;
        }

        .table table {
            width: 100% !important;
            border-collapse: collapse !important;
        }

        .w-100 {
            width: 100% !important;
        }

        .mb-0 {
            margin-bottom: 0px !important;
        }

        .mt-0 {
            margin-top: 0px !important;
        }

        hr {
            color: black !important;
        }

        .fw-semibold {
            font-weight: 550 !important;
        }

        .fw-bold {
            font-weight: 700 !important;
        }

        .fs-2 {
            font-size: 13px !important;
        }

        .fs-3 {
            font-size: 11px !important;
        }

        .pr-2 {
            padding-right: 15px !important;
        }
    </style>
</head>

<body>

    <div class="table-no-border">
        <table>
            <tr>
                <td class="text-right align-middle" width="20%">
                    <img src="<?= base_url() ?>/logo/perumda-gorut.png" class="pr-2" width="100">
                </td>
                <td class="align-middle">
                    <div class="fs-2 text-black fw-bold"><b>PERUSAHAAN UMUM DAERAH AIR MINUM</b></div>
                    <div class="fs-2 text-black fw-semibold"><b>TIRTA GERBANG EMAS</b></div>
                    <div class="fs-2 text-black fw-semibold"><b>KABUPATEN GORONTALO UTARA</b></div>
                    <div class="fs-3 text-black">Jl. Kasmat Lahay Titidu - Kwandang No. 17, Telp. (0442) 310077, Kode Pos 96252</div>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <hr>
                </td>
            </tr>
        </table>
    </div>

    <div>
        <?= $html ?? '' ?>
    </div>

</body>

</html>