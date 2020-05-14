<?php
include_once(dirname(__FILE__) . "/common/common.php");
SOY2::import("domain.order.SOYShop_ItemModule");
/*
 */
class YupackOrderCSV extends SOYShopOrderExportBase{
	
	private $csvLogic;

	/**
	 * 検索結果一覧に表示するメニューの表示文言
	 */
	function getMenuTitle(){
		return "ゆうパックプリントCSV出力";
	}

	/**
	 * 検索結果一覧に表示するメニューの説明
	 */
	function getMenuDescription(){
		return 'ゆうパックプリント形式のCSVを出力します。&nbsp;&nbsp;(<strong>文字コード=</strong>
			<input id="charset_shit_jis" type="radio" name="charset" value="Shift-JIS" checked />
			<label for="charset_shit_jis">Shift-JIS</label>		
			<input id="charset_utf_8" type="radio" name="charset" value="UTF-8" />
			<label for="charset_utf_8">UTF-8</label>
		)';
	}

	/**
	 * export エクスポート実行
	 */
	function export($orders){
		
		if(!$this->csvLogic)$this->csvLogic = new YupackOutputCSV();
		
		set_time_limit(0);
		$lines = array();
		
		foreach($orders as $order){
			$orderId = $order->getId();
			$lines[] = $this->csvLogic->getCSVLine($orderId);
		}
	
		$charset = (isset($_REQUEST["charset"])) ? $_REQUEST["charset"] : "Shift-JIS";
		
		header("Cache-Control: public");
		header("Pragma: public");
    	header("Content-Disposition: attachment; filename=yupack_" . $orderId.".csv");
		header("Content-Type: text/csv; charset=" . htmlspecialchars($charset) . ";");
		
		ob_start();
		echo implode("," , $this->csvLogic->getLabels());
		echo "\r\n";
		echo implode("\r\n",$lines);
		$csv = ob_get_contents();
		ob_end_clean();
		
		echo mb_convert_encoding($csv,$charset,"UTF-8");
		
		exit;	//csv output
		
	}

}

SOYShopPlugin::extension("soyshop.order.export","yupack_order_csv","YupackOrderCSV");
?>
