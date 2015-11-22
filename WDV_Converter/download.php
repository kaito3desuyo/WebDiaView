<?php 
include('oud_json_converter.php');
header("Content-Type: application/json; charset=utf-8");
convert_json("oud/hanshin_sanyo_0903.oud");
exit();
?>