<?php
/*
 * Created on 2009/07/28
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */

class LoggingSlipNumberOrderCustomfield extends SOYShopOrderCustomfield{
		
	function clear(CartLogic $cart){}	
	function doPost($param){}
	function order(CartLogic $cart){}
	function hasError($param){}
	function getForm(CartLogic $cart){}
	
	function display($orderId){
		$array = array();
		
		$attr = self::getLogic()->getAttribute($orderId);
		if(!is_null($attr->getOrderId())){
			$array[] = array(
				"name" => "伝票番号",
				"value" => htmlspecialchars($attr->getValue1(), ENT_QUOTES, "UTF-8")
			);
		}
				
		return $array;
	}
	
	/**
	 * @param int $orderID
	 * @return array labelとformの連想配列を格納
	 */
	function edit($orderId){
		
		$array = array();
		
		$attr = self::getLogic()->getAttribute($orderId);
		if(!is_null($attr->getOrderId())){
			$array[] = array(
				"label" => "伝票番号",
				"form" => "<input type=\"text\" class=\"text\" name=\"Customfield[LoggingSlipNumber]\" value=\"" . htmlspecialchars($attr->getValue1(), ENT_QUOTES, "UTF-8") . "\" style=\"width:95%;\">"
			);
		}
				
		return $array;
	}
	
	/**
	 * 編集画面で編集するための設定内容を取得する
	 * @param int $orderId
	 * @return array saveするための配列
	 */
	function config($orderId){

		if(isset($_POST["Customfield"]["LoggingSlipNumber"])){
			if(strlen($_POST["Customfield"]["LoggingSlipNumber"])){
				self::getLogic()->save($orderId, trim($_POST["Customfield"]["LoggingSlipNumber"]));
			}else{
				self::getLogic()->delete($orderId);
			}
		}
		
		//ここで完結させるため、returnで空の配列を返す
		return array();
	}
	
	private function getLogic(){
		static $logic;
		if(is_null($logic)) $logic = SOY2Logic::createInstance("module.plugins.logging_slip_number.logic.LoggingLogic");
		return $logic;
	}
}
SOYShopPlugin::extension("soyshop.order.customfield", "logging_slip_number", "LoggingSlipNumberOrderCustomfield");
?>