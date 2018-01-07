<?php

require_once 'functions.php';
require_once 'OperationalToAnalyticalDataProcessor.php';

$processor = new OperationalToAnalyticalDataProcessor(getDbConnection('std'), 'ddl/move-operational-to-analytical.sql');
$processor->process();