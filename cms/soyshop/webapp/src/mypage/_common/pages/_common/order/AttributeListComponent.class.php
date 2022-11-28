<?php

class AttributeListComponent extends HTMLList{

	private $orderId;

	protected function populateItem($entity, $key) {
		if(!is_string($key)) $key = "";

		$this->addLabel("attribute_title", array(
			"text" => (isset($entity["name"])) ? $entity["name"] : ""
		));

		$this->addLabel("attribute_value", array(
			"html" => (isset($entity["value"]) && is_string($entity["value"])) ? nl2br(htmlspecialchars($entity["value"], ENT_QUOTES, "UTF-8")) : ""
		));

		//支払い方法の場合は変更ボタンを表示
		$this->addModel("is_payment_change_link", array(
			"visible" => self::isPaymentEditable($key)
		));

		$this->addLink("payment_change_link", array(
			"link" => soyshop_get_mypage_url() . "/order/edit/payment/" . $this->orderId
		));

		if(isset($entity["hidden"]) && $entity["hidden"]) return false;
	}

	private function isPaymentEditable(string $moduleId){
		if(is_bool(strpos($moduleId, "payment_"))) return false;
		if(!SOYShopPluginUtil::checkIsActive("order_edit_on_mypage")) return false;

		//支払い変更のページのテンプレートがあるか？
		if(file_exists(SOYSHOP_MAIN_MYPAGE_TEMPLATE_DIR . "order/edit/PaymentPage.html")) return true;

		return false;
	}

	function setOrderId($orderId){
		$this->orderId = $orderId;
	}
}
