<?php

$curDirPath = dirname(__FILE__);
if ($_SERVER['REQUEST_METHOD'] === 'POST')
{
    $uploadfile = "$curDirPath/../../../stock_records.csv";

    if (move_uploaded_file($_FILES['csvFile']['tmp_name'], $uploadfile))
    {
        include_once("$curDirPath/sync.php");
    }
    else
    {
        echo "Possible file upload attack!\n";
    }
}

echo '
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-type" content="text/html;charset=UTF-8" />
    </head>
    <body>
        <form action="./upload.php" method="POST" enctype="multipart/form-data">
            <input id="fileSelect" type="file" name="csvFile" />
            <input id="submitBtn" type="submit" />
        </form>
    </body>
</html>';

?>
