<?php

$semiRand     = md5(time());
$mimeBoundary = "==Multipart_Boundary_x{$semiRand}x";

function generateMailHeader($from, $cc)
{
    global $mimeBoundary;

    $strHeaders =
        "From: $from\n" .
        "Cc: $cc\n" .
        "MIME-Version: 1.0\n" .
        "Content-Type: multipart/alternative; boundary=\"{$mimeBoundary}\"";

    return $strHeaders;
}

function sendMultipartMail($email, $from, $cc, $subject, $textMessage, $htmlMessage)
{
    global $mimeBoundary;
    mb_internal_encoding('UTF-8');

    $strCharset  = 'ISO-2022-JP';
    $noticeText  = "This is a multi-part message in MIME format.";

    $message =
        "$noticeText\n" .
        "\n" .
        "--{$mimeBoundary}\n" .
        "Content-Type:text/plain; charset=\"$strCharset\"\n" .
        "Content-Transfer-Encoding: 7bit\n" .
        "\n" .
        "$textMessage\n" .
        "\n" .
        "--{$mimeBoundary}\n" .
        "Content-Type:text/html; charset=\"$strCharset\"\n" .
        "Content-Transfer-Encoding: 7bit\n" .
        "\n" .
        "$htmlMessage\n" .
        "\n" .
        "--{$mimeBoundary}--\n";

    $subject = mb_encode_mimeheader($subject, 'ISO-2022-JP-MS', 'UTF-8');
    $message = mb_convert_encoding($message, 'ISO-2022-JP-MS', 'UTF-8');

    mb_language('ja');
    mail($email, $subject, $message, generateMailHeader($from, $cc));
}
