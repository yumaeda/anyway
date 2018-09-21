<?php

require_once(UTIL);

function sendMail($email, $subject, $message, $from, $replyTo, $cc, $fUtf8)
{
    mb_internal_encoding('UTF-8');

    if (!$fUtf8)
    {
        $headers = "Content-type: text/html;charset=ISO-2022-JP" . "\n" .
                   "From: $from" . "\n" .
                   "Reply-To: $replyTo" . "\n" .
                   "Cc: $cc" . "\n" .
                   "X-Mailer: PHP/" . phpversion();

        $subject = mb_encode_mimeheader($subject, 'ISO-2022-JP-MS', 'UTF-8');
        $message = mb_convert_encoding($message, 'ISO-2022-JP-MS', 'UTF-8');

        mb_language('ja');
        mail($email, $subject, $message, $headers);
    }
    else
    {
        $headers = "Content-type: text/html" . "\n" .
                   "From: $from" . "\n" .
                   "Cc: $cc" . "\n" .
                   "X-Mailer: PHP/" . phpversion();

        mb_language('uni');
        mb_send_mail($email, $subject, $message, $headers);
    }
}

