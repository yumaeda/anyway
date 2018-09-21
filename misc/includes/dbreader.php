<?php

require_once('defines.php');

function executeSelectAllQuery($strDB, $strTable)
{
    return executeSelectQuery($strDB, $strTable, null, null, null);
}

function executeSelectQuery($strDB, $strTable, $rgstrField, $strCondition, $strOrderBy)
{
    $dbName = DB_NAME;
    if ($strDB)
    {
        $dbName = $strDB;
    }

    $result = null;
    $query  = generateSelectQuery($strTable, $rgstrField, $strCondition, $strOrderBy);

    if (defined('DB_USER'))
    {
        $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, $dbName);
    }
    else
    {
        $mysqli = new mysqli(DB_SERVER, DB_LOGIN, DB_PWD, $dbName);
    }

    if (!($mysqli->connect_errno))
    {
        $mysqli->query("SET NAMES 'utf8'");
        $result = $mysqli->query($query);
        $mysqli->close();
    }

    return $result;
}

function generateSelectQuery($strTable, $rgstrField, $strCondition, $strOrderBy)
{
    $query = "SELECT ";

    if (is_null($rgstrField) || (count($rgstrField) == 0))
    {
        $query .= "*";
    }
    else
    {
        $query .= join(", ", $rgstrField);
    }

    $query .= " FROM $strTable";
    if (!is_null($strCondition))
    {
        $query .= " WHERE $strCondition";
    }

    if ($strOrderBy)
    {
        $query .= " ORDER BY $strOrderBy";
    }

    return $query;
}

function generateJsonResponse($result)
{
    $index    = 0;
    $response = '[';

    if ($result !== FALSE)
    {
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
                $response .= ', ';
            }

            $response .= '{';

            for ($i = 0; $i < count($fieldInfos); ++$i)
            {
                $colName = $fieldInfos[$i]->name;
                $colValue = $row[$colName];

                if ($i > 0)
                {
                    $response .= ', ';
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

            $response .= '}';
        }
    }

    $response .= ']';

    return $response;
}
