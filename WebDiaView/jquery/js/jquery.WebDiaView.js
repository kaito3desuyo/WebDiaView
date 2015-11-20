(function($){
	//WebDiaViewプラグイン
	//定義
	$.fn.WebDiaView = function(options){
		
		//デフォルト引数の設定
		var defaults = {
			text: 'This is WebDia View Plugin',
			path: 'js/json/testdata.json'
		};
		var setting = $.extend(defaults, options);
		
		//メイン処理関数
		var write_table = function(setting){
			//JSONデータの読み込み
			var data = {};
			$.getJSON(
				setting.path,
				null,
				function(data, status){
					console.dir(data);
				
					//データ概要を取得して表示する
					$('p.wdv-datainfo').html('データ名：' 　	+ data.DataName 	+ '<br>' +
											　'バージョン：'		+ data.Version 		+ '<br>' +
											　'最終更新日：' 	+ data.LastUpdate 	+ '<br>' +
											　'作成者：' 		+ data.Contributor
					);
				
					//列車番号を表に追加する
					$('table.wdv-table').append($("<tr></tr>").append($("<td></td>").text("列車番号")));
					for(var i in data.DiagramConfig.Type[0].Inbound){
						$('table.wdv-table tr').append($("<td></td>").text(data.DiagramConfig.Type[0].Inbound[i].TrainNumber));
					}
				
				
					//駅名を表に追加する
					for(var i in data.RouteConfig.Route.Station){
						$('table.wdv-table').append($("<tr></tr>").append($("<td></td>").text(data.RouteConfig.Route.Station[i].StationName)));				
					}
				
					//時刻を表に追加する
					var Row = null;
					for(var i in data.RouteConfig.Route.Station){
						Row = Number(i) + 1;
						for(var k in data.DiagramConfig.Type[0].Inbound){
							$('table.wdv-table tr:eq('+Row+')').append($("<td></td>").text(data.DiagramConfig.Type[0].Inbound[k].Time[i]));				
						};
					};
				}
			);
		};
	
		//関数読み込み
		write_table(setting);
		
		//メソッドチェーン対応
		return(this);
	};
})(jQuery);