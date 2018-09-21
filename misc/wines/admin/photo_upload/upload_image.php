<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);

$curDirPath = dirname(__FILE__);
require_once("$curDirPath/imgutils_core.php");

$targetImgDirPath = '../../../images/wines';

function resizeImage($srcImgPath, $newWidth, $fCompress)
{
    global $targetImgDirPath;

    $fileName             = basename($srcImgPath);
    $imgSrc               = createImg($fileName, $srcImgPath);
    list($width, $height) = getimagesize($srcImgPath);
    $newHeight            = ($height / $width) * $newWidth;

    $uploadedImg = imagecreatetruecolor($newWidth, $newHeight);
    imagealphablending($uploadedImg, false);
    imagesavealpha($uploadedImg, true);
    $transparent = imagecolorallocatealpha($uploadedImg, 255, 255, 255, 127);
    imagefilledrectangle($uploadedImg, 0, 0, $newWidth, $newHeight, $transparent);
    imagecopyresampled($uploadedImg, $imgSrc, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

    $targetPath = ($targetImgDirPath . '/' . $newWidth . 'px/' . $fileName);
    writeImg($uploadedImg, $targetPath, $fCompress);

    imagedestroy($imgSrc);
    imagedestroy($uploadedImg);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST')
{
    if (is_array($_FILES)) 
    {
        if (isset($_FILES['userFiles']['tmp_name']))
        {
            if (is_uploaded_file($_FILES['userFiles']['tmp_name']))
            {
                $sourcePath = $_FILES['userFiles']['tmp_name'];
                $fileName   = $_FILES['userFiles']['name'];
                $targetPath = "$targetImgDirPath/raw/$fileName";

                if (isSupportedExtension($fileName) &&
                    move_uploaded_file($sourcePath, $targetPath))
                {
                    resizeImage($targetPath, 400, true);
                    resizeImage($targetPath, 200, true);
                    resizeImage($targetPath, 100, true);

                    echo $fileName;
                }
                else
                {
                    echo "Failed to upload $fileName<br />";
                }
            }
            else
            {
                echo "Failed to upload the image.<br />";
            }
        }
    }
}

?>
