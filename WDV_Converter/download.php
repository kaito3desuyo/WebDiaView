<?php 
include('oud_json_converter.php');
header("Content-Type: application/json; charset=utf-8");
convert_json("oud/jr_hanwa_1101_01.oud");
exit();
?>