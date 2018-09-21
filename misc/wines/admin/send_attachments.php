<?php

class AttachmentFile
{
    public $name;
    public $type;
    public $dirPath;
}

function sendMailWithAttachments($to, $from, $subject, $body, $rgobjAttachment)
{
    $semiRand     = md5(time());
    $mimeBoundary = "==Multipart_Boundary_x{$semiRand}x";
    $strHeaders   =
        "From: $from\n" .
        "MIME-Version: 1.0\n" .
        "Content-Type: multipart/mixed; boundary=\"{$mimeBoundary}\"";

    $emailMessage =
        "This is a multi-part message in MIME format.\n\n" .
        "--{$mimeBoundary}\n" .
        "Content-Type:text/plain; charset=\"ISO-8859-1\"\n" .
        "Content-Transfer-Encoding: 7bit\n\n" . $body . "\n\n";

    foreach ($rgobjAttachment as $objAttachment)
    {
        $filename = $objAttachment->name;
        $fileType = $objAttachment->type;
        $filePath = $objAttachment->dirPath . $filename;
        $file     = fopen($filePath, 'rb');
        $data     = fread($file, filesize($filePath));
        fclose($file);

        $data = chunk_split(base64_encode($data));

        $emailMessage .=
            "--{$mimeBoundary}\n" .
            "Content-Type: {$fileType};\n" .
            " name=\"{$filename}\"\n" .
            "Content-Disposition: attachment;\n" .
            " filename=\"{$filename}\"\n" .
            "Content-Transfer-Encoding: base64\n\n" .
            $data . "\n\n";
    }

    $emailMessage .= "--{$mimeBoundary}--\n";

    mail($to, $subject, $emailMessage, $strHeaders);
}

$wineCsv = new AttachmentFile();
$wineCsv->name    = 'wines.csv';
$wineCsv->type    = 'text/csv';
$wineCsv->dirPath = './';

$stockCsv       = new AttachmentFile();
$stockCsv->name = 'stocks.csv';
$stockCsv->type = 'text/csv';
$stockCsv->dirPath = './';

$rgobjCsv = array($wineCsv, $stockCsv);

sendMailWithAttachments(
    'mail@anyway-grapes.jp',
    'sysadm@anyway-grapes.jp',
    'Notification: CSVs for Yahoo! Japan Shopping',
    'CSVs for [Yahoo! Japan Shopping] site has been exported successfully.',
    $rgobjCsv
);

?>
