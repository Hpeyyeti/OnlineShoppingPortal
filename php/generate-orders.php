<?php

require_once 'functions.php';
require_once 'SimulatedOrderGenerator.php';
$orders = [];
if (!empty($_POST)) {
    $generator = new SimulatedOrderGenerator(getDbConnection());
    $minOrderDate = DateTimeImmutable::createFromFormat("Y-m-d", $_POST['minOrderDate']);
    $maxOrderDate = DateTimeImmutable::createFromFormat("Y-m-d", $_POST['maxOrderDate']);
    //$generator->cleanUpDb();
    $orders = $generator->generateOrders($_POST['numOrders'], $minOrderDate, $maxOrderDate);
}



?><!DOCTYPE html>
<html>
<head>
    <style>
        html {
            font-family: 'open sans', sans-serif;
        }
        table {
            border-collapse: collapse;
            border: 1px solid #ccc;
            margin: 1em;
            box-shadow: 1px 1px 4px #555;
        }
        td, th {
            border: 1px solid #ccc;
            padding: 4px;
            text-align: center;
        }

        .order {
            margin: 4em 2em;
        }

    </style>
</head>
<script src="web-static/jquery.js"></script>
<body>
<div class="wrapper">
    <section>
        <form action="" method="post">
            <p>Inputs:</p>
            <table class="inputRows">
                <tbody>
                <tr>
                    <td>Num Orders</td>
                    <td><input name="numOrders" value="5"></td>
                </tr>
                <tr>
                    <td>Min Order Date</td>
                    <td><input name="minOrderDate" value="2014-01-01"></td>
                </tr>
                <tr>
                    <td>Max Order Date</td>
                    <td><input name="maxOrderDate" value="2016-06-01"></td>
                </tr>
                </tbody>
            </table>

            <p><input type="submit" value="Create Orders"></p>
        </form>
    </section>
    <hr>
    <section>
        <div id="results">

<?php
    foreach ($orders as $i => $order) {
        $productHtml = renderHtmlTable(array_map(function ($r) {
            unset($r['SubCategoryId']);
            return $r;
        }, $order['ProductDataRows']));
        unset($order['ProductDataRows'], $order['PasswordHash']);
        $orderHtml = renderHtmlTable([$order]);
        ?>
        <div class="order">
            <div><?= $orderHtml ?></div>
            <div><?= $productHtml ?></div>
        </div>
        <hr>
        <?php
    }
?>

        </div>
    </section>
</div>


</body>
</html>