<?php

class CommonAnalyticsInfo extends SOYShopInfoPageBase{

	function getPage($active = false){
		if($active){
			$html = array();
			$html[] = '注文一覧の検索結果をエクスポートするのところに統計ボタンが追加されます。<br>';
			$html[] = '<a href="http://www.chartjs.org/" target="_blank">Chart.js | Open source HTML5 Charts for your website</a><br>';
			$html[] = "※サーバのバージョンによってはグラフが表示されないことがあります。";
			return implode("\r\n", $html);
		}else{
			return "";
		}
	}

}
SOYShopPlugin::extension("soyshop.info", "common_analytics", "CommonAnalyticsInfo");
?>