<?php
if(!isset($_GET['id'])){
exit;
}
$id = intval($_GET['id']);
require_once __DIR__."/../vendor/autoload.php";
use Picqer\Barcode\BarcodeGeneratorPNG;
$db = new MysqliDb();
$db->where("id" , $id);
$p = $db->getOne("products",['id','barcode']);
// var_dump($p);
$generator = new BarcodeGeneratorPNG();
$barcodeImage = base64_encode($generator->getBarcode($p['barcode'], $generator::TYPE_CODE_128));
$quantity = 20;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Document</title>
    <style>
        .main{
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
        }
        .barcode{
            width: auto;
            margin: 10px;
            border: 2px solid gray;
            padding: 5px;

        }
    </style>
</head>
<body>
    <h2>Printing <?= $quantity ;?> barcodes for: <?= htmlspecialchars($p['barcode']) ?></h2>
    <div class="main">
    <?php for ($i = 0; $i < $quantity; $i++): ?>
        <div class="barcode">
            <img src="data:image/png;base64,<?= $barcodeImage ?>" alt="Barcode <?= $p['barcode'] ?>" />
            <div><?= htmlspecialchars($p['barcode']) ?></div>
        </div>
    <?php endfor; ?>
    </div>

    <script>
        window.onload = () => {
            window.print();
        };
    </script>
</body>
</html>