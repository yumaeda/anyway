<?php

error_reporting(E_ALL | E_STRICT);
ini_set('display_errors', 1);

if (!defined('DB_HOST'))
{
    define('DB_HOST', 'localhost');
}

if (!defined('DB_NAME'))
{
    define ('DB_NAME', 'shop');
}

if (!defined('DB_USER'))
{
    define('DB_USER', 'admin');
}

if (!defined('DB_PASSWORD'))
{
    define('DB_PASSWORD', 'winemasterP@ssw0rd');
}
