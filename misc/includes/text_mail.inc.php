<?php

function generateMailHeader($from, $cc)
{
    global $mimeBoundary;

    $strHeaders =
        "From: $from\n" .
        "Cc: $cc\n" .
        "MIME-Version: 1.0\n" .
        'Content-Type: text/plain; charset=ISO-2022-JP';

    return $strHeaders;
}

function sendTextMail($email, $from, $cc, $subject, $body)
{
    mb_internal_encoding('UTF-8');
    mb_language('ja');

    $subject = mb_encode_mimeheader($subject, 'ISO-2022-JP-MS');
    $message = mb_convert_encoding($body, 'ISO-2022-JP-MS');

    mail($email, $subject, $message, generateMailHeader($from, "$from,$cc"));
}

function sendMailAsPlainText($email, $customerName, $from, $cc, $subject, $body)
{
    if (!empty($customerName))
    {
        mb_internal_encoding('UTF-8');
        mb_language('ja');

        $email = mb_encode_mimeheader($customerName, 'ISO-2022-JP-MS') . "<$email>";
    }

    sendTextMail($email, $from, $cc, $subject, $body);
}
