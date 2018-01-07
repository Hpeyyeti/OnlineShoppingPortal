<?php
ini_set('memory_limit', '2G');

require_once 'functions.php';
require_once 'ProductDataImporter.php';

$file = 'external-datasets/products-500k.tsv';

$importer = new ProductDataImporter(getDbConnection(), $file);
$importer->import();