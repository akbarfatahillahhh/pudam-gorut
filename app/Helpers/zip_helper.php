<?php

function zipFile($filePath)
{
      $zip = new ZipArchive;
        if ($zip->open($fileZipPath, ZipArchive::CREATE || ZipArchive::OVERWRITE) === TRUE) {
            $zip->addFile($fileZipPath);
        $zip->close();
        echo 'ok';
    } else {
        echo 'failed';
    }
} 