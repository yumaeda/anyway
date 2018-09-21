<?php

$fExportWines = false;
$bodyHtml     = '';

$curDirPath = dirname(__FILE__);
if ($_SERVER['REQUEST_METHOD'] === 'POST')
{
    $uploadfile = "$curDirPath/../../wines.csv";
    if (move_uploaded_file($_FILES['csvFile']['tmp_name'], $uploadfile))
    {
        include_once("$curDirPath/sync.php");
        $fExportWines = true;
    }
    else
    {
        echo "Possible file upload attack!\n";
    }

    // Script for generating alternative images for the wines w/o image.
    $imgCopyScript = "$curDirPath/copy_alt_wine_images.php";
    shell_exec("/usr/bin/nohup php $imgCopyScript > $imgCopyScript.log 2> $imgCopyScript.err &");
}
else
{
    $bodyHtml = '
        <form action="./upload.php" method="POST" enctype="multipart/form-data">
            <input id="fileSelect" type="file" name="csvFile" />
            <input id="submitBtn"  type="submit" />
        </form>
        <div style="margin-top:75px;">
            <a href="./resetSyncStatus.php">同期中のステータスを強制リセット</a>
        </div>
';
}

echo '
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-type" content="text/html;charset=UTF-8" />
';

if ($fExportWines)
{
    echo '<meta http-equiv="refresh" content="0; url=http://anyway-grapes.jp/wines/admin/export_wines.php" />';
}

echo "
    </head>
    <body>
        $bodyHtml
    </body>
</html>
";

?>
