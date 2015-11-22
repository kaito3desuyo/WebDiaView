<?php 
include('oud_json_converter.php');
?>
<!DOCTYPE html>
<html>
<head>
<title>oudファイルをJSONファイルにコンバートするWebサイト</title>
</head>
<body>
<a href="download.php">ダウンロード</a>
<pre>
<?php var_dump(loading_file("oud/hanshin_sanyo_0903.oud")); ?>
</pre>
</body>
</html>