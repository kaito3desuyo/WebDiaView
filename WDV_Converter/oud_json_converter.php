<?php
function upload_file(){
	///////////////////////
	//ファイルアップロード処理部//
	///////////////////////
	try{
		if(!isset($_FILES['upfile']['error']) || !is_int($_FILES['upfile']['error'])){
			throw new RuntimeException("不正なパラメータです。管理人にお問い合わせください。");
		}
		
		switch($_FILES["upfile"]["error"]){
			case UPLOAD_ERR_OK:
				break;
			case UPLOAD_ERR_NO_FILE:
				throw new RuntimeException("ファイルが選択されていません。");
				break;
			case UPLOAD_ERR_INI_SIZE:
			case UPLOAD_ERR_FORM_SIZE:
				throw new RuntimeException("ファイルサイズが許容値を超えています。");
				break;
			default:
				throw new RuntimeException("不明なエラーが発生しました。");
		}
		
		$finfo = new finfo(FILEINFO_MIME_TYPE);
		if(
			!array_search($finfo->file($_FILES["upfile"]["tmp_name"]), array("oud" => 'text/plain'), true)
			|| pathinfo($_FILES["upfile"]["name"])["extension"] !== "oud"
		){
			throw new RuntimeException("oudファイルではありません。");
		}
		
		$content = file_get_contents($_FILES["upfile"]["tmp_name"]);
		if(!preg_match("/FileType=OuDia/", $content)){
			throw new RuntimeException("oudファイルですが、書式が正しくありません。");
		}
		unset($content);
		
		loading_file($_FILES["upfile"]["tmp_name"]);
		
	}catch(Exception $e){
		echo "エラーが発生しました：".$e->getMessage();
		exit();
	}
}

function loading_file($filename){
	////////////////////////////
	//ファイル読み込み部          //
	//@param $filename ファイル名//
	////////////////////////////
	$filename = mb_convert_encoding($filename, "SJIS", "UTF-8");
	$content = file_get_contents($filename);
	$content = mb_convert_encoding($content, "UTF-8", "SJIS");
	
	$content = explode("\n", $content);
	$content = array_map('trim', $content);
	
	$oudArray = Array();
	
	///////////////
	//ファイル解析部//
	///////////////
	
	//各情報の開始行を取得する
	$RouteRowStart = array_keys($content, "Rosen.");
	$StationRowStart = array_keys($content, "Eki.");
	$ClassRowStart = array_keys($content, "Ressyasyubetsu.");
	$DiaRowStart = array_keys($content, "Dia.");
	$TrainRowStart = array_keys($content, "Ressya.");
	$ConfigRowStart = array_keys($content, "DispProp.");
	
	foreach($RouteRowStart as $i){
		//路線情報
		$RouteName = explode("=", $content[$i + 1]);
		$oudArray["RouteConfig"]["RouteName"] = $RouteName[1];
		unset($RouteName);
		break;
	}
	
	foreach($StationRowStart as $i){
		//駅情報
		$k = $i + 1;
		while($content[$k] !== "."){
			$result = explode("=", $content[$k]);
			$Param = Param_Analyse($result[0]);
			$Value = Value_Analyse(str_replace("\\","",$result[1]));
			$oudArray["RouteConfig"]["Station"][$i][$Param] = $Value;
			
			unset($result);
			unset($Param);
			unset($Value);
			$k++;
		}
		
	}
	$oudArray["RouteConfig"]["Station"] = array_values($oudArray["RouteConfig"]["Station"]);//配列の番号を振り直し
	
	foreach($ClassRowStart as $i){
		//種別情報
		$k = $i + 1;
		while($content[$k] !== "."){
			$result = explode("=", $content[$k]);
			$Param = Param_Analyse($result[0]);
			$Value = Value_Analyse(str_replace("\\","",$result[1]));
			$oudArray["ClassConfig"][$i][$Param] = $Value;
			
			//略称が存在しなければ、フルネームを代入する。
			if(empty($oudArray["ClassConfig"][$i]["ShortName"])){
				$oudArray["ClassConfig"][$i]["ShortName"] = $oudArray["ClassConfig"][$i]["FullName"];
			}
			
			unset($oudArray["ClassConfig"][$i]["JikokuhyouFontIndex"]);
			unset($oudArray["ClassConfig"][$i]["StopMarkDrawType"]);
			unset($result);
			unset($Param);
			unset($Value);
			$k++;
		}
		
	}
	$oudArray["ClassConfig"] = array_values($oudArray["ClassConfig"]);//配列の番号を振り直し
	
	foreach($DiaRowStart as $i){
		//ダイヤ情報
		$DiaName = explode("=", $content[$i + 1]);
		$oudArray["DiagramConfig"][] = array("DiaName" => $DiaName[1], "Inbound" => array(), "Outbound" => array());
	}
	
	foreach($TrainRowStart as $i){
		//列車情報
		if($i < $DiaRowStart[1]){
			$DiaName = explode("=", $content[$DiaRowStart[0] + 1])[1];
		}else{
			$DiaName = explode("=", $content[$DiaRowStart[1] + 1])[1];
		}

		$k = $i + 1;
		$Direction = null;
		while($content[$k] !== "."){
			$result = explode("=", $content[$k]);

			//上下判別
			if($result[0] === "Houkou" && $result[1] === "Kudari"){
				$Direction = "Outbound";
			}elseif($result[0] === "Houkou" && $result[1] === "Nobori"){
				$Direction = "Inbound";
			}
			
			$Param = Param_Analyse($result[0]);
			$Value = Value_Analyse(str_replace("\\","",$result[1]));
			
			//時刻情報
			if($Param === "Time"){
				$Value = explode(",", $Value);
				
				$j = 0;
				foreach($Value as $val){
					//停通と時刻を分ける
					$tmp = explode(";", $val);
					if(empty($tmp[0])){
						$tmp[0] = "0";
					}
					
					if(!isset($tmp[1])){//時刻無しならtmp変数を初期化する
						$tmp[1] = null;
						$tmp[2] = null;
					}elseif(preg_match("/\//", $tmp[1])){//時刻が存在し、/で分けられているならスラッシュで再度分ける
						$tmp2 = explode("/", $tmp[1]);
						$tmp[1] = $tmp2[0];
						$tmp[2] = $tmp2[1];
					}else{//時刻が存在するが、出発時刻のみの場合はtmp1（着時刻）に代入されている時刻をtmp2へ
						$tmp[2] = $tmp[1];
						$tmp[1] = null;
					}
					
					$Value[$j] = array(	
										"Stop" => $tmp[0],
										"Arrive" => $tmp[1],
										"Departure" => $tmp[2]
										);
										
					unset($val);
					unset($tmp);
					unset($tmp2);
					$j++;
				}
			}
			
			for($j = 0; $j < count($oudArray["DiagramConfig"]); $j++){
				if($DiaName === $oudArray["DiagramConfig"][$j]["DiaName"]){
					$oudArray["DiagramConfig"][$j][$Direction][$i][$Param] = $Value;
				}
			}
					
			unset($result);
			unset($Param);
			unset($Value);
			$k++;
		}
		
		for($j = 0; $j < count($oudArray["DiagramConfig"]); $j++){
			$oudArray["DiagramConfig"][$j]["Inbound"] = array_values($oudArray["DiagramConfig"][$j]["Inbound"]);//配列の番号を振り直し
			$oudArray["DiagramConfig"][$j]["Outbound"] = array_values($oudArray["DiagramConfig"][$j]["Outbound"]);//配列の番号を振り直し
		}

	}

	convert_json($oudArray);
}

function convert_json($array){
	////////////////////////////
	//JSONファイル変換出力部     //
	//@param $filename ファイル名//
	////////////////////////////
	header("Content-Type: application/json; charset=utf-8");
	echo json_encode($array);
}

function Param_Analyse($str){
	switch(true){
		//駅関連
		case $str === "Ekimei":
			return "StationName";
		case $str === "Ekijikokukeisiki":
			return "Display";
		case $str === "Ekikibo":
			return "Scale";
		//種別情報関係
		case $str === "Syubetsumei":
			return "FullName";
			break;
		case $str === "Ryakusyou":
			return "ShortName";
			break;
		case $str === "JikokuhyouMojiColor":
			return "TextColor";
			break;
		case $str === "DiagramSenColor":
			return "LineColor";
			break;
		case $str === "DiagramSenStyle":
			return "LineType";
			break;
		//列車情報関係
		case $str === "Houkou":
			return "Direction";
			break;
		case $str === "Syubetsu":
			return "Class";
			break;
		case $str === "Ressyabangou":
			return "TrainNumber";
			break;
		case $str === "Ressyamei":
			return "TrainName";
			break;
		case $str === "EkiJikoku":
			return "Time";
			break;
		case $str === "Gousuu":
			return "TrainNo";
			break;
		default:
			return $str;
	}
}

function Value_Analyse($str){
	switch(true){
		//駅関連
		case $str === "Jikokukeisiki_NoboriChaku":
			return "Inbound-Arr";
		case $str === "Jikokukeisiki_KudariChaku":
			return "Outbound-Arr";
		case $str === "Jikokukeisiki_Hatsu":
			return "DepOnly";
		case $str === "Jikokukeisiki_Hatsuchaku":
			return "DepArr";
		case $str === "Ekikibo_Ippan":
			return "Normal";
		case $str === "Ekikibo_Syuyou":
			return "Main";
		//種別情報関係
		//数字だけ（フォントインデックス）
		case is_numeric($str) and strlen($str) === 1:
			return $str;
		
		//種別ごとのカラー
		case strlen($str) === 8 and ctype_xdigit($str):
			return "#".mb_substr($str, 6, 2, "UTF-8").mb_substr($str, 4, 2, "UTF-8").mb_substr($str, 2, 2, "UTF-8");
			break;
			
		//ダイヤグラムの線種
		case $str === "SenStyle_Jissen":
			return "solid";
			break;
		case $str === "SenStyle_Hasen":
			return "dashed";
			break;
		case $str === "SenStyle_Tensen":
			return "dotted";
			break;
		case $str === "SenStyle_Ittensasen":
			return "double";
			break;
		default:
			return $str;
	}
}
?>