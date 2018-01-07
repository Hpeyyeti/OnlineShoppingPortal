<?php

/**
 * Parse our xml config file and puts it into a php variable.
 */
$cfg = simplexml_load_file(__DIR__ . '/config.xml');

foreach ($cfg->database->children() as $connectionName => $element) {
    foreach ($element->attributes() as $k => $v) {
        $GLOBALS['config']['database'][$connectionName][$k] = (string) $v;
    }
}

unset($cfg);