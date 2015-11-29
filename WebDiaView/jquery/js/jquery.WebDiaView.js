;(function($){
	//WebDiaViewプラグイン
	//定義
	$.fn.WebDiaView = function(options){
		
		//デフォルト引数の設定
		var defaults = {
			text: 'This is WebDia View Plugin',
			path: 'js/json/Hanwa.json',			//パス
			mode: 'Station',							//表示モード
			modeselect: true,							//表示モードの切り替えtrue/false
			type: 0,									//時刻表データ選択初期値
			direction: 'Outbound',						//上下選択初期値
			dirChange: null,							//上下選択が操作された時、古い情報を入れるバッファ
			stanum: 0,									//駅番号（全駅検索のみ使用）
			maxcol: 20,									//最大表示列数（全線検索のみ使用）
			startcol: 33,								//表示開始列数（全線検索のみ使用）
			passmark: '&#x2193;',						//通過マーク
			noviamark: '&#124;',						//経由なしマーク
			noservicemark: '&#x2025;',					//運行なしマーク
			finalstopmark: '&#61;',						//終点マーク
			mainstamark: '&#x2500;',					//主要駅マーク
			traincolumn: [								//ヘッダーカラムの数と名前と変数名
							["列車番号", "SelectDia[j].TrainNumber", "wdv-Traincol-TrainNumber", false],
							["列車種別", "data.ClassConfig[SelectDia[j].Class].ShortName", "wdv-Traincol-Class", false],
							["列車名", "SelectDia[j].TrainName + SelectDia[j].TrainNo", "wdv-Traincol-TrainName", true],
						],
			stationcolumn: [							//上に同じく
							["", ""],
							["営業キロ", ""]
						],
			yomiganamode: false							//DiaProモード
		};
		var setting = $.extend(defaults, options);
		
		//メイン処理関数
		var wdv_writing_table = function(setting){
			//JSONデータの読み込み→テーブル描画
			$.getJSON(
				setting.path,
				function(data){
					Search_Form(data);
				}
				//eval("TableFormat_" + setting.mode)
			);
		}
		
		//関数読み込み
		wdv_writing_table(setting);	
		
		//フォーム生成関数
		function Search_Form(data){
			var url = window.location.href;
			var filename = url.match(".+/(.+?)([\?#;].*)?$")[1];
			var txt = [];
			var dupcheck = null;
			var dupcheck2 = [];
			txt.push("<form class=\"wdv-form\" method=\"get\"></form>");
			
			//モード選択フォーム（trueのみ）
			if(setting.modeselect === true){
				txt.push("<label><input type=\"radio\" name=\"mode\" value=\"AllRoute\">全線時刻表</label>");
				txt.push("<label><input type=\"radio\" name=\"mode\" value=\"Station\">全駅時刻表</label>");
				txt.push("<label><input type=\"radio\" name=\"mode\" value=\"Diagram\">ダイヤグラム</label>");
				txt.push("<br>");
			}

			//駅名選択フォーム
			if(setting.modeselect === true || setting.mode === "Station"){
				txt.push("<select class=\"wdv-form-selectbox-station\">");
				$.each(data.RouteConfig.Station, function(key, val){
				
					//駅名に重複があったら駅番号を配列にして返す
					$.each(data.RouteConfig.Station, function(key2, val2){
					
						if(key2 !== key && val.StationName === val2.StationName && $.inArray(key, dupcheck2) < 0){
							txt.push("<option value=\""+ [key, key2] + "\">" + val.StationName + "</option>");
							dupcheck = true;
							dupcheck2.push(key, key2);
							return false;
						}else if(key2 !== key && val.StationName === val2.StationName && $.inArray(key, dupcheck2) > 0){
							dupcheck = true;
							return false;
						}else{
							dupcheck = false;
						}
						
					});
				
					if(dupcheck === false && $.inArray(key, dupcheck2) < 0){
						txt.push("<option value=\""+ [key] + "\">" + val.StationName + "</option>");
					}
				
				});
				txt.push("</select>");
			}
			
			//ダイヤ選択フォーム
			txt.push("<select class=\"wdv-form-selectbox-day\">");
			$.each(data.DiagramConfig, function(key, val){				
				txt.push("<option value=\""+ key + "\">" + val.DiaName + "</option>");
			});
			txt.push("</select>");
			
			//方向選択フォーム
			txt.push("<select class=\"wdv-form-selectbox-direction\">>");
			$.each({"Inbound":"上り", "Outbound":"下り"}, function(key, val){
				txt.push("<option value=\""+ key + "\">" + val + "</option>");
			});
			txt.push("</select>");
			
			txt.push("<input class=\"wdv-form-button\" type=\"submit\" value=\"検索実行\">");
			txt.push("</form>");
			
			$('div.wdv-searchform')[0].innerHTML = txt.join("");

			setting.type = $(".wdv-form-selectbox-day option:selected").val();
			setting.dirChange = $(".wdv-form-selectbox-direction option:selected").val();
						
			//モード選択
			if(setting.modeselect === true){
				//モードによりチェック済みの要素を変える
				switch(setting.mode){
					case "AllRoute":
						$('input[name="mode"]:eq(0)').prop('checked', true);
						break;
					case "Station":
						$('input[name="mode"]:eq(1)').prop('checked', true);
						break;
					case "Diagram":
						$('input[name="mode"]:eq(2)').prop('checked', true);
						break;
				}
				//全駅モード以外は駅選択チェックボックスを無効
				if(setting.mode !== "Station"){
					$('.wdv-form-selectbox-station').attr("disabled", "disabled");
				}
				//チェックを変えたら
				$('input[name="mode"]:radio').change( function() {				
					if($(this).val() === "Station"){
						$('.wdv-form-selectbox-station').removeAttr("disabled");
					}else{
						$('.wdv-form-selectbox-station').attr("disabled", "disabled");
					} 
					setting.mode = $('input[name="mode"]:checked').val();
				});  
			}
				
			//検索条件変更
			$('.wdv-form-selectbox-station').change(function(){
				setting.stanum = $(".wdv-form-selectbox-station option:selected").val();
			});
			
			$('.wdv-form-selectbox-day').change(function(){
				setting.type = $(".wdv-form-selectbox-day option:selected").val();
				setting.dirChange = $(".wdv-form-selectbox-direction option:selected").val();
			});
			
			$('.wdv-form-selectbox-direction').change(function(){
				setting.type = $(".wdv-form-selectbox-day option:selected").val();
				setting.dirChange = $(".wdv-form-selectbox-direction option:selected").val();
			});	
			
			$('.wdv-form-button').click(function(e){
				$('table.wdv-table').empty();
				setTimeout(function(){
					eval("TableFormat_" + setting.mode)(data, setting);
				},500);
			});
		}
		

		function TableFormat_Station(data, setting){
			//時刻表表示　全駅時刻表モード
			//各種変数指定
			var Stations = data.RouteConfig.Station;//駅情報
			var SelectDia = eval("data.DiagramConfig[" + setting.type + "]." + setting.dirChange);
			var StationDia = {	"4": [],
								"5": [],
								"6": [],
								"7": [],
								"8": [],
								"9": [],
								"10": [],
								"11": [],
								"12": [],
								"13": [],
								"14": [],
								"15": [],
								"16": [],
								"17": [],
								"18": [],
								"19": [],
								"20": [],
								"21": [],
								"22": [],
								"23": [],
								"0": [],
								"1": [],
								"2": [],
								"3": []
							};
			
			if(setting.direction !== setting.dirChange){//上りダイヤ選択時は駅情報を逆順にする
				Stations = Stations.reverse();
				setting.direction = setting.dirChange;
			}
			
			
			//駅番号にカンマがない（複数の番号がない）なら末尾にカンマを付ける
			if(String(setting.stanum).match(/,/) === null){
				setting.stanum = setting.stanum + ",";
			}
			//カンマで駅番号を分けて、出来た配列の分だけ整理を繰り返す
			$.each(setting.stanum.split(',', -1), function(key, val){
				
				//上りダイヤ選択時は駅情報を逆順にする
				if(setting.direction === "Inbound"){
					var StaNum = (Number(Stations.length) - 1) - Number(val);
				}else{
					var StaNum = val;
				}

				//時刻ごとに配列に整理
				$.each(SelectDia, function(key, val){
				
					//駅+1がnull→駅は終点
					var FinalStop = null;
					$.each(val.Time, function(key){
						if(
							(val.Time[StaNum] && val.Time[StaNum].Departure !== "" && val.Time[StaNum].Stop === "1" && !val.Time[Number(key) + 1])
						)
						{
							FinalStop = Stations[key].StationName;
						}else{
							FinalStop = "？";
						}
					});
				

				
					if(val.Time[StaNum] && val.Time[StaNum].Departure !== "" && val.Time[StaNum].Stop === "1"){
					
						switch(val.Time[StaNum].Departure.length){
							case 3:
								var newAryKey = val.Time[StaNum].Departure.slice(0, 1);
								var DepMin = val.Time[StaNum].Departure.slice(1, 3);
								var DepSec = "00";
								break;
							case 4:
								var newAryKey = val.Time[StaNum].Departure.slice(0, 2);
								var DepMin = val.Time[StaNum].Departure.slice(2, 4);
								var DepSec = "00";
								break;
							case 5:
								var newAryKey = val.Time[StaNum].Departure.slice(0, 1);
								var DepMin = val.Time[StaNum].Departure.slice(1, 3);
								var DepSec = val.Time[StaNum].Departure.slice(3, 5);
								break;
							case 6:
								var newAryKey = val.Time[StaNum].Departure.slice(0, 2);
								var DepMin = val.Time[StaNum].Departure.slice(2, 4);
								var DepSec = val.Time[StaNum].Departure.slice(4, 6);
								break;
							default:
								var newAryKey = null;
						}
					
						StationDia[Number(newAryKey)].push(
															{
																"TrainNumber": val.TrainNumber,
																"Class": data.ClassConfig[Number(val.Class)].ShortName,
																"TrainName": traincolumn_validate_strings(val.TrainName),
																"TrainNo": traincolumn_validate_strings(val.TrainNo),
																"Arrive": val.Time[StaNum].Arrive,
																"Departure": val.Time[StaNum].Departure,
																"DepMin": DepMin,
																"DepSec": DepSec,
																"FinalStop": FinalStop,
																"TextColor": data.ClassConfig[Number(val.Class)].TextColor
															}
														);
														
					}
				});
			});
			
			//Table描画
			var TimeDupChk = "";//時刻重複検出
			//var TraNoDupChk = "";//列車番号重複検出
			$.each(StationDia, function(key, val){
				$('table.wdv-table').append("<tr></tr>");
				var td = [];
				td.push("<td>" + key + "時</td>");
				
				//分でソート
				val.sort(function(a,b){
					return(a.DepMin - b.DepMin || a.DepSec - b.DepSec);
				});
				
				$.each(val, function(key, val){
					//列車の重複を検査
					if(val.Departure !== TimeDupChk){
						td.push("<td style=\"color:" + val.TextColor + ";\">" + val.DepMin + "<br>" + val.Class + val.FinalStop + "</td>");
					}
					TimeDupChk = val.Departure;
					//TraNoDupChk = val.TrainNumber;
				});
				
				$('table.wdv-table tr:eq(' + key +')')[0].innerHTML = td.join("");
			});
		}
		
		function TableFormat_AllRoute(data, setting){
			//時刻表表示　全線時刻表モード
			//データ概要を取得して表示する
			//$('p.wdv-datainfo').html('データ名：' 　	+ data.DataName 	+ '<br>' +
			//						　'バージョン：'		+ data.Version 		+ '<br>' +
			//						　'最終更新日：' 	+ data.LastUpdate 	+ '<br>' +
			//						　'作成者：' 		+ data.Contributor
			//);
						
			//各種変数指定
			var Stations = data.RouteConfig.Station;//駅情報
			
			if(setting.direction !== setting.dirChange){//上りダイヤ選択時は駅情報を逆順にする
				Stations = Stations.reverse();
				setting.direction = setting.dirChange;
			}
			
			var SelectDia = eval("data.DiagramConfig[" + setting.type + "]." + setting.direction);//ダイヤ情報
			
			//ページネーション機構
			if(setting.startcol === 0){//0件目
				$('p.wdv-controllmenu').html('<a href="javascript:void(0)" class="wdv-controllmenu-next">後の' + setting.maxcol + '件へ≫</a>');
			}else if(setting.startcol < setting.maxcol){//1以上～最大表示数未満まで
				$('p.wdv-controllmenu').html('<a href="javascript:void(0)" class="wdv-controllmenu-prev">≪前の' + setting.startcol + '件へ</a> <a href="javascript:void(0)" class="wdv-controllmenu-next">後の' + setting.maxcol + '件へ≫</a>');
			}else if(SelectDia.length - setting.startcol < setting.maxcol){//データ件数-表示開始件数=最大表示数以下
				$('p.wdv-controllmenu').html('<a href="javascript:void(0)" class="wdv-controllmenu-prev">≪前の' + setting.maxcol + '件へ</a>');
			}else if(SelectDia.length - (setting.startcol + setting.maxcol) < setting.maxcol){//データ件数-(表示開始件数+最大表示数)=最大表示数以下
				$('p.wdv-controllmenu').html('<a href="javascript:void(0)" class="wdv-controllmenu-prev">≪前の' + setting.maxcol + '件へ</a> <a href="javascript:void(0)" class="wdv-controllmenu-next">後の' + eval(SelectDia.length - (setting.startcol + setting.maxcol)) + '件へ≫</a>');
			}else if(setting.startcol >= setting.maxcol){
				$('p.wdv-controllmenu').html('<a href="javascript:void(0)" class="wdv-controllmenu-prev">≪前の' + setting.maxcol + '件へ</a> <a href="javascript:void(0)" class="wdv-controllmenu-next">後の' + setting.maxcol + '件へ≫</a>');
			}
			
			$('a.wdv-controllmenu-prev').click(function(){
				if(setting.startcol < setting.maxcol){
					setting.startcol = 0;
				}else{
					setting.startcol = setting.startcol - setting.maxcol;
				}
				$('table.wdv-table').empty();
				TableFormat_AllRoute(data, setting);
			});
			$('a.wdv-controllmenu-next').click(function(){
				setting.startcol = setting.startcol + setting.maxcol;
				$('table.wdv-table').empty();
				TableFormat_AllRoute(data, setting);
			});
			
			//列車情報カラム
			var RowNum = null;
			$.each(setting.traincolumn, function(i, val){
				var td = [];
				$('table.wdv-table').append("<tr></tr>");
				td.push("<td colspan=\"2\" class=\"wdv-headercol\">" + setting.traincolumn[i][0] + "</td>");
				
				var colnum = 0;
				for(var j = Number(setting.startcol); j < SelectDia.length; j++){
					var Address = eval(setting.traincolumn[i][1]);//変数参照先の取得
					var test = "";
					Address = traincolumn_validate_strings(Address);//無駄な空白と未定義の削除

					//縦書きtrueの場合は一文字ずつ分けて改行タグを仕込む
					if(setting.traincolumn[i][3] === true){
						var tmp = Address;
						Address = "";
						$.each(tmp.split(""), function(key, val){
							if(!isFinite(val)){
								Address += val.replace(/ー/g, "｜") + "<br>";
							}else{
								Address += val;
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
					
					td.push("<td class=\"wdv-Traincol " + setting.traincolumn[i][2] + "\" style=\"color:" + data.ClassConfig[SelectDia[j].Class].TextColor + ";\">" + Address + "</td>");
					
					colnum++;
					//最大行数になったらやめる
					if(setting.maxcol <= Number(colnum)){
						break;
					}
				}
				
				$('table.wdv-table tr:eq(' + i +')')[0].innerHTML = td.join("");

				RowNum = Number(i) + 1;
			});
			

			//メインカラム
			var Row = RowNum;//列車情報カラムの行数に応じて自動調整
			$.each(Stations, function(i, val){
				
				var CSS_Class_kmCol = "wdv-kmcol ";//営業キロカラムCSSクラス初期設定
				var CSS_Class_StaNameCol = "wdv-StationNamecol ";//駅名カラムCSSクラス初期設定
				var CSS_Class_DepArrCol = "wdv-DepArrcol ";//発着表示カラムCSSクラス初期設定
				var CSS_Class_TimeCol = "wdv-Timecol ";//時刻カラムCSSクラス初期設定
				if(this.Kyoukaisen){
					//境界線クラスの追加
					if(setting.direction === "Inbound"){
						CSS_Class_kmCol = CSS_Class_kmCol + "wdv-Bordercol-Top";
						CSS_Class_StaNameCol = CSS_Class_StaNameCol + "wdv-Bordercol-Top";
						CSS_Class_DepArrCol = CSS_Class_DepArrCol + "wdv-Bordercol-Top";
						CSS_Class_TimeCol = CSS_Class_TimeCol + "wdv-Bordercol-Top";
					}else if(setting.direction === "Outbound"){
						CSS_Class_kmCol = CSS_Class_kmCol + "wdv-Bordercol-Bottom";
						CSS_Class_StaNameCol = CSS_Class_StaNameCol + "wdv-Bordercol-Bottom";
						CSS_Class_DepArrCol = CSS_Class_DepArrCol + "wdv-Bordercol-Bottom";
						CSS_Class_TimeCol = CSS_Class_TimeCol + "wdv-Bordercol-Bottom";
					}

				}
				
				//発着時刻表示の場合
				if(this.Display === "DepArr"){
					//着時刻
					var td = [];
					var DepArr = "Arrive";
					$('table.wdv-table').append("<tr></tr>");
					//td.push("<td class=\"" + CSS_Class_kmCol + "\" rowspan=\"2\">" + undefined_to_space(Stations[i].km) + "</td>");//営業キロ
					td.push("<td class=\"" + CSS_Class_StaNameCol + "\" rowspan=\"2\">" + Stations[i].StationName + "</td>");//駅名
					td.push("<td class=\"" + CSS_Class_DepArrCol + "wdv-Arrcol\">" + "着" + "</td>");//発着表示
					
					var colnum = 0;
					for(var j = Number(setting.startcol); j < SelectDia.length; j++){
						if(SelectDia[j].Time[i] === undefined || SelectDia[j].Time[i].Stop === "0" || (SelectDia[j].Time[i].Stop === "1" && SelectDia[j].Time[i].Arrive === null && SelectDia[j].Time[Number(i) - 1].Stop === "0")){
							var TimeCol = setting.noservicemark;
						}else if(SelectDia[j].Time[i].Stop === "2"){
							var TimeCol = setting.passmark;
						}else if(SelectDia[j].Time[i].Stop === "3" || (SelectDia[j].Time[i].Stop === "1" && SelectDia[j].Time[i].Arrive === null && SelectDia[j].Time[Number(i) - 1].Stop === "3")){
							var TimeCol = setting.noviamark;
						}else{
							var TimeCol = eval("SelectDia[j].Time[i]." + DepArr);
						}
						
						//3桁時は時刻の前に半角空白
						if(TimeCol.length === 3){
							TimeCol = "　" + TimeCol;
						}
						td.push("<td class=\"" + CSS_Class_TimeCol + "wdv-Arrcol\" style=\"color:" + data.ClassConfig[SelectDia[j].Class].TextColor + ";\">" + TimeCol + "</td>\n");
					
						colnum++;
						//最大行数になったらやめる
						if(setting.maxcol <= Number(colnum)){
							break;
						}
					}

					$('table.wdv-table tr:eq('+ Row +')')[0].innerHTML = td.join("");
					Row = Row + 1;
					
					//発時刻
					var td = [];
					var DepArr = "Departure";
					$('table.wdv-table').append("<tr></tr>");
					td.push("<td class=\"" + CSS_Class_DepArrCol + "wdv-Depcol\">" + "発" + "</td>");//発着表示
					
					var colnum = 0;
					for(var j = Number(setting.startcol); j < SelectDia.length; j++){
						if(SelectDia[j].Time[i] === undefined || SelectDia[j].Time[i].Stop === "0" || (SelectDia[j].Time[i].Stop === "1" && SelectDia[j].Time[i].Departure === "" && SelectDia[j].Time[Number(i) + 1] == null)){
							var TimeCol = setting.noservicemark;
						}else if(SelectDia[j].Time[i].Stop === "2"){
							var TimeCol = setting.passmark;
						}else if(SelectDia[j].Time[i].Stop === "3" || (SelectDia[j].Time[i].Stop === "1" && SelectDia[j].Time[i].Departure === "" && SelectDia[j].Time[Number(i) + 1].Stop === "3")){
							var TimeCol = setting.noviamark;
						}else{
							var TimeCol = eval("SelectDia[j].Time[i]." + DepArr);
						}
						//3桁時は時刻の前に半角空白
						if(TimeCol.length === 3){
							TimeCol = "　" + TimeCol;
						}
						td.push("<td class=\"" + CSS_Class_TimeCol + "wdv-Depcol\" style=\"color:" + data.ClassConfig[SelectDia[j].Class].TextColor + ";\">" + TimeCol + "</td>\n");
					
						colnum++;
						//最大行数になったらやめる
						if(setting.maxcol <= Number(colnum)){
							break;
						}
					};
					
					$('table.wdv-table tr:eq('+ Row +')')[0].innerHTML = td.join("");
					Row = Row + 1;
					
				//それ以外の表示の場合
				}else{
					//上り着下り着
					var DepArr_JP = "発";
					var DepArr = "Departure";
					var td = [];
					if((setting.direction === "Inbound" && this.Display === "Inbound-Arr") ||
					   (setting.direction === "Outbound" && this.Display === "Outbound-Arr")){
						DepArr_JP = "着";
						DepArr = "Arrive";
					}
					
					$('table.wdv-table').append("<tr></tr>");
					//td.push("<td class=\"" + CSS_Class_kmCol + "\">" + undefined_to_space(Stations[i].km) + "</td>");//営業キロ
					td.push("<td class=\"" + CSS_Class_StaNameCol + "\">" + Stations[i].StationName + "</td>");//駅名
					td.push("<td class=\"" + CSS_Class_DepArrCol + "\">" + DepArr_JP + "</td>");//発着表示
					
					var colnum = 0;
					for(var j = Number(setting.startcol); j < SelectDia.length; j++){
						if(SelectDia[j].Time[Number(i) - 1] && SelectDia[j].Time[Number(i) - 1].Stop === "1" && Stations[Number(i) - 1].Display === "DepOnly" && !SelectDia[j].Time[i]){
							var TimeCol = setting.finalstopmark;//終点マーク
						}else if((!SelectDia[j].Time[i] || SelectDia[j].Time[i].Stop === "0") && Stations[i].Display === "DepOnly" && Stations[i].Scale === "Main"){
							var TimeCol = setting.mainstamark;//主要駅照準線マーク
						}else if(SelectDia[j].Time[i] === undefined || SelectDia[j].Time[i].Stop === "0"){
							var TimeCol = setting.noservicemark;//運行なしマーク
						}else if(SelectDia[j].Time[i].Stop === "2"){
							var TimeCol = setting.passmark;//通過マーク
						}else if(SelectDia[j].Time[i].Stop === "3"){
							var TimeCol = setting.noviamark;//経由なしマーク
						}else if(!SelectDia[j].Time[Number(i) + 1] && !SelectDia[j].Time[i].Departure){
							var TimeCol = SelectDia[j].Time[i].Arrive;//次駅の定義が無く、描画駅の発時刻が無ければ、発駅表示でも着時刻を表示する
						}else{
							var TimeCol = eval("SelectDia[j].Time[i]." + DepArr);
						}

						//3桁時は時刻の前に半角空白
						if(TimeCol.length === 3){
							TimeCol = "　" + TimeCol;
						}
						td.push("<td class=\"" + CSS_Class_TimeCol + "\" style=\"color:" + data.ClassConfig[SelectDia[j].Class].TextColor + ";\">" + TimeCol + "</td>\n");
					
						colnum++;
						//最大行数になったらやめる
						if(setting.maxcol <= Number(colnum)){
							break;
						}					
					};
					
					$('table.wdv-table tr:eq('+ Row +')')[0].innerHTML = td.join("");
					Row = Row + 1;
				}
				TimeCol = null;
			});
		}
		
		function traincolumn_validate_strings(str){
			if(str === undefined || str === "undefined" || str === null || str === "" || str !== str){
				return "";
			}else{
				var str = str.replace(/\s+/g, "");
				return str;
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

		
		//メソッドチェーン対応
		return(this);
	};
})(jQuery);