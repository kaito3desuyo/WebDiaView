<?php
///////////////////////////////////
//Oudファイルを読み込み配列の形に整理します
///////////////////////////////////

function load_oud_file($filename){
	$filename = mb_convert_encoding($filename, "SJIS", "UTF-8");
	$content = file_get_contents($filename);
	$content = mb_convert_encoding($content, "UTF-8", "SJIS");
	
	$content = explode("\n", $content);//改行で配列に分割する
	$content = array_map('trim', $content);//空白削除
	//"."だけの列を削除
	//$content = array_filter($content, function($param){
	//	return $param !== ".";
	//});
	$content = array_values($content);//配列の番号を振り直し
	
	$oudArray = array();
	
	$Stanum = 0;
	$TrainTypenum = 0;
	
	for($i = 0; $i < count($content); $i++)
	{
		if($content[$i] === "Rosen."){
			//路線情報
			$RouteName = explode("=", $content[$i + 1]);
			$oudArray["RouteData"]["Name"] = $RouteName[1];
		}
		
		if($content[$i] === "Eki."){
			
			//駅情報
			$k = 1;
			while($content[$i + $k] !== "."){
				$tmp = explode("=", $content[$i + $k]);
				$param = StaData_param($tmp[0]);
				$value = StaData_value($tmp[1]);
				$oudArray["StaData"][$Stanum][$param] = $value;
				$k++;
			}
			$Stanum++;
		}
		
		if($content[$i] === "Ressyasyubetsu."){
			
			//種別情報
			$k = 1;
			while($content[$i + $k] !== "."){
				$tmp = explode("=", $content[$i + $k]);
				$param = TrainTypeData_param($tmp[0]);
				$value = TrainTypeData_value($tmp[1]);
				if($tmp[0] === "JikokuhyouFont"){
					$font1 = explode(";", $tmp[2]);
					$font2 = explode(";", $tmp[3]);
					$value = array("font-size" => $font1[0], "font-name" => $font2[0], "font-type" => $font2[1]);
				}
				$oudArray["TrainTypeData"][$TrainTypenum][$param] = $value;
				$k++;
			}
			//略称が存在しなければ、フルネームを代入する
			if(empty($oudArray["TrainTypeData"][$TrainTypenum]["shortname"])){
				$oudArray["TrainTypeData"][$TrainTypenum]["shortname"] = $oudArray["TrainTypeData"][$TrainTypenum]["fullname"];
			}
			$TrainTypenum++;
		}
		
		if($content[$i] === "Dia."){
			
			//ダイヤ情報
			$DiaName = explode("=", $content[$i + 1]);
			$oudArray["DiaData"][$DiaName[1]] = array("上り" => array(), "下り" => array());
						
			$k = $i + 1;
			$Dianum = 0;
			$direction = null;
			$directionbefore = null;
			while($content[$k] !== "Dia." and $content[$k] !== "DispProp."){

				if($content[$k] === "Ressya."){
				
					$j = $k + 1;
					while($content[$j] !== "."){
						$tmp = explode("=", $content[$j]);
						$param = DiaData_param($tmp[0]);
						$value = DiaData_value($tmp[1]);
						
						$directionbefore = $direction;//方向データを記憶しておく
						
						if($value === "up"){
							$direction = "上り";
						}elseif($value === "down"){
							$direction = "下り";
						}
						
						if($directionbefore !== $direction){
							$Dianum = 0;//方向データが従前と異なる場合、配列の値を初期化する
						}
						
						if($param === "time"){
							$time = explode(",", $value);
							for($row = 0; $row < count($time); $row++){
								$time[$row] = explode(";", $time[$row]);
							}
							$value = $time;
						}
						$oudArray["DiaData"][$DiaName[1]][$direction][$Dianum][$param] = $value;
						$j++;
					}
					//号数が存在しなければ、nullを代入する。存在していれば後ろに「号」を付ける
					if(empty($oudArray["DiaData"][$DiaName[1]][$direction][$Dianum]["Gousuu"])){
						$oudArray["DiaData"][$DiaName[1]][$direction][$Dianum]["Gousuu"] = null;
					}else{
						$oudArray["DiaData"][$DiaName[1]][$direction][$Dianum]["Gousuu"] .= "号";
					}
					$Dianum++;
				}
				$k++;
			}
			
		}
		
	}

	return $oudArray;
}

function select_dia_table($filename, $day, $direction){
	$data = load_oud_file($filename);
	
	$ret["RouteData"] = $data["RouteData"];
	$ret["StaData"] = $data["StaData"];
	$ret["TrainTypeData"] = $data["TrainTypeData"];
	$ret["DiaData"] = $data["DiaData"][$day][$direction];
	
	return $ret;
}

function create_dia_table($filename, $day, $direction){
	$data = load_oud_file($filename);
	
	$ret["RouteData"] = $data["RouteData"];
	$ret["StaData"] = $data["StaData"];
	$ret["TrainTypeData"] = $data["TrainTypeData"];
	$ret["DiaData"] = $data["DiaData"][$day][$direction];
	
?>
	<table>
		<tr>
			<td>列車番号</td>
			<?php foreach($ret["DiaData"] as $value): ?>
			<td><?=$value["trainnumber"]?></td>
			<?php endforeach; ?>
		</tr>
		<tr>
			<td>列車種別</td>
			<?php foreach($ret["DiaData"] as $value): ?>
			<td><?=$ret["TrainTypeData"][$value["traintype"]]["shortname"]?></td>
			<?php endforeach; ?>
		</tr>
		<tr>
			<td>列車名</td>
			<?php foreach($ret["DiaData"] as $value): ?>
			<td><?=$value["trainname"]?><?=$value["Gousuu"]?></td>
			<?php endforeach; ?>
		</tr>
	</table>
<?php

}


function StaData_param($str){
	switch(true){
		case $str === "Ekimei":
			return "name";
			break;
		case $str === "Ekijikokukeisiki":
			return "type";
			break;
		case $str === "Ekikibo":
			return "scale";
			break;
	}
}

function StaData_value($str){
	switch(true){
		case $str === "Jikokukeisiki_Hatsu":
			return "DepOnly";
			break;
		case $str === "Jikokukeisiki_Hatsuchaku":
			return "DepArr";
			break;
		case $str === "Jikokukeisiki_NoboriChaku":
			return "UpArr";
			break;
		case $str === "Jikokukeisiki_KudariChaku":
			return "DownArr";
			break;
		case $str === "Ekikibo_Ippan":
			return "Normal";
			break;
		case $str === "Ekikibo_Syuyou":
			return "Main";
			break;
		default:
			return $str;
			break;
	}
}

function TrainTypeData_param($str){
	switch(true){
		case $str === "Syubetsumei":
			return "fullname";
			break;
		case $str === "Ryakusyou":
			return "shortname";
			break;
		case $str === "JikokuhyouMojiColor":
			return "textcolor";
			break;
		case $str === "JikokuhyouFont":
			return "font";
			break;
		case $str === "DiagramSenColor":
			return "linecolor";
			break;
		case $str === "DiagramSenStyle":
			return "linetype";
			break;
		case $str === "StopMarkDrawType":
			return "stopmark";
			break;
	}
}

function TrainTypeData_value($str){
	switch(true){
		//種別ごとのカラー
		case ctype_xdigit($str):
			return "#".mb_substr($str, 2, 6, "UTF-8");
			break;
			
		//ダイヤグラムの線種
		case $str === "SenStyle_Jissen":
			return "solid";
			break;
		case $str === "SenStyle_Hasen":
			return "textcolor";
			break;
		case $str === "SenStyle_Tensen":
			return "linecolor";
			break;
		case $str === "SenStyle_Ittensasen":
			return "linetype";
			break;
			
		//ダイヤグラム上に停車駅を明示
		case $str === "EStopMarkDrawType_DrawOnStop":
			return true;
			break;
		case $str === "EStopMarkDrawType_Nothing":
			return false;
			break;
		default:
			return $str;
			break;
	}
}

function DiaData_param($str){
	switch(true){
		//上り下り
		case $str === "Houkou":
			return "direction";
			break;
		case $str === "Syubetsu":
			return "traintype";
			break;
		case $str === "Ressyabangou":
			return "trainnumber";
			break;
		case $str === "Ressyamei":
			return "trainname";
			break;
		case $str === "EkiJikoku":
			return "time";
			break;
		case $str === "Gousuu":
			return "Gousuu";
			break;
	}
}

function DiaData_value($str){
	switch(true){
		//上り下り
		case $str === "Nobori":
			return "up";
			break;
		case $str === "Kudari":
			return "down";
			break;
		default:
			return $str;
			break;
	}
}

create_dia_table("oud/kagoshimahonsen_1104.oud", "平日", "上り");
var_dump(select_dia_table("oud/kagoshimahonsen_1104.oud", "平日", "上り")["DiaData"])
?>