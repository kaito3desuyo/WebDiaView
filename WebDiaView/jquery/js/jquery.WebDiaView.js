(function($){
	//WebDiaViewプラグイン
	//定義
	$.fn.WebDiaView = function(options){
		
		//デフォルト引数の設定
		var defaults = {
			text: 'This is WebDia View Plugin',
			path: 'js/json/Hanshin-Sanyo.json',
			mode: 'AllRoute',
			type: '平日',
			direction: '上り',
			passmark: '↓',
			noviamark: '|',
			noservicemark: '‥'
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
			
			//列車情報カラム
			//一段目
			var td = [];
			$('table.wdv-table').append("<tr></tr>");
			td.push("<td rowspan=\"2\">営業キロ</td>");//営業キロ
			td.push("<td colspan=\"2\">列車番号</td>");//列車番号
			for(var i in SelectDia){
				td.push("<td>" + undefined_to_space(SelectDia[i].TrainNumber) + "</td>");//列車番号
			}
			$('table.wdv-table tr:eq(0)')[0].innerHTML = td.join("");
			
			//二段目
			var td = [];
			$('table.wdv-table').append("<tr></tr>");
			td.push("<td colspan=\"2\">列車種別</td>");//列車種別
			for(var i in SelectDia){
				td.push("<td style=\"color:" + data.ClassConfig[SelectDia[i].Class].TextColor + ";\">" + data.ClassConfig[SelectDia[i].Class].ShortName + "</td>");//列車種別
			}
			$('table.wdv-table tr:eq(1)')[0].innerHTML = td.join("");
			
			//メインカラム
			var Row = 2;//列車情報カラムの行数に応じて調整
			for(var i in data.RouteConfig.Station){
				//発着時刻表示の場合
				if(data.RouteConfig.Station[i].Display === "DepArr"){
					var td = [];
					var Time = "Arrive";
					$('table.wdv-table').append("<tr></tr>");
					td.push("<td rowspan=\"2\">" + undefined_to_space(Stations[i].km) + "</td>");//営業キロ
					td.push("<td rowspan=\"2\">" + Stations[i].StationName + "</td>");//駅名
					td.push("<td>" + "着" + "</td>");//発着表示
					
					for(var k in SelectDia){
						if(SelectDia[k].Time[i] === undefined || SelectDia[k].Time[i].Stop === "0" || (SelectDia[k].Time[i].Stop === "1" && SelectDia[k].Time[i].Arrive === null && SelectDia[k].Time[i - 1].Stop === "0")){
							var TimeCol = setting.noservicemark;
						}else if(SelectDia[k].Time[i].Stop === "2"){
							var TimeCol = setting.passmark;
						}else if(SelectDia[k].Time[i].Stop === "3" || (SelectDia[k].Time[i].Stop === "1" && SelectDia[k].Time[i].Arrive === null && SelectDia[k].Time[i - 1].Stop === "3")){
							var TimeCol = setting.noviamark;
						}else{
							var TimeCol = eval("SelectDia[k].Time[i]." + Time);
						}
						td.push("<td style=\"color:" + data.ClassConfig[SelectDia[k].Class].TextColor + "\";>" + TimeCol + "</td>");
					}
					$('table.wdv-table tr:eq('+ Row +')')[0].innerHTML = td.join("");
					Row = Row + 1;
					
					//発時刻
					var td = [];
					var Time = "Departure";
					$('table.wdv-table').append("<tr></tr>");
					td.push("<td>" + "発" + "</td>");//発着表示
					for(var k in SelectDia){
						if(SelectDia[k].Time[i] === undefined || SelectDia[k].Time[i].Stop === "0" || SelectDia[k].Time[i].Departure === ""){
							var TimeCol = setting.noservicemark;
						}else if(SelectDia[k].Time[i].Stop === "2"){
							var TimeCol = setting.passmark;
						}else if(SelectDia[k].Time[i].Stop === "3"){
							var TimeCol = setting.noviamark;
						}else{
							var TimeCol = eval("SelectDia[k].Time[i]." + Time);
						}
						td.push("<td style=\"color:" + data.ClassConfig[SelectDia[k].Class].TextColor + "\";>" + TimeCol + "</td>");
					}
					$('table.wdv-table tr:eq('+ Row +')')[0].innerHTML = td.join("");
					Row = Row + 1;
				//それ以外の表示の場合
				}else{
					//上り着下り着
					var DepArr = "発";
					var Time = "Departure";
					var td = [];
					if((setting.direction === "上り" && Stations[i].Display === "Inbound-Arr") ||
					   (setting.direction === "下り" && Stations[i].Display === "Outbound-Arr")){
						DepArr = "着";
						Time = "Arrive";
					}
					
					$('table.wdv-table').append("<tr></tr>");
					td.push("<td>" + undefined_to_space(Stations[i].km) + "</td>");//営業キロ
					td.push("<td>" + Stations[i].StationName + "</td>");//駅名
					td.push("<td>" + DepArr + "</td>");//発着表示
					
					for(var k in SelectDia){
						if(SelectDia[k].Time[i] === undefined || SelectDia[k].Time[i].Stop === "0"){
							var TimeCol = setting.noservicemark;
						}else if(SelectDia[k].Time[i].Stop === "2"){
							var TimeCol = setting.passmark;
						}else if(SelectDia[k].Time[i].Stop === "3"){
							var TimeCol = setting.noviamark;
						}else{
							var TimeCol = eval("SelectDia[k].Time[i]." + Time);
						}
						td.push("<td style=\"color:" + data.ClassConfig[SelectDia[k].Class].TextColor + "\";>" + TimeCol + "</td>");
					}
					$('table.wdv-table tr:eq('+ Row +')')[0].innerHTML = td.join("");
					Row = Row + 1;
				}	
			}
		}
		
		function undefined_to_space($str){
			if($str === undefined){
				return "";
			}else{
				return $str;
			}
		}
		
		//関数読み込み
		write_table(setting);
		
		//メソッドチェーン対応
		return(this);
	};
})(jQuery);