<?php

class AttributeListComponent extends HTMLList{

	private $orderId;

	protected function populateItem($item, $key) {

		$this->addLabel("attribute_title", array(
			"text" => (isset($item["name"])) ? $item["name"] : ""
		));

		$this->addLabel("attribute_value", array(
			"html" => (isset($item["value"])) ? nl2br(htmlspecialchars($item["value"], ENT_QUOTES, "UTF-8")) : ""
		));

		//支払い方法の場合は変更ボタンを表示
		$this->addModel("is_payment_change_link", array(
			"visible" => self::isPaymentEditable($key)
		));

		$this->addLink("payment_change_link", array(
			"link" => soyshop_get_mypage_url() . "/order/edit/payment/" . $this->orderId
		));

		if(isset($item["hidden"]) && $item["hidden"]) return false;
	}

	private function isPaymentEditable($moduleId){
		if(strpos($moduleId, "payment_") === false) return false;
		if(!SOYShopPluginUtil::checkIsActive("order_edit_on_mypage")) return false;

		//支払い変更のページのテンプレートがあるか？
		if(file_exists(SOYSHOP_MAIN_MYPAGE_TEMPLATE_DIR . "order/edit/PaymentPage.html")) return true;

		return false;
	}

	function setOrderId($orderId){
		$this->orderId = $orderId;
	}
}
