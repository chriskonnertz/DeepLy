<?php

// This script reads a file and sends it to the client (browser) to be downloaded.
// Inspired by: https://linuxhint.com/download_file_php/

$filename = 'test_document_translated.pdf';

if(file_exists($filename)) {
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header("Cache-Control: no-cache, must-revalidate");
    header("Expires: 0");
    header('Content-Disposition: attachment; filename="'.basename($filename).'"');
    header('Content-Length: '.filesize($filename));
    header('Pragma: public');

    flush();

    readfile($filename);

    die();
} else {
    echo 'File "'.$filename.'" does not exist.';
}
