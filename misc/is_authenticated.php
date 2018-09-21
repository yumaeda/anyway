<?php

session_start();

$fAuthenticated = 'FALSE';

if ($_SERVER['REQUEST_METHOD'] === 'GET')
{
    $userName = '';
    if (isset($_SESSION['user_id']) &&
        isset($_SESSION['user_name']))
    {
        $fAuthenticated = 'TRUE';
    }
}

echo $fAuthenticated;

?>
