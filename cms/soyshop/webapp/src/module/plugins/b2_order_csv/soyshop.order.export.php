<?php
include_once(dirname(__FILE__) . "/common/class.php");
SOY2::import("domain.order.SOYShop_ItemModule");
/*
 */
class SOYShopB2OrderCSV extends SOYShopOrderExportBase{

	private $csvLogic;

	/**
	 * 検索結果一覧に表示するメニューの表示文言
	 */
	function getMenuTitle(){
		return "B2CSV出力";
	}

	/**
	 * 検索結果一覧に表示するメニューの説明
	 */
	function getMenuDescription(){
		SOY2::import("module.plugins.b2_order_csv.form.B2ExportFormPage");
		$form = SOY2HTMLFactory::createInstance("B2ExportFormPage");
		$form->execute();
		return $form->getObject();
	}

	/**
	 * export エクスポート実行
	 */
	function export($orders){

		if(!$this->csvLogic) $this->csvLogic = new B2OutputCSV();

		set_time_limit(0);
		$lines = array();

		foreach($orders as $order){
			$orderId = $order->getId();
			//伝票番号登録分だけCSVを出力する
			$slipNumbers = explode(",", self::_slipLogic()->getAttribute($orderId)->getValue1());
			if(!count($slipNumbers)) $slipNumbers[] = null;
			foreach($slipNumbers as $slipNumber){
				$line = $this->csvLogic->getCSVLine($orderId, $slipNumber);
				if(!is_null($line)) $lines[] = $line;
			}
		}

		$charset = (isset($_REQUEST["charset"])) ? $_REQUEST["charset"] : "Shift-JIS";

		header("Cache-Control: public");
		header("Pragma: public");
    	header("Content-Disposition: attachment; filename=b2_" . $orderId.".csv");
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

	private function _slipLogic(){
		static $logic;
		if(is_null($logic)) $logic = SOY2Logic::createInstance("module.plugins.slip_number.logic.SlipNumberLogic");
		return $logic;
	}
}

SOYShopPlugin::extension("soyshop.order.export","b2_order_csv","SOYShopB2OrderCSV");
