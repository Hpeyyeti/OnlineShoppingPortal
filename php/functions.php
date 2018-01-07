<?php

require_once 'config.php';

function getDbConnection($profile = 'std') {
    $cfg = $GLOBALS['config']['database'][$profile];
    $dsn = sprintf("mysql:host=%s;port=%s;dbname=%s;charset=utf8", $cfg['dbHost'], $cfg['dbPort'], $cfg['dbName']);
    $dbh = new PDO($dsn,  $cfg['dbUser'], $cfg['dbPass'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    return $dbh;
}

/**
 * Prints a 3d array of strings(typical result of a db query) into an html table.
 *
 * @param string[][] $rows
 * @return string
 */
function renderHtmlTable(array $rows) {
    $columnNames = array_keys(current($rows));
    $headerCols = '';
    foreach ($columnNames as $columnName) {
        $headerCols .= sprintf("<th>%s</th>\n", htmlspecialchars($columnName));
    }
    $tableRows = [];
    foreach ($rows as $row) {
        $tds = '';
        foreach ($row as $col => $val) {
            $tds .= sprintf("<td>%s</td>\n", is_array($val)  ? renderHtmlTable($val) : htmlspecialchars($val));
        }
        $tableRows[] = "<tr>$tds</tr>\n";
    }

    return sprintf(
        "<table class='db-result-rows'>\n<thead><tr>%s</tr></thead><tbody>%s</tbody></table>\n",
        $headerCols,
        join("\n", $tableRows)
    );
}

/**
 * Calls all the public getter methods, putting the results
 * into a associative array with keys named like the properties the getter is retrieving.
 *
 * For example, if there's 2 getters named GetFirstName and GetEmail then the assoc array returned will be like:
 * ['FirstName' => 'foo', 'Email' => 'bar']
 *
 * @param $obj
 * @return array
 */
function toAssocArrayFromPublicGetters($obj)
{
    $class = new ReflectionClass(get_class($obj));
    $methods = $class->getMethods(ReflectionMethod::IS_PUBLIC);
    $assocArray = [];
    foreach ($methods as $method) {
        if (preg_match('/^get(.*)/', $method->getName(), $matches)) {
            $propertyName = $matches[1];
            $returnValue = $method->invoke($obj);
            $assocArray[$propertyName] = $returnValue;
        }
    }
    return $assocArray;
}

/**
 * same as toAssocArrayFromPublicGetters, but operates on an array.
 *
 * @param array $rows
 * @return array
 */
function convertAllObjectsToAssocArrayFromPublicGetters(array $rows)
{
    $newRows = [];
    foreach ($rows as $row) {
        $newRows[] = toAssocArrayFromPublicGetters($row);
    }
    return $newRows;
}