<?php
require_once("../../../wp-load.php");
$pid = $_GET["product"];
$_product = new WC_Product($pid);

//$_product->get_regular_price();
//$_product->get_sale_price();
//$_product->get_price();
$arr = array();
if ($_GET["product"] && $_GET["ajax"]) {
    //$pid = $_POST["product_id"]
    $_product = new WC_Product($pid);
    $ptitle = $_product->post->post_title;
    $pprice = $_product->get_price();
    $sku = $_product->get_sku();
    $arr["id"] = $pid;
    $arr["sku"] = $sku;
    $arr["name"] = $ptitle;
    $arr["price"] = $pprice;
    $attributes = $_product->get_attributes();

    foreach ($attributes as $attrib) {
        $attribs .= "'".$attrib["name"]."': '".$attrib["value"]."',";
        $keyName = $attrib["name"];
        $arr[$keyName] = $attrib["value"];
    }
    echo json_encode($arr);
    exit();
}

