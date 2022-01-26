<?php

class SlipNumberOrderCustomfield extends SOYShopOrderCustomfield{

	function clear(CartLogic $cart){
		$cart->clearAttribute("slip_number.value");
		$cart->clearOrderAttribute("slip_number");
	}

	function doPost($param){
		$slipNumber = (isset($param["slip_number"])) ? trim($param["slip_number"]) : "";
		if(strlen($slipNumber)){
			$slipNumber = SOY2Logic::createInstance("module.plugins.slip_number.logic.SlipNumberLogic")->convert($slipNumber);
			$cart = $this->getCart();
			$cart->setAttribute("slip_number.value", $slipNumber);
			$cart->setOrderAttribute("slip_number", "伝票番号", $slipNumber, true, true);
		}
	}
	function order(CartLogic $cart){
		$cart->clearOrderAttribute("slip_number");
	}

	function complete(CartLogic $cart){
		$orderId = (int)$cart->getAttribute("order_id");
		$slipNumber = (string)$cart->getAttribute("slip_number.value");
		if($orderId > 0 && strlen($slipNumber)){
			SOY2Logic::createInstance("module.plugins.slip_number.logic.SlipNumberLogic")->save($orderId, $slipNumber);
		}
	}

	function getForm(CartLogic $cart){
		if(defined("SOYSHOP_ADMIN_PAGE") && SOYSHOP_ADMIN_PAGE && strpos($_SERVER["REQUEST_URI"], "/Order/Register")){
			$slipNumber = $cart->getAttribute("slip_number.value");
			return array("slip_number" => array(
				"name" => "伝票番号",
				"description" => "<input type=\"text\" name=\"customfield_module[slip_number]\" value=\"" . $slipNumber . "\" placeholder=\"伝票番号を複数登録する場合は、カンマ区切りで登録します。\" style=\"width:95%;\">"
			));
		}
	}

	function display(int $orderId){
		$slipNumberChain = self::_logic()->getSlipNumberByOrderId($orderId);
		if(strlen($slipNumberChain)){
			return array(array(
				"name" => "伝票番号",
				"value" => htmlspecialchars($slipNumberChain, ENT_QUOTES, "UTF-8")
			));
		}

		return array();
	}

	/**
	 * @param int $orderID
	 * @return array labelとformの連想配列を格納
	 */
	function edit(int $orderId){
		$slipNumberChain = self::_logic()->getSlipNumberByOrderId($orderId);
		if(strlen($slipNumberChain)){
			return array(array(
				"label" => "伝票番号",
				"form" => "<input type=\"text\" class=\"form-control\" name=\"Customfield[SlipNumber]\" value=\"" . htmlspecialchars($slipNumberChain, ENT_QUOTES, "UTF-8") . "\" style=\"width:95%;\">"
			));
		}

		return array();
	}

	/**
	 * 編集画面で編集するための設定内容を取得する
	 * @param int $orderId
	 * @return array saveするための配列
	 */
	function config(int $orderId){
		if(isset($_POST["Customfield"]["SlipNumber"]) && strlen($_POST["Customfield"]["SlipNumber"])){
			self::_logic()->save($orderId, trim($_POST["Customfield"]["SlipNumber"]));
		}else{
			self::_logic()->delete($orderId);
		}

		//ここで完結させるため、returnで空の配列を返す
		return array();
	}

	private function _logic(){
		static $logic;
		if(is_null($logic)) $logic = SOY2Logic::createInstance("module.plugins.slip_number.logic.SlipNumberLogic");
		return $logic;
	}
}
SOYShopPlugin::extension("soyshop.order.customfield", "slip_number", "SlipNumberOrderCustomfield");
