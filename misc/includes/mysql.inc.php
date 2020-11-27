<?php

if (!defined('DB_USER'))
{
    define('DB_USER', 'reader');
}

if (!defined('DB_PASSWORD'))
{
    define('DB_PASSWORD', 'readerP@ssw0rd');
}

if (!defined('DB_HOST'))
{
    define('DB_HOST', 'localhost');
}

if (!defined('DB_NAME'))
{
    define('DB_NAME', 'shop');
}

$dbc = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
mysqli_set_charset($dbc, 'utf8');

function prepareNextQuery($dbc)
{
    while ($dbc->more_results())
    {
        $dbc->next_result();
        if ($res = $dbc->store_result())
        {
            $res->free(); 
        }
    }
}

function escape_data($data, $dbc)
{
    return mysqli_real_escape_string($dbc, trim($data));
}

// Copied from dbreader.php (Need to remove the duplicate in the future).
function generateJsonResponse($result)
{
    $index    = 0;
    $response = "[";

    // Caches the field infos.
    $fieldInfos = array();
    while ($meta = mysqli_fetch_field($result))
    {
        array_push($fieldInfos, $meta);
    }

    while ($row = mysqli_fetch_assoc($result))
    {
        if ($index++ > 0)
        {
            $response .= ", ";
        }

        $response .= "{";

        for ($i = 0; $i < count($fieldInfos); ++$i)
        {
            $colName = $fieldInfos[$i]->name;
            $colValue = $row[$colName];

            if ($i > 0)
            {
                $response .= ", ";
            }

            $response .= "\"$colName\": ";

            if (($fieldInfos[$i]->type == 253) || // VARCHAR
                ($fieldInfos[$i]->type == 254) || // CHAR
                ($fieldInfos[$i]->type == 7))     // TIMESTAMP
            {
                // Strip backslash before quote.
                $colValue = stripslashes($colValue);

                // Return characters have to be escaped in json.
                $colValue = str_replace("\r", "\\r", $colValue);
                $colValue = str_replace("\n", "\\n", $colValue);

                $response .= json_encode($colValue);
            }
            else
            {
                $response .= $colValue;
            }
        }

        $response .= "}";
    }

    $response .= "]";

    return $response;
}
