<?php

$dbTables = array(
    "orders" => array(
        "order_id",
        "customer_id",
        "delivery_date",
        "delivery_time",
        "refrigerated",
        "date_created",
        "url",
        "comment",
        "status",
        "payment_method"
    ),

    "wines" => array(
        "barcode_number",
        "cepage",
        "region",
        "region_jpn",
        "type",
        "price",
        "country",
        "producer",
        "producer_jpn",
        "vintage",
        "store_price",
        "name",
        "name_jpn",
        "comment",
        "stock",
        "apply"
    ),

    "stocks" => array(
        "barcode_number",
        "stock"
    ),

    "star_wines" => array(
        "barcode_number"
    )
);

?>
