<?php
//
//WDV Converter
//oud file convert to json file
//
class wdv_converter{
	
	//コンバート処理
	public function convert_execute(){
		$this->file_check();
		header("Content-Disposition: inline; filename=\"".$_FILES["upfile"]["name"].".json\"");
		header("Content-Type: application/octet-stream; charset=utf-8");
		$ary = $this->file_analysis();
		echo $this->array_to_json($ary);
		//echo "peak:".memory_get_peak_usage() / (1024 * 1024)."MB<br>";
	}
	
	//ファイルチェック
	private function file_check(){
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
		
			$fp = fopen($_FILES["upfile"]["tmp_name"], "r");
			if(!preg_match("/FileType=OuDia/", fgets($fp))){
				throw new RuntimeException("oudファイルですが、書式が正しくありません。");
			}
			fclose($fp);
				
		}catch(Exception $e){
			echo "エラーが発生しました：".$e->getMessage();
			exit();
		}
	}
	
	//ファイルの内容を一行ずつ取得する
	private function file_read_lines(){
		$oudArray = array();
		$fp = fopen($_FILES["upfile"]["tmp_name"], "r");
		while(($line = fgets($fp)) !== false){
			$str = mb_convert_encoding($line, "UTF-8", "SJIS");
			$str = trim($str);
			yield $str;
		}
		fclose($fp);
	}
	
	//取得したデータを解析し配列にする
	private function file_analysis(){
		$oudArray = array();
		$type = null;
		$num = -1;
		$num2 = -1;
		foreach($this->file_read_lines() as $line){
			switch(true){
				//パターンによりタイプを分ける
				case $line === "Rosen.":
					$type = "RouteConfig";
					break;
				case $line === "Eki.":
					if($type !== "Station"){
						$num = -1;
					}
					$type = "Station";
					$num++;
					break;
				case $line === "Ressyasyubetsu.":
					if($type !== "ClassConfig"){
						$num = -1;
					}
					$type = "ClassConfig";
					$num++;
					break;
				case $line === "Dia.":
					if($type !== "DiagramConfig" && $type !== "Train"){
						$num = -1;
					}
					$type = "DiagramConfig";
					$num++;
					break;
				case $line === "Kudari.":
					$direction = "Outbound";
					$num2 = -1;
					break;
				case $line === "Nobori.":
					$direction = "Inbound";
					$num2 = -1;
					break;
				case $line === "Ressya.":
					$type = "Train";
					$num2++;
					break;
				case preg_match("/KitenJikoku/", $line):
					$type = "Option";
					break;
				case $line === ".":
					break;
				default:
					//実際に配列に値を入れる
					$result = explode("=", $line);
					if(!isset($result[1])){
						$result[1] = null;
					}
					//各パターンで場合分け
					if($type === "RouteConfig"){
						$oudArray["RouteConfig"][$this->str_rewrite($result[0])] = $this->str_rewrite($result[1]);
					}
					if($type === "Station"){
						$result[1] = str_replace("\\","",$result[1]);
						$oudArray["RouteConfig"]["Station"][$num][$this->str_rewrite($result[0])] = $this->str_rewrite($result[1]);
					}
					if($type === "ClassConfig"){
						$oudArray["ClassConfig"][$num][$this->str_rewrite($result[0])] = $this->str_rewrite($result[1]);
						//略称が存在しなければ、フルネームを代入する。
						if(empty($oudArray["ClassConfig"][$num]["ShortName"])){
							$oudArray["ClassConfig"][$num]["ShortName"] = $oudArray["ClassConfig"][$num]["FullName"];
						}
					}
					if($type === "DiagramConfig"){
						$oudArray["DiagramConfig"][$num][$this->str_rewrite($result[0])] = $this->str_rewrite($result[1]);
					}
					if($type === "Train"){
						$result[1] = str_replace("\\","",$result[1]);
						$result[1] = $this->str_rewrite($result[1]);
						
						if(preg_match("/;/", $result[1])){
							$input = explode(",", $result[1]);
							$result[1] = array();
							foreach($input as $val){
								$tmp = explode(";", $val);
								if(empty($tmp[0])){
									$tmp[0] = "0";
								}
					
								if(!isset($tmp[1])){//時刻無しならtmp変数はnull
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
					
								$result[1][] = array(	
													"Stop" => $tmp[0],
													"Arrive" => $tmp[1],
													"Departure" => $tmp[2]
													);
								unset($input);
								unset($val);
								unset($tmp);
								unset($tmp2);
							}
						}
						
						$oudArray["DiagramConfig"][$num][$direction][$num2][$this->str_rewrite($result[0])] = $result[1];

					}
					unset($result);					
					break;
			}
		}
		
		//数値キーの配列であることを保証させる
		$oudArray["RouteConfig"]["Station"] = array_values($oudArray["RouteConfig"]["Station"]);
		$oudArray["ClassConfig"] = array_values($oudArray["ClassConfig"]);
		$oudArray["DiagramConfig"] = array_values($oudArray["DiagramConfig"]);
		foreach($oudArray["DiagramConfig"] as $key => $val){
			if(!isset($oudArray["DiagramConfig"][$key]["Inbound"])){
				$oudArray["DiagramConfig"][$key]["Inbound"] = array();
			}
			if(!isset($oudArray["DiagramConfig"][$key]["Outbound"])){
				$oudArray["DiagramConfig"][$key]["Outbound"] = array();
			}
			$oudArray["DiagramConfig"][$key]["Inbound"] = array_values($oudArray["DiagramConfig"][$key]["Inbound"]);
			$oudArray["DiagramConfig"][$key]["Outbound"] = array_values($oudArray["DiagramConfig"][$key]["Outbound"]);
		}
		
		return $oudArray;
		
	}
	
	//JSON変換
	private function array_to_json($ary){
		return json_encode($ary);
		//var_dump($ary);
	}
	
	//データの名前・値を任意に書き換える
	private function str_rewrite($str){
		/////
		//パラメーター
		/////
		switch(true){
			case $str === "Rosenmei":
				return "RouteName";
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
		/////
		//値
		/////
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
		/////
		//デフォルト
		/////
			default:
				return $str;
		}
	}
	
}

?>