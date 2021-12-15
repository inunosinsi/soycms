<?php

class LogLogic extends SOY2LogicBase {

	function __construct(){}

	// モードとラベルは定数で持つ
	function save(SOYShop_Order $order){
		//検索用でオーダーカスタムフィールド(日付)に値を突っ込む
		$attr = soyshop_get_order_date_attribute_object($order->getId(), "order_invoice_mode_" . ORDER_DOCUMENT_MODE);
		$attr->setValue(time());
		soyshop_save_order_date_attribute_object($attr);
	}
}
