<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);

$curDirPath = dirname(__FILE__);
$pdfDirPath = "$curDirPath/../../../../wholesale/receipts";

if (($_SERVER['REQUEST_METHOD'] === 'POST') &&
    isset($_POST['customer_id']))
{
    $cId              = $_POST['customer_id'];
    $targetPdfDirPath = "$pdfDirPath/$cId";

    if (is_array($_FILES)) 
    {
        if (isset($_FILES['userFiles']['tmp_name']))
        {
            $cUserFile = count($_FILES['userFiles']['tmp_name']);

            for ($i = 0; $i < $cUserFile; ++$i)
            {
                if (is_uploaded_file($_FILES['userFiles']['tmp_name'][$i]))
                {
                    $sourcePath   = $_FILES['userFiles']['tmp_name'][$i];
                    $fileName     = $_FILES['userFiles']['name'][$i];
                    $targetPath   = "$targetPdfDirPath/$fileName";
                    $strExtension = strtolower(pathinfo($targetPath, PATHINFO_EXTENSION));

                    if (('pdf' === $strExtension) && move_uploaded_file($sourcePath, $targetPath))
                    {
                        echo "Uploaded $fileName<br />";
                    }
                    else
                    {
                        echo "Failed to upload $fileName<br />";
                    }
                }
                else
                {
                    echo "PDF #$i has not uploaded.<br />";
                }
            }
        }
    }
}

?>
