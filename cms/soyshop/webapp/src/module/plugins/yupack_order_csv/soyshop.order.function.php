<?php
SOY2::import("domain.order.SOYShop_ItemModule");
include_once(dirname(__FILE__) . "/common/common.php");

class SOYShopYupackOrderFunction extends SOYShopOrderFunction{
	
	/**
	 * title text
	 */
	function getTitle(){
		return "ゆうパックCSV";
		
	}
	
	private $csvLogic;
	
	/**
	 * @return html
	 */
	function getPage(){
		
		if(!$this->csvLogic)$this->csvLogic = new YupackOutputCSV();
		
		set_time_limit(0);
		
		$orderId = $this->getOrderId();
		$line = $this->csvLogic->getCSVLine($orderId);
		
		$orderDao = SOY2DAOFactory::create("order.SOYShop_OrderDAO");
		try{
			$order = $orderDao->getById($orderId);
		}catch(Exception $e){
			$order = new SOYShop_Order();
		}
		
		$userDao = SOY2DAOFactory::create("user.SOYShop_UserDAO");
		try{
			$user = $userDao->getById($order->getId());
		}catch(Exception $e){
			$user = new SOYShop_User();
		}
	
		$charset = (isset($_REQUEST["charset"])) ? $_REQUEST["charset"] : "Shift-JIS";
		$line = mb_convert_encoding($line, $charset, "UTF-8");
		
		header("Cache-Control: public");
		header("Pragma: public");
    	header("Content-Disposition: attachment; filename=" . $user->getName() . "_" . $orderId.".csv");
		header("Content-Type: text/csv; charset=" . htmlspecialchars($charset) . ";");
		
		echo mb_convert_encoding(implode("," , $this->csvLogic->getLabels()),$charset,"UTF-8");
		echo "\r\n";
		echo $line;
		exit;	//csv output
		
	}
	
    /**
     * ファイル出力: 改行コードはCRLF
     */
    function outputFile($label, $lines, $charset){
    	

    	echo implode(",", $label);
    	echo "\n";
    	echo implode(",", $lines);
    }
    
    	function setCharset($charset) {

		switch($charset){
			case "Shift-JIS":
			case "Shift_JIS":
				$charset = "Shift_JIS";
				break;
			default:
				$charset = "UTF-8";
				break;
		}

		$this->charset = $charset;
	}
}

SOYShopPlugin::extension("soyshop.order.function","yupack_order_csv","SOYShopYupackOrderFunction");