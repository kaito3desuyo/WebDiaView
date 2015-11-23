(function($){
	//WebDiaViewプラグイン
	//定義
	$.fn.WebDiaView = function(options){
		
		//デフォルト引数の設定
		var defaults = {
			text: 'This is WebDia View Plugin',
			path: 'js/json/STK.json',
			mode: 'AllRoute',
			type: '平日',
			direction: '下り',
			passmark: '&#x2193;',
			noviamark: '&#124;',
			noservicemark: '&#x2025;',
			finalstopmark: '&#61;',
			mainstamark: '&#x2500;',
			traincolumn: [
							["列車番号", "SelectDia[j].TrainNumber", "", false],
							["列車種別", "data.ClassConfig[SelectDia[j].Class].ShortName", "", false],
							["列車名", "SelectDia[j].TrainName + SelectDia[j].TrainNo", "", true],
						],
			stationcolumn: [
							["", ""],
							["営業キロ", ""]
						],
			yomiganamode: false
		};
		var setting = $.extend(defaults, options);
		
		//メイン処理関数
		var write_table = function(setting){
			//JSONデータの読み込み→テーブル描画
			$.getJSON(
				setting.path,
				eval("TableFormat_" + setting.mode)
			);
			$('table.wdv-table').css('white-space','nowrap')
		}
		
		function TableFormat_AllRoute(data){
			//時刻表表示　全線時刻表モード
			//データ概要を取得して表示する
			$('p.wdv-datainfo').html('データ名：' 　	+ data.DataName 	+ '<br>' +
									　'バージョン：'		+ data.Version 		+ '<br>' +
									　'最終更新日：' 	+ data.LastUpdate 	+ '<br>' +
									　'作成者：' 		+ data.Contributor
			);
			//各種変数指定
			var Stations = data.RouteConfig.Station;//駅情報
			if(setting.direction == "上り"){//上りダイヤ選択時は駅情報を逆順にする
				Stations = Stations.reverse();
			}
			var SelectDia = eval("data.DiagramConfig." + setting.type + "." + setting.direction);//ダイヤ情報
			
			//駅情報カラム
			//for(var i in setting.stationcolumn){
			//	var td = [];
			//	$('table.wdv-table').append("<tr></tr>");
			//	td.push("<td class=\"wdv-headercol\">" + setting.stationcolumn[i][0] + "</td>");
			//	$('table.wdv-table tr:eq(' + i +')').append(td);
			//}
			
			//列車情報カラム
			for(var i in setting.traincolumn){
				var td = [];
				$('table.wdv-table').append("<tr></tr>");
				td.push("<td colspan=\"2\" class=\"wdv-headercol\">" + setting.traincolumn[i][0] + "</td>");
				for(var j in SelectDia){
					var Address = eval(setting.traincolumn[i][1]);//変数参照先の取得
					var test = "";
					Address = traincolumn_validate_strings(Address);//無駄な空白と未定義の削除

					//縦書きtrueの場合は一文字ずつ分けて改行タグを仕込む
					if(setting.traincolumn[i][3] === true){
						var tmp = Address;
						Address = "";
						tmp.split('').forEach(function(str){
							if(!isFinite(str)){
								Address += str.replace(/ー/g, "｜") + "<br>";
							}else{
								Address += str;
							}
						});
					}
					
					//号数があれば、号を入れる
					if(setting.traincolumn[i][0] === "列車名"　&& SelectDia[j].TrainNo){
						Address = Address + "<br>号";
					}
					
					//DiaProモード
					if(setting.traincolumn[i][0] === "列車種別"　&& setting.yomiganamode === true){
						Address = yomiganamode(Address);
					}
					
					td.push("<td class=\"wdv-Traincol\" style=\"color:" + data.ClassConfig[SelectDia[j].Class].TextColor + ";\">" + Address + "</td>");
				}
				$('table.wdv-table tr:eq(' + i +')')[0].innerHTML = td.join("");
				var RowNum = Number(i) + 1;
			}
			
			//ヘッダーカラム・列車情報カラム
			//for(var i in setting.headercolumn){
			//	var td = [];
			//	$('table.wdv-table').append("<tr></tr>");
			//	for(var j in setting.headercolumn[i]){
			//		if(j == setting.headercolumn[i].length - 1){
			//			td.push("<td colspan=\"2\" class=\"wdv-headercol\">" + setting.headercolumn[i][j] + "</td>");
			//		}else{
			//			td.push("<td class=\"wdv-headercol\">" + setting.headercolumn[i][j] + "</td>");
			//		}
			//		var ColNum = Number(j) + 1;
			//	}
			//	for(var k in SelectDia){
			//		td.push("<td class=\"wdv-TrainNumbercol\" style=\"color:" + data.ClassConfig[SelectDia[k].Class].TextColor + ";\">" + undefined_to_space(eval(setting.headcol_train[i])) + "</td>");//列車番号
			//	}
			//	$('table.wdv-table tr:eq(' + i +')')[0].innerHTML = td.join("");
			//	var RowNum = Number(i) + 1;
			//}

			//一段目
			//var td = [];
			//$('table.wdv-table').append("<tr></tr>");
			//td.push("<td class=\"wdv-headercol\" rowspan=\"2\">営業キロ</td>");//営業キロ
			//td.push("<td class=\"wdv-headercol\" colspan=\"2\">列車番号</td>");//列車番号
			//for(var i in SelectDia){
			//	td.push("<td class=\"wdv-TrainNumbercol\">" + undefined_to_space(SelectDia[i].TrainNumber) + "</td>");//列車番号
			//}
			//$('table.wdv-table tr:eq(0)')[0].innerHTML = td.join("");
			
			////二段目
			//var td = [];
			//$('table.wdv-table').append("<tr></tr>");
			//td.push("<td class=\"wdv-headercol\" colspan=\"2\">列車種別</td>");//列車種別
			//for(var i in SelectDia){
			//	td.push("<td class=\"wdv-Classcol\" style=\"color:" + data.ClassConfig[SelectDia[i].Class].TextColor + ";\">" + data.ClassConfig[SelectDia[i].Class].ShortName + "</td>");//列車種別
			//}
			//$('table.wdv-table tr:eq(1)')[0].innerHTML = td.join("");
			
			//メインカラム
			var Row = RowNum;//列車情報カラムの行数に応じて自動調整
			for(var i in data.RouteConfig.Station){
				
				var CSS_Class_kmCol = "wdv-kmcol ";//営業キロカラムCSSクラス初期設定
				var CSS_Class_StaNameCol = "wdv-StationNamecol ";//駅名カラムCSSクラス初期設定
				var CSS_Class_DepArrCol = "wdv-DepArrcol ";//発着表示カラムCSSクラス初期設定
				var CSS_Class_TimeCol = "wdv-Timecol ";//時刻カラムCSSクラス初期設定
				if(Stations[i].Kyoukaisen){
					//境界線クラスの追加
					if(setting.direction === "上り"){
						CSS_Class_kmCol = CSS_Class_kmCol + "wdv-Bordercol-Top";
						CSS_Class_StaNameCol = CSS_Class_StaNameCol + "wdv-Bordercol-Top";
						CSS_Class_DepArrCol = CSS_Class_DepArrCol + "wdv-Bordercol-Top";
						CSS_Class_TimeCol = CSS_Class_TimeCol + "wdv-Bordercol-Top";
					}else if(setting.direction === "下り"){
						CSS_Class_kmCol = CSS_Class_kmCol + "wdv-Bordercol-Bottom";
						CSS_Class_StaNameCol = CSS_Class_StaNameCol + "wdv-Bordercol-Bottom";
						CSS_Class_DepArrCol = CSS_Class_DepArrCol + "wdv-Bordercol-Bottom";
						CSS_Class_TimeCol = CSS_Class_TimeCol + "wdv-Bordercol-Bottom";
					}

				}
				
				//発着時刻表示の場合
				if(data.RouteConfig.Station[i].Display === "DepArr"){
					//着時刻
					var td = [];
					var DepArr = "Arrive";
					$('table.wdv-table').append("<tr></tr>");
					//td.push("<td class=\"" + CSS_Class_kmCol + "\" rowspan=\"2\">" + undefined_to_space(Stations[i].km) + "</td>");//営業キロ
					td.push("<td class=\"" + CSS_Class_StaNameCol + "\" rowspan=\"2\">" + Stations[i].StationName + "</td>");//駅名
					td.push("<td class=\"" + CSS_Class_DepArrCol + "wdv-Arrcol\">" + "着" + "</td>");//発着表示
					
					for(var k in SelectDia){
						if(SelectDia[k].Time[i] === undefined || SelectDia[k].Time[i].Stop === "0" || (SelectDia[k].Time[i].Stop === "1" && SelectDia[k].Time[i].Arrive === null && SelectDia[k].Time[Number(i) - 1].Stop === "0")){
							var TimeCol = setting.noservicemark;
						}else if(SelectDia[k].Time[i].Stop === "2"){
							var TimeCol = setting.passmark;
						}else if(SelectDia[k].Time[i].Stop === "3" || (SelectDia[k].Time[i].Stop === "1" && SelectDia[k].Time[i].Arrive === null && SelectDia[k].Time[Number(i) - 1].Stop === "3")){
							var TimeCol = setting.noviamark;
						}else{
							var TimeCol = eval("SelectDia[k].Time[i]." + DepArr);
						}
						//3桁時は時刻の前に半角空白
						if(TimeCol.length === 3){
							TimeCol = "　" + TimeCol;
						}
						td.push("<td class=\"" + CSS_Class_TimeCol + "wdv-Arrcol\" style=\"color:" + data.ClassConfig[SelectDia[k].Class].TextColor + ";\">" + TimeCol + "</td>\n");
					}
					$('table.wdv-table tr:eq('+ Row +')')[0].innerHTML = td.join("");
					Row = Row + 1;
					
					//発時刻
					var td = [];
					var DepArr = "Departure";
					$('table.wdv-table').append("<tr></tr>");
					td.push("<td class=\"" + CSS_Class_DepArrCol + "wdv-Depcol\">" + "発" + "</td>");//発着表示
					for(var k in SelectDia){
						if(SelectDia[k].Time[i] === undefined || SelectDia[k].Time[i].Stop === "0" || (SelectDia[k].Time[i].Stop === "1" && SelectDia[k].Time[i].Departure === "" && SelectDia[k].Time[Number(i) + 1] == null)){
							var TimeCol = setting.noservicemark;
						}else if(SelectDia[k].Time[i].Stop === "2"){
							var TimeCol = setting.passmark;
						}else if(SelectDia[k].Time[i].Stop === "3" || (SelectDia[k].Time[i].Stop === "1" && SelectDia[k].Time[i].Departure === "" && SelectDia[k].Time[Number(i) + 1].Stop === "3")){
							var TimeCol = setting.noviamark;
						}else{
							var TimeCol = eval("SelectDia[k].Time[i]." + DepArr);
						}
						//3桁時は時刻の前に半角空白
						if(TimeCol.length === 3){
							TimeCol = "　" + TimeCol;
						}
						td.push("<td class=\"" + CSS_Class_TimeCol + "wdv-Depcol\" style=\"color:" + data.ClassConfig[SelectDia[k].Class].TextColor + ";\">" + TimeCol + "</td>\n");
					}
					$('table.wdv-table tr:eq('+ Row +')')[0].innerHTML = td.join("");
					Row = Row + 1;
				//それ以外の表示の場合
				}else{
					//上り着下り着
					var DepArr_JP = "発";
					var DepArr = "Departure";
					var td = [];
					if((setting.direction === "上り" && Stations[i].Display === "Inbound-Arr") ||
					   (setting.direction === "下り" && Stations[i].Display === "Outbound-Arr")){
						DepArr_JP = "着";
						DepArr = "Arrive";
					}
					
					$('table.wdv-table').append("<tr></tr>");
					//td.push("<td class=\"" + CSS_Class_kmCol + "\">" + undefined_to_space(Stations[i].km) + "</td>");//営業キロ
					td.push("<td class=\"" + CSS_Class_StaNameCol + "\">" + Stations[i].StationName + "</td>");//駅名
					td.push("<td class=\"" + CSS_Class_DepArrCol + "\">" + DepArr_JP + "</td>");//発着表示
					
					for(var k in SelectDia){
						if(SelectDia[k].Time[Number(i) - 1] && SelectDia[k].Time[Number(i) - 1].Stop === "1" && Stations[Number(i) - 1].Display === "DepOnly" && !SelectDia[k].Time[i]){
							var TimeCol = setting.finalstopmark;//終点マーク
						}else if((!SelectDia[k].Time[i] || SelectDia[k].Time[i].Stop === "0") && Stations[i].Display === "DepOnly" && Stations[i].Scale === "Main"){
							var TimeCol = setting.mainstamark;//主要駅照準線マーク
						}else if(SelectDia[k].Time[i] === undefined || SelectDia[k].Time[i].Stop === "0"){
							var TimeCol = setting.noservicemark;//運行なしマーク
						}else if(SelectDia[k].Time[i].Stop === "2"){
							var TimeCol = setting.passmark;//通過マーク
						}else if(SelectDia[k].Time[i].Stop === "3"){
							var TimeCol = setting.noviamark;//経由なしマーク
						}else if(!SelectDia[k].Time[Number(i) + 1] && !SelectDia[k].Time[i].Departure){
							var TimeCol = SelectDia[k].Time[i].Arrive;//次駅の定義が無く、描画駅の発時刻が無ければ、発駅表示でも着時刻を表示する
						}else{
							var TimeCol = eval("SelectDia[k].Time[i]." + DepArr);
						}
						//3桁時は時刻の前に半角空白
						if(TimeCol.length === 3){
							TimeCol = "　" + TimeCol;
						}
						td.push("<td class=\"" + CSS_Class_TimeCol + "\" style=\"color:" + data.ClassConfig[SelectDia[k].Class].TextColor + ";\">" + TimeCol + "</td>\n");
					}
					$('table.wdv-table tr:eq('+ Row +')')[0].innerHTML = td.join("");
					Row = Row + 1;
				}	
			}
		}
		
		function traincolumn_validate_strings(str){
			if(!str){
				return "";
			}else{
				return str.replace(/\s+/g, "");
			}
		}
		
		function yomiganamode(str){
			//DiaProフォントを利用される方向け
			switch(str){
				case "特急":
					return "とっきゅう";
					break;
				case "急行":
					return "きゅうこう";
					break;
				case "快速":
					return "かいそく";
					break;
				case "区快":
					return "くかい";
					break;
				case "新快":
					return "しんかい";
					break;
				case "準快":
					return "じゅんかい";
					break;
				case "通快":
					return "つうかい";
					break;
				case "特快":
					return "とっかい";
					break;
				case "通特":
					return "つうとく";
					break;
				case "直快":
					return "ちょっかい";
					break;
				case "快特":
					return "かいとく";
					break;
				default:
					return str;
					break;
			}
		}
			
		//関数読み込み
		write_table(setting);
		
		//メソッドチェーン対応
		return(this);
	};
})(jQuery);