<?php
// Set timezone to Bangladesh Standard Time
date_default_timezone_set('Asia/Dhaka');

require_once __DIR__."/../../vendor/autoload.php";
$db = new MysqliDb();
$barcode = $_GET['code'];
$db->where("barcode",$barcode);
$info = $db->getOne("products",['id','name','selling_price']);
echo json_encode($info);
?>