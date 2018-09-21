<?php

if ($_SERVER['REQUEST_METHOD'] == 'POST')
{
    $apiUri       = 'https://api.instagram.com/v1/users/self/media/recent';
    $access_token = '1944835304.3df933a.563e448baa0a414c9e8aad24b1860c3a';

    echo @file_get_contents("$apiUri?access_token=$access_token&count=20");
}

?>
