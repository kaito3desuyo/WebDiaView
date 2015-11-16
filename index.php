<?php include('oud_load_api.php'); ?>

<!DOCTYPE html>
<html>
	<head>
	<style>
@font-face {
	font-family: DiaPro;
	  src: url("DiaPro-web/DiaPro-Regular.otf")  format('opentype'),
	       url("DiaPro-web/DiaPro-Regular.woff") format('woff'),
	       url("DiaPro-web/DiaPro-Regular.eot")  format('eot');
}

.oud_to_Timetable { border-collapse: collapse; border: 1px solid #333; margin: 0 }
.oud_to_Timetable td { white-space: nowrap; border: 0; padding: 0 1px 1px 1px; border-right: 1px solid #333; font-size: 13px; line-height: 1 }
.oud_to_Timetable .tnum { font-family: 'Times New Roman'; font-style: italic; text-align: center; font-size: 0.7em; letter-spacing: -1px; overflow: auto }
.oud_to_Timetable .sname { width: 4em; text-align: right; border-right: 0px; font-family: 'MS Mincho', 'ヒラギノ明朝', 'Hiragino Mincho', serif; }
.oud_to_Timetable .stype { font-family: 'MS Mincho', 'ヒラギノ明朝', 'Hiragino Mincho', serif; }
.oud_to_Timetable .stime { 
	text-align: center;
	width: 2em;
	font-family: DiaPro;
	-moz-font-feature-settings: "rlig=1";
	-msie-font-feature-settings: "rlig";
	-webkit-font-feature-settings: "rlig";
	-o-font-feature-settings: "rlig";
	font-feature-settings: "rlig";
}
.oud_to_Timetable .tname {
	text-align:center;
	font-family: 'MS Mincho', 'ヒラギノ明朝', 'Hiragino Mincho', serif;
	margin:0 auto;
}


</style>
	</head>
	<body>
	
		<h1>oudファイル読み込みスクリプト</h1>
		
		<?php create_dia_table("oud/hanshin_sanyo_0903.oud", "平日", "下り", 0, "max"); ?>
	
	</body>
</html>