<?php

class ReturnsSlipNumberOrderCustomfield extends SOYShopOrderCustomfield{

	function clear(CartLogic $cart){
		$cart->clearAttribute("returns_slip_number.value");
		$cart->clearOrderAttribute("returns_slip_number");
	}

	function doPost(array $param){
		$slipNumber = (isset($param["returns_slip_number"])) ? trim($param["returns_slip_number"]) : "";
		if(strlen($slipNumber)){
			$slipNumber = SOY2Logic::createInstance("module.plugins.returns_slip_number.logic.ReturnsSlipNumberLogic")->convert($slipNumber);
			$cart = $this->getCart();
			$cart->setAttribute("returns_slip_number.value", $slipNumber);
			$cart->setOrderAttribute("returns_slip_number", "返送伝票番号", $slipNumber, true, true);
		}
	}

	function order(CartLogic $cart){
		$cart->clearOrderAttribute("returns_slip_number");
	}

	function complete(CartLogic $cart){
		$orderId = $cart->getAttribute("order_id");
		$slipNumber = $cart->getAttribute("returns_slip_number.value");
		if(is_numeric($orderId) && strlen($slipNumber)){
			SOY2Logic::createInstance("module.plugins.returns_slip_number.logic.ReturnsSlipNumberLogic")->save($orderId, $slipNumber);
		}
	}

	function getForm(CartLogic $cart){
		if(defined("SOYSHOP_ADMIN_PAGE") && SOYSHOP_ADMIN_PAGE && strpos($_SERVER["REQUEST_URI"], "/Order/Register")){
			$slipNumber = $cart->getAttribute("returns_slip_number.value");
			if(!is_string($slipNumber)) $slipNumber = "";
			return array("returns_slip_number" => array(
				"name" => "返送伝票番号",
				"description" => "<input type=\"text\" name=\"customfield_module[returns_slip_number]\" value=\"" . $slipNumber . "\" placeholder=\"伝票番号を複数登録する場合は、カンマ区切りで登録します。\" style=\"width:95%;\">"
			));
		}
		return array();
	}

	function display(int $orderId){
		$slipNumberChain = self::_logic()->getSlipNumberByOrderId($orderId);
		if(strlen($slipNumberChain)){
			return array(array(
				"name" => "返送伝票番号",
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
				"label" => "返送伝票番号",
				"form" => "<input type=\"text\" class=\"form-control\" name=\"Customfield[ReturnsSlipNumber]\" value=\"" . htmlspecialchars($slipNumberChain, ENT_QUOTES, "UTF-8") . "\" style=\"width:95%;\">"
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
		if(isset($_POST["Customfield"]["ReturnsSlipNumber"]) && strlen($_POST["Customfield"]["ReturnsSlipNumber"])){
			self::_logic()->save($orderId, trim($_POST["Customfield"]["ReturnsSlipNumber"]));
		}else{
			self::_logic()->delete($orderId);
		}

		//ここで完結させるため、returnで空の配列を返す
		return array();
	}

	private function _logic(){
		static $logic;
		if(is_null($logic)) $logic = SOY2Logic::createInstance("module.plugins.returns_slip_number.logic.ReturnsSlipNumberLogic");
		return $logic;
	}
}
SOYShopPlugin::extension("soyshop.order.customfield", "returns_slip_number", "ReturnsSlipNumberOrderCustomfield");
