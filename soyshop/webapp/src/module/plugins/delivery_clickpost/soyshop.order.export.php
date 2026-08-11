<?php

class DeliveryClickPostCSV extends SOYShopOrderExportBase{

	/**
	 * 検索結果一覧に表示するメニューの表示文言
	 */
	function getMenuTitle(){
		return "郵送(クリックポスト)CSV出力";
	}

	/**
	 * 検索結果一覧に表示するメニューの説明
	 */
	function getMenuDescription(){
		return '郵送(クリックポスト)のCSVを出力します。&nbsp;&nbsp;(<strong>文字コード=</strong>
			<input id="charset_shit_jis" type="radio" name="charset" value="Shift-JIS" />
			<label for="charset_shit_jis">Shift-JIS</label>
			<input id="charset_utf_8" type="radio" name="charset" value="UTF-8" />
			<label for="charset_utf_8">UTF-8</label>
		)';
	}

	/**
	 * export エクスポート実行
	 */
	function export(array $orders){
		set_time_limit(0);
		SOY2::import("domain.config.SOYShop_Area");
		$lines = array();
	
		foreach($orders as $order){
			$addr = $order->getAddressArray();
			$line = array();

			$fullAddr = "";
			if(isset($addr["area"]) && is_numeric($addr["area"])) {
				$fullAddr .= SOYShop_Area::getAreaText($addr["area"]);
			}

			if(isset($addr["address1"])) {
				$fullAddr .= $addr["address1"];
			}
			
			$line[] = (isset($addr["zipCode"])) ? $addr["zipCode"] : "";	//お届け先郵便番号
			$line[] = (isset($addr["name"])) ? $addr["name"] : "";	//お届け先氏名
			$line[] = soyshop_get_user_object((int)$order->getUserId())->getHonorific();	//お届け先敬称
			$line[] = $fullAddr;	//お届け先住所1行目
			$line[] = (isset($addr["address2"])) ? $addr["address2"] : "";	//お届け先住所2行目
			$line[] = (isset($addr["address3"])) ? $addr["address3"] : "";	//お届け先住所3行目
			$line[] = (isset($addr["address4"])) ? $addr["address4"] : "";	//お届け先住所4行目
			$line[] = "";	//内容品

			$lines[] = implode(",", $line);
		}

		$charset = (isset($_REQUEST["charset"])) ? $_REQUEST["charset"] : "Shift-JIS";

		header("Cache-Control: public");
		header("Pragma: public");
    	header("Content-Disposition: attachment; filename=clickpost_".date("YmdHis").".csv");
		header("Content-Type: text/csv; charset=" . htmlspecialchars($charset) . ";");

		ob_start();
		echo self::_labels();
		echo "\r\n";
		echo implode("\r\n",$lines);
		$csv = ob_get_contents();
		ob_end_clean();

		echo mb_convert_encoding($csv,$charset,"UTF-8");
		exit;	//csv output
	}

	/**
	 * @param array
	 * @param int
	 * @return string
	 */
	private function _labels(){
		$labels = array();
		$labels[] = "お届け先郵便番号";
		$labels[] = "お届け先氏名";
		$labels[] = "お届け先敬称";
		$labels[] = "お届け先住所1行目";
		$labels[] = "お届け先住所2行目";
		$labels[] = "お届け先住所3行目";
		$labels[] = "お届け先住所4行目";
		$labels[] = "内容品";
		
		return implode(",", $labels);
	}
}

SOYShopPlugin::extension("soyshop.order.export", "delivery_clickpost", "DeliveryClickPostCSV");
