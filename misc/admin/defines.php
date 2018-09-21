<?php

// Do not use the value of $_SERVER['DOCUMENT_ROOT'] since it does not work for IIS.
$_SERVER['DOCUMENT_ROOT'] =
    substr($_SERVER['SCRIPT_FILENAME'], 0, -strlen($_SERVER['SCRIPT_NAME']));

define (DB_HOST,     'localhost');
define (DB_USER,     'seiya_admin3');
define (DB_PASSWORD, 'anywayP@ssw0rd');
define (DB_NAME,     'seiya_anyway');

define (LOCAL_APP_DIR, $_SERVER['DOCUMENT_ROOT'] . '/');
define (MAX_SIZE, 400);
