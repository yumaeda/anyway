<?php

header('Content-Type: text/javascript;charset=utf-8');
header('Access-Control-Allow-Origin: http://anyway-grapes.jp/');

if ($_SERVER['REQUEST_METHOD'] === 'POST')
{
    exit();
}

require_once('dbreader.php');

$result = FALSE;
if (isset($_GET['dbTable']))
{
    $dbName = '';
    if (isset($_GET['dbName']))
    {
        $dbName = $_GET['dbName'];
    }

    $dbTable = $_GET['dbTable'];

    if (isset($_GET['id']))
    {
        $id = $_GET['id'];
        $result = executeSelectQuery($dbName, $dbTable, null, "id=$id", null);
    }
    else
    {
        if (isset($_GET['condition']))
        {
            $orderBy = '';
            if (isset($_GET['orderBy']))
            {
                $orderBy = $_GET['orderBy'];
            }

            $condition = stripcslashes($_GET['condition']);
            $result = executeSelectQuery($dbName, $dbTable, null, $condition, $orderBy);
        }
        else
        {
            $result = executeSelectAllQuery($dbName, $dbTable);
        }
    }
}

$jsonResponse = generateJsonResponse($result);

// Free the result set.
if ($result !== FALSE)
{
    mysqli_free_result($result);
}

if (isset($_GET['xDomainCallback']))
{
    $callback = $_GET['xDomainCallback'];
    echo $callback . '(' . $jsonResponse . ')';
}
else
{
    echo $jsonResponse;
}
