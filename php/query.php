<?php

/**
 * This script lets you execute an arbitrary select query and show the results. Specify the file in the url like:
 *
 * localhost/226p1/project/query.php/?file=find-brands.sql
 */
    
require_once 'functions.php';


if (!isset($_GET['file'])) {
    echo "No ?file=foo.sql specified in url\n";
    exit;
}

$file = 'queries/' . basename($_GET['file']);
if (!is_readable($file)) {
    echo "Can't read query file\n";
    exit;
}
$params = isset($_GET['sqlParams']) ? (array) $_GET['sqlParams'] : [];

$sql = file_get_contents($file);
if (!empty($_GET['execute'])) {
    $dbh = getDbConnection();
    $positionalPlaceholdersOnly = preg_replace('/\:\w+/', '?', $sql);
    $stmt = $dbh->prepare($positionalPlaceholdersOnly);
    if (!$stmt->execute($params)) {
        echo "Query failed\n";
        exit;
    }
    $rows = $stmt->fetchAll();
    $resultHtmlTable = $rows ? renderHtmlTable($rows) : '';
} else {
    $resultHtmlTable = '';
}

?><!DOCTYPE html>
<html>
<head>
    <style>
        html {
            font-family: 'open sans', sans-serif;
        }
        table, th, td {
            border: 1px solid black;
            border-collapse: collapse;
        }
        th, td {
            padding: 15px;
        }
        td {
            text-align: center;
        }
        .wrapper {
            width: 80%;
            margin: auto;
        }
        #query-sql {
            padding: 2em;
            border: 1px solid #ccc;
            white-space: pre;
        }
        .mono, samp.placeholder {
            font: 1.2rem "Courier New", Courier, monospace;
        }
        .placeholder {
            padding: 2px;
        }
        .active {
            background-color: yellow;
        }
    </style>
</head>
<script src="web-static/jquery.js"></script>
<body>
<div class="wrapper">
    <section>
        <p>Sql:</p>
        <div id="query-sql" class="mono"><?= htmlspecialchars($sql) ?></div>
        <form action="" method="get">
            <div><input type="hidden" name="file" value="1"></div>
            <p>Inputs:</p>
            <table class="inputRows">
                <tbody>
                <tr>
                    <td>Placeholder Name</td>
                    <td>Value to Bind</td>
                </tr>
                </tbody>
            </table>
            <div><input type="hidden" name="execute" value="1"></div>
            <p><input type="submit" value="Execute"></p>
        </form>
    </section>
    <hr>
    <section>
        <div id="results">
            <?php
                if ($resultHtmlTable) {
                    echo "<h3>Results</h3>\n";
                    echo $resultHtmlTable;
                } else if (!empty($_GET['execute'])) {
                    echo "<h3>No Results</h3>\n";
                }

            ?>
        </div>
    </section>
</div>

<script>
    var submittedParams = <?= json_encode($_GET, JSON_HEX_TAG | JSON_HEX_AMP | JSON_PRETTY_PRINT) ?>;
    $(function () {
        var i = 0;
        var $tbl = $(".inputRows");
        var newSqlHtml = $("#query-sql").html().replace(/\?|:\w+/g, function(sqlPlaceholder) {
            var fieldName = sqlPlaceholder === "?" ? "? #" + (i + 1) : sqlPlaceholder;
            var $row = $("<tr><td class=field></td><td class=input><input name='sqlParams[]'></td></tr>");
            $row.find(".field").text(fieldName);
            $row.find("input").val((submittedParams.sqlParams || [])[i]);

            var idx = (function () {return i;})();
            $row.hover(function () {
                $(".placeholder" + idx).addClass("active");
            }, function () {
                $(".placeholder" + idx).removeClass("active");
            });

            $tbl.find("tbody").append($row);

            var placeholderHtml = "<samp class=placeholder" + i + ">" + sqlPlaceholder + "</samp>";
            i++;
            return placeholderHtml;
        });
        $("#query-sql").html(newSqlHtml);
        $("[name=file]").val(submittedParams.file);
    });
</script>
</body>
</html>