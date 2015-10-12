<?php
///////////////////////////////////
//Oudファイルを読み込み配列の形に整理します
///////////////////////////////////
function load_oud_file_new($filename){
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
	$count = count($content);//配列数取得
	
	$columncount = -1;
	$dataname = null;
	$Direction = null;
	$oudArray = array();
	echo "MEMORY : " . number_format(memory_get_usage()) . " byte";
	
	//各情報の開始行を取得する
	$RouteRow = array_keys($content, "Rosen.");
	$StationRow = array_keys($content, "Eki.");
	$ClassRow = array_keys($content, "Ressyasyubetsu.");
	$DiaRow = array_keys($content, "Dia.");
	$TrainRow = array_keys($content, "Ressya.");
	$ConfigRow = array_keys($content, "DispProp.");
	
	echo "<br>MEMORY : " . number_format(memory_get_usage()) . " byte";
	
	foreach($ConfigRow as $i){
		//設定情報
		$k = 1;
		while($content[$i + $k] !== "."){
			$column = explode("=", $content[$i + $k]);
			if(!isset($column[1])){
				$column[1] = null;
			}
			$param = ConfigData_param($column[0]);
			$value = ConfigData_value($column[1]);
			if($column[0] === "JikokuhyouFont"){
				$font1 = explode(";", $column[2]);
				$font2 = explode(";", $column[3]);
				if(empty($column[4])){
					$column[4] = ";";
				}
				$font3 = explode(";", $column[4]);
			
				if(empty($font2[1])){
					$font2[1] = null;
				}
				if(empty($font3[1])){
					$font3[1] = null;
				}
				
				$value = array("font-size" => $font1[0], "font-name" => $font2[0], "font-type" => $font2[1].$font3[1]);
			}
			$oudArray["ConfigData"][$param][] = $value;
			unset($column);
			unset($font1);
			unset($font2);
			unset($font3);
			$k++;
		}
		break;
	}
	
	echo "<br>MEMORY : " . number_format(memory_get_usage()) . " byte";
	
	foreach($RouteRow as $i){
		//路線情報
		$RouteName = explode("=", $content[$i + 1]);
		$oudArray["RouteData"]["Name"] = $RouteName[1];
		unset($RouteName);
		break;
	}
	
	echo "<br>MEMORY : " . number_format(memory_get_usage()) . " byte";
	
	$Stanum = 0;
	foreach($StationRow as $i){
		//駅情報
		$k = 1;
		while($content[$i + $k] !== "."){
			$column = explode("=", $content[$i + $k]);
			if(!isset($column[1])){
				$column[1] = null;
			}
			$param = StaData_param($column[0]);
			$value = StaData_value($column[1]);
			$oudArray["StaData"][$Stanum][$param] = $value;
			unset($column);
			unset($param);
			unset($value);
			$k++;
		}
		$Stanum++;
		break;
	}
	
	echo "<br>MEMORY : " . number_format(memory_get_usage()) . " byte";
	
	$TrainTypenum = 0;
	foreach($ClassRow as $i){
		//種別情報
		$k = 1;
		while($content[$i + $k] !== "."){
			$column = explode("=", $content[$i + $k]);
			$param = TrainTypeData_param($column[0]);
			$value = TrainTypeData_value($column[1]);
			if($column[0] === "JikokuhyouFont"){
				$font1 = explode(";", $column[2]);
				$font2 = explode(";", $column[3]);
				$value = array("font-size" => $font1[0], "font-name" => $font2[0], "font-type" => $font2[1]);
			}
			$oudArray["TrainTypeData"][$TrainTypenum][$param] = $value;
			unset($column);
			unset($param);
			unset($value);
			unset($font1);
			unset($font2);
			$k++;
		}
		//略称が存在しなければ、フルネームを代入する。
		if(empty($oudArray["TrainTypeData"][$TrainTypenum]["shortname"])){
			$oudArray["TrainTypeData"][$TrainTypenum]["shortname"] = $oudArray["TrainTypeData"][$TrainTypenum]["fullname"];
		}
		
		$TrainTypenum++;
	}
	
	echo "<br>MEMORY : " . number_format(memory_get_usage()) . " byte";

	foreach($DiaRow as $i){
		
		//ダイヤ情報
		$DiaName = explode("=", $content[$i + 1]);
		$oudArray["DiaData"][$DiaName[1]] = array("上り" => array(), "下り" => array());
					
		
		$Dianum = 0;
		$direction = null;
		$directionbefore = null;
		
		$first = $DiaRow[0];
		$middle = $DiaRow[1];
		$end = $TrainRow[count($TrainRow) - 1];
		
		echo $first.".";
		echo $middle.".";
		echo $end."<br>";
		
		if($i < $middle){
			$checkmin = $first;
			$checkmax = $middle;
		}elseif($middle <= $i){
			$checkmin = $middle;
			$checkmax = $end;
		}

		$k = $i + 1;
		//var_dump($TrainRow);
		while($checkmin < $k and $k <= $checkmax){
			
			
			$k++;
		}
		
	}

	echo "<br>MEMORY : " . number_format(memory_get_usage()) . " byte";
	echo "<pre>";
	var_dump($oudArray);
	echo "</pre>";
	return $oudArray;
}


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
	$count = count($content);
	
	
	$oudArray = array();
	
	//先に設定情報だけを取得
	for($i = 0; $i < $count; $i++){
		if($content[$i] === "DispProp."){
			
			//設定情報
			$k = 1;
			while($content[$i + $k] !== "."){
				$tmp = explode("=", $content[$i + $k]);
				$param = ConfigData_param($tmp[0]);
				$value = ConfigData_value($tmp[1]);
				if($tmp[0] === "JikokuhyouFont"){
					$font1 = explode(";", $tmp[2]);
					$font2 = explode(";", $tmp[3]);
					if(empty($tmp[4])){
						$tmp[4] = ";";
					}
					$font3 = explode(";", $tmp[4]);
					
					if(empty($font2[1])){
						$font2[1] = null;
					}
					if(empty($font3[1])){
						$font3[1] = null;
					}
					
					$value = array("font-size" => $font1[0], "font-name" => $font2[0], "font-type" => $font2[1].$font3[1]);
				}
				$oudArray["ConfigData"][$param][] = $value;
				unset($tmp);
				unset($font1);
				unset($font2);
				unset($font3);
				$k++;
			}
			break;
		}
	}
	
	echo "<br>MEMORY : " . number_format(memory_get_usage()) . " byte";
	
	//路線・駅・列車情報の取得
	$Stanum = 0;
	$TrainTypenum = 0;
	for($i = 0; $i < $count; $i++)
	{		
		if($content[$i] === "Rosen."){
			//路線情報
			$RouteName = explode("=", $content[$i + 1]);
			$oudArray["RouteData"]["Name"] = $RouteName[1];
			unset($RouteName);
			break;
		}
	}
	
	echo "<br>MEMORY : " . number_format(memory_get_usage()) . " byte";
	
	for($i = 0; $i < $count; $i++)
	{
		
		if($content[$i] === "Eki."){
			//駅情報
			$k = 1;
			while($content[$i + $k] !== "."){
				$tmp = explode("=", $content[$i + $k]);
				$param = StaData_param($tmp[0]);
				$value = StaData_value($tmp[1]);
				$oudArray["StaData"][$Stanum][$param] = $value;
				unset($tmp);
				unset($param);
				unset($value);
				$k++;
			}
			$Stanum++;
		}
		
	}
	
	echo "<br>MEMORY : " . number_format(memory_get_usage()) . " byte";
	
	for($i = 0; $i < $count; $i++)
	{
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
				unset($tmp);
				unset($param);
				unset($value);
				unset($font1);
				unset($font2);
				$k++;
			}
			//略称が存在しなければ、フルネームを代入する。
			if(empty($oudArray["TrainTypeData"][$TrainTypenum]["shortname"])){
				$oudArray["TrainTypeData"][$TrainTypenum]["shortname"] = $oudArray["TrainTypeData"][$TrainTypenum]["fullname"];
			}
			
			$TrainTypenum++;
		}
			
	}
	
	echo "<br>MEMORY : " . number_format(memory_get_usage()) . " byte";
	
	for($i = 0; $i < $count; $i++)
	{
		
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
						unset($tmp);
						
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
								//時刻
								$time[$row] = explode(";", $time[$row]);
								if(empty($time[$row][1])){
									$time[$row][1] = null;
								}
								$time[$row] = array("stop" => $time[$row][0], "value" => $time[$row][1]);
								
								//着発で分ける
								if(preg_match("/\//", $time[$row]["value"])){
									//"/"がある場合
									$time[$row]["value"] = explode("/", $time[$row]["value"]);
									if(empty($time[$row]["value"][1])){
										$time[$row]["value"][1] = "";
									}
									$time[$row]["value"] = array("Arr" => $time[$row]["value"][0], "Dep" => $time[$row]["value"][1]);
								}else{
									//"/"がない場合
									$time[$row]["value"] = array("Arr" => "", "Dep" => $time[$row]["value"]);
								}
								
								//特定種別の時だけ全角数字にする（DiaProモード専用）
								$Classnum = $oudArray["DiaData"][$DiaName[1]][$direction][$Dianum]["traintype"];//種別No.問い合わせ
								if(isset($oudArray["TrainTypeData"][$Classnum]["font"])){
									$fontnum = $oudArray["TrainTypeData"][$Classnum]["font"];//フォントNo.問い合わせ
								
									if($oudArray["ConfigData"]["font"][$fontnum]["font-type"] === "Bold" or $oudArray["ConfigData"]["font"][$fontnum]["font-type"] === "BoldItalic"){
										if(!empty($time[$row]["value"]["Dep"])){
											$time[$row]["value"]["Dep"] =  mb_convert_kana($time[$row]["value"]["Dep"], "N");
										}
										if(!empty($time[$row]["value"]["Arr"])){
											$time[$row]["value"]["Arr"] =  mb_convert_kana($time[$row]["value"]["Arr"], "N");
										}
									}
								}
								
								//3ケタの場合は頭に空白をつける
								if(mb_strlen($time[$row]["value"]["Arr"], "UTF-8") === 3){
									$time[$row]["value"]["Arr"] = "&#45;".$time[$row]["value"]["Arr"];
								}
								if(mb_strlen($time[$row]["value"]["Dep"], "UTF-8") === 3){
									$time[$row]["value"]["Dep"] = "&#45;".$time[$row]["value"]["Dep"];
								}
								
							}
							
							$value = $time;
							unset($time);
						}
						
						$oudArray["DiaData"][$DiaName[1]][$direction][$Dianum][$param] = $value;
						$j++;
						unset($param);
						unset($value);
						
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
			break;
		}
		
	}
	echo "<br>MEMORY : " . number_format(memory_get_usage()) . " byte";
	unset($i);
	unset($k);
	unset($Stanum);
	unset($TrainTypenum);
	unset($Dianum);
	unset($row);

	return $oudArray;
}

function select_dia_table($filename, $day, $direction){
	$data = load_oud_file($filename);
	
	if($direction === "上り"){
		$data["StaData"] = array_reverse($data["StaData"]);
	}
	
	$ret["RouteData"] = $data["RouteData"];
	$ret["StaData"] = $data["StaData"];
	$ret["TrainTypeData"] = $data["TrainTypeData"];
	$ret["DiaData"] = $data["DiaData"][$day][$direction];
	$ret["ConfigData"] = $data["ConfigData"];
	
	return $ret;
}

function create_dia_table($filename, $day, $direction, $startcol, $endcol){

	$data = load_oud_file_new($filename);
	
	
	if($direction === "上り"){
		$data["StaData"] = array_reverse($data["StaData"]);
	}
	
	$ret["RouteData"] = $data["RouteData"];
	$ret["StaData"] = $data["StaData"];
	$ret["TrainTypeData"] = $data["TrainTypeData"];
	$ret["DiaData"] = $data["DiaData"][$day][$direction];
	
	unset($data);
	
	//カラム数の設定
	if($endcol === "max"){
		$endcol = count($ret["DiaData"]);
	}elseif($endcol > count($ret["DiaData"])){
		$endcol = count($ret["DiaData"]);
	}
	
?>
	<table class="oud_to_Timetable">
		<tr>
			<td colspan="2">列車番号</td>
			<?php for($i = $startcol; $i < $endcol; $i++): ?>
			<td class="tnum" style="color:<?=$ret["TrainTypeData"][$ret["DiaData"][$i]["traintype"]]["textcolor"]?>"><?=$ret["DiaData"][$i]["trainnumber"]?></td>
			<?php endfor; ?>
		</tr>
		<tr>
			<td colspan="2">列車種別</td>
			<?php for($i = $startcol; $i < $endcol; $i++): ?>
			<td class="ttype" style="color:<?=$ret["TrainTypeData"][$ret["DiaData"][$i]["traintype"]]["textcolor"]?>"><?=$ret["TrainTypeData"][$ret["DiaData"][$i]["traintype"]]["shortname"]?></td>
			<?php endfor; ?>
		</tr>
		<tr>
			<td colspan="2">列車名</td>
			<?php for($i = $startcol; $i < $endcol; $i++): ?>
			<td class="tname" style="color:<?=$ret["TrainTypeData"][$ret["DiaData"][$i]["traintype"]]["textcolor"]?>">
				<?php 
					$output = $ret["DiaData"][$i]["trainname"].$ret["DiaData"][$i]["Gousuu"];
					foreach(preg_split("//u", $output, -1, PREG_SPLIT_NO_EMPTY) as $value){
						echo $value."<br>";
					}
				?>	
			</td>
			<?php endfor; ?>
		</tr>
		
		<?php for($i = 0; $i < count($ret["StaData"]); $i++): ?>
		
		<?php if($ret["StaData"][$i]["type"] === "DepArr" or ($ret["StaData"][$i]["type"] === "UpArr" and $direction === "上り") or ($ret["StaData"][$i]["type"] === "DownArr" and $direction === "下り")): ?>
		<!-- 着時刻カラム -->
		<tr>
			<?php if($ret["StaData"][$i]["type"] === "DepArr"): ?>
			<td class="sname" rowspan="2"><?=$ret["StaData"][$i]["name"]?></td>
			<?php else: ?>
			<td class="sname"><?=$ret["StaData"][$i]["name"]?></td>
			<?php endif; ?>
			
			<td class="stype" style="border-bottom:1px solid #000;">着</td>
			
			<?php for($j = $startcol; $j < $endcol; $j++): ?>
			<td class="stime" style="border-bottom:1px solid #000; color:<?=$ret["TrainTypeData"][$ret["DiaData"][$j]["traintype"]]["textcolor"]?>">
			<?php //通過・経由なし・時刻なし
			switch(true){
				//運行なし
				case empty($ret["DiaData"][$j]["time"][$i]["stop"]):
					echo "&#x2025;";
					break;
				
				//発着駅カラム：経由なし
				case $ret["StaData"][$i]["type"] === "DepArr" 
				and isset($ret["DiaData"][$j]["time"][$i - 1]["stop"]) 
				and $ret["DiaData"][$j]["time"][$i - 1]["stop"] === "3"://着発駅表示・経由なし
					echo "&#124;";
					break;
				
				//発着駅カラム：運行なし
				case $ret["StaData"][$i]["type"] === "DepArr" 
				and $ret["DiaData"][$j]["time"][$i]["stop"] === "1" 
				and empty($ret["DiaData"][$j]["time"][$i]["value"]["Arr"]):
					echo "&#x2025;";
					break;
				
				case $ret["DiaData"][$j]["time"][$i]["stop"] === "1"://停車
					echo $ret["DiaData"][$j]["time"][$i]["value"]["Arr"];
					break;
				case $ret["DiaData"][$j]["time"][$i]["stop"] === "2"://通過
					echo "&#x2193;";
					break;
				case $ret["DiaData"][$j]["time"][$i]["stop"] === "3"://経由なし
					echo "&#124;";
					break;
				default:
					echo null;
					break;
			}
			?>
			</td>
			
			<?php endfor; ?>
		</tr>
		<?php endif; ?>
		
		<?php if($ret["StaData"][$i]["type"] === "DepArr" or $ret["StaData"][$i]["type"] === "DepOnly" or ($ret["StaData"][$i]["type"] === "UpArr" and $direction === "下り") or ($ret["StaData"][$i]["type"] === "DownArr" and $direction === "上り")): ?>
		<!-- 発時刻カラム -->
		<tr>
			<?php if($ret["StaData"][$i]["type"] !== "DepArr"): ?>
			<td class="sname"><?=$ret["StaData"][$i]["name"]?></td>
			<?php endif; ?>
			
			<td class="stype">発</td>
			
			<?php for($j = $startcol; $j < $endcol; $j++): ?>
			<td class="stime" style="color:<?=$ret["TrainTypeData"][$ret["DiaData"][$j]["traintype"]]["textcolor"]?>">
			<?php //通過・経由なし・時刻なし
			switch(true){
				//終点表示
				case $ret["StaData"][$i]["type"] === "DepOnly"//表示する駅カラムが発時刻のみで
				and isset($ret["StaData"][$i - 1]["type"])//一個前の駅カラムにデータが存在して
				and $ret["StaData"][$i - 1]["type"] !== "DepArr"//一個前の駅カラムが発着表示でなく
				and empty($ret["DiaData"][$j]["time"][$i]["stop"])//表示する駅カラムが運行なしで
				and isset($ret["DiaData"][$j]["time"][$i - 1]["stop"])//一個前の駅カラムにデータが存在して
				and $ret["DiaData"][$j]["time"][$i - 1]["stop"] === "1"://そこに停車するとき
					echo "&#61;";
					break;
				
				//運行なし
				case empty($ret["DiaData"][$j]["time"][$i]["stop"])://運行なし
					echo "&#x2025;";
					break;
				
				//発着駅カラム：通過
				case $ret["StaData"][$i]["type"] === "DepArr" 
				and $ret["DiaData"][$j]["time"][$i]["stop"] === "2"://着発駅表示・通過
					echo "&#x2193;";
					break;
				//発着駅カラム：経由なし
				case $ret["StaData"][$i]["type"] === "DepArr" 
				and isset($ret["DiaData"][$j]["time"][$i + 1]["stop"]) 
				and $ret["DiaData"][$j]["time"][$i + 1]["stop"] === "3"://着発駅表示・経由なし
					echo "&#124;";
					break;
				//発着駅カラム：運行なし
				case $ret["StaData"][$i]["type"] === "DepArr" 
				and empty($ret["DiaData"][$j]["time"][$i]["value"]["Dep"])://着発駅表示・運行なし
					echo "&#x2025;";
					break;
												
				//停車駅
				case empty($ret["DiaData"][$j]["time"][$i]["value"]["Dep"]) 
				and $ret["DiaData"][$j]["time"][$i]["stop"] === "1"://停車（終着）
					echo $ret["DiaData"][$j]["time"][$i]["value"]["Arr"];
					break;
				case $ret["DiaData"][$j]["time"][$i]["stop"] === "1"://停車
					echo $ret["DiaData"][$j]["time"][$i]["value"]["Dep"];
					break;
				
				//通過駅
				case $ret["DiaData"][$j]["time"][$i]["stop"] === "2"://通過
					echo "&#x2193;";
					break;
					
				//経由なし
				case $ret["DiaData"][$j]["time"][$i]["stop"] === "3"://経由なし
					echo "&#124;";
					break;
					
				default:
					echo null;
					break;
			}
			?>
			</td>
			
			<?php endfor; ?>
		</tr>
		<?php endif; ?>
		
		<?php endfor; ?>
		
	</table>
<?php
echo "MEMORY : " . number_format(memory_get_usage()) . " byte";
}

function Param_convert($str){
	switch(true){
		//駅情報関係
		case $str === "Ekimei":
			return "name";
			break;
		case $str === "Ekijikokukeisiki":
			return "type";
			break;
		case $str === "Ekikibo":
			return "scale";
			break;
		//種別情報関係
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
		case $str === "JikokuhyouFontIndex":
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
		//列車情報関係
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
		//設定情報関係
		case $str === "JikokuhyouFont":
			return "font";
			break;
		case $str === "DiaEkimeiFont":
			return "default_stafont";
			break;
		case $str === "DiaJikokuFont":
			return "default_timefont";
			break;
		case $str === "DiaRessyaFont":
			return "default_trainfont";
			break;
		case $str === "CommentFont":
			return "default_commentfont";
			break;
		case $str === "DiaMojiColor":
			return "default_textcolor";
			break;
		case $str === "DiaHaikeiColor":
			return "default_backgroundcolor";
			break;
		case $str === "DiaRessyaColor":
			return "default_traincolor";
			break;
		case $str === "DiaJikuColor":
			return "default_diagrambarcolor";
			break;
		case $str === "EkimeiLength":
			return "default_statextlength";
			break;
		case $str === "JikokuhyouRessyaWidth":
			return "default_timetextlength";
			break;
	}
}

function Value_convert($str){
	switch(true){
		//駅情報関係
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
		//種別情報関係
		//数字だけ（フォントインデックス）
		case is_numeric($str) and strlen($str) === 1:
			return $str;
		
		//種別ごとのカラー
		case ctype_xdigit($str):
			return "#".mb_substr($str, 6, 2, "UTF-8").mb_substr($str, 4, 2, "UTF-8").mb_substr($str, 2, 2, "UTF-8");
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
		//列車情報関係
		case $str === "Nobori":
			return "up";
			break;
		case $str === "Kudari":
			return "down";
			break;
		//設定情報関係
		//数字だけ（フォントインデックス）
		case is_numeric($str) and strlen($str) === 1:
			return $str;
		
		//種別ごとのカラー
		case ctype_xdigit($str):
			return "#".mb_substr($str, 6, 2, "UTF-8").mb_substr($str, 4, 2, "UTF-8").mb_substr($str, 2, 2, "UTF-8");
			break;
			
		default:
			return str_replace("\\", "", $str);
			break;
	}
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
		case $str === "JikokuhyouFontIndex":
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
		//数字だけ（フォントインデックス）
		case is_numeric($str) and strlen($str) === 1:
			return $str;
		
		//種別ごとのカラー
		case ctype_xdigit($str):
			return "#".mb_substr($str, 6, 2, "UTF-8").mb_substr($str, 4, 2, "UTF-8").mb_substr($str, 2, 2, "UTF-8");
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
			return str_replace("\\", "", $str);
			break;
	}
}

function ConfigData_param($str){
	switch(true){
		case $str === "JikokuhyouFont":
			return "font";
			break;
		case $str === "DiaEkimeiFont":
			return "default_stafont";
			break;
		case $str === "DiaJikokuFont":
			return "default_timefont";
			break;
		case $str === "DiaRessyaFont":
			return "default_trainfont";
			break;
		case $str === "CommentFont":
			return "default_commentfont";
			break;
		case $str === "DiaMojiColor":
			return "default_textcolor";
			break;
		case $str === "DiaHaikeiColor":
			return "default_backgroundcolor";
			break;
		case $str === "DiaRessyaColor":
			return "default_traincolor";
			break;
		case $str === "DiaJikuColor":
			return "default_diagrambarcolor";
			break;
		case $str === "EkimeiLength":
			return "default_statextlength";
			break;
		case $str === "JikokuhyouRessyaWidth":
			return "default_timetextlength";
			break;
	}
}

function ConfigData_value($str){
	switch(true){
		//数字だけ（フォントインデックス）
		case is_numeric($str) and strlen($str) === 1:
			return $str;
		
		//種別ごとのカラー
		case ctype_xdigit($str):
			return "#".mb_substr($str, 6, 2, "UTF-8").mb_substr($str, 4, 2, "UTF-8").mb_substr($str, 2, 2, "UTF-8");
			break;
		default:
			return $str;
			break;
	}
}

//create_dia_table("oud/kagoshimahonsen_1104.oud", "平日", "上り");
//var_dump(select_dia_table("oud/jr_hanwa_1101_01.oud", "平日", "上り", 0, 200)["TrainTypeData"]);
?>