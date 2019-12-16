<?php

class AggregateInfo extends SOYShopInfoPageBase{

	function getPage($active = false){
		if($active){
			$html = array();
			$html[] = '注文一覧の検索結果をエクスポートするのところに集計ボタンが追加されます。';
			return implode("\r\n", $html);
		}else{
			return "";
		}
	}
}
SOYShopPlugin::extension("soyshop.info", "common_aggregate", "AggregateInfo");
?>