<?php

/*
 * Expected Variables
 *     $strName
 *     $strBody
 */

$strLang     = isset($_REQUEST['lang']) ? $_REQUEST['lang'] : 'ja';
$rootPageUrl = "//anyway-grapes.jp/store/index.php?pc_view=1&submenu=critics&lang=$strLang";

echo '
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-type" content="text/html;charset=UTF-8" />
        <title>' . $strName . '</title>
        <meta name="viewport" content="width=device-width, user-scalable=no" />
        <link rel="stylesheet" type="text/css" href="//anyway-grapes.jp/producers/header.css" />
        <link rel="stylesheet" type="text/css" href="//anyway-grapes.jp/producers/common.css" />
        <link rel="stylesheet" type="text/css" href="//anyway-grapes.jp/producers/body.css" />
        <link rel="stylesheet" type="text/css" media="only screen and (max-device-width: 480px)" href="//anyway-grapes.jp/producers/header_mobile.css" />
        <link rel="stylesheet" type="text/css" media="only screen and (max-device-width: 480px)" href="//anyway-grapes.jp/producers/body_mobile.css" />
        <script type="text/javascript">

        document.createElement(\'header\');

        </script>
    </head>
    <body>
        <header>
            <ul>
                <li>
                    <a href="' . $rootPageUrl . '" target="_parent">Wine Critics Info / <span class="jpnText">ワイン評価紙&評論家</span></a>
                </li>
                <li>&nbsp;&nbsp;&gt;&gt;</li>
                <li>
                    <strong>' . $strName . '</strong>
                </li>
            </ul>
        </header>
        <div class="contents">
            <h2>' . $strJpnName . '</h2>
            <p>' . $strBody . '</p>
        </div>
    </body>
</html>';
