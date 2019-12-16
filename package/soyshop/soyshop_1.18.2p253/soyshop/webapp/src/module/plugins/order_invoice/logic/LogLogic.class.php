<?php

class LogLogic extends SOY2LogicBase {

	function __construct(){}

	// モードとラベルは定数で持つ
	function save(SOYShop_Order $order){
		//検索用でオーダーカスタムフィールド(日付)に値を突っ込む
		$attrDao = self::dao();
		$attr = new SOYShop_OrderDateAttribute();
		$attr->setOrderId($order->getId());
		$attr->setFieldId("order_invoice_mode_" . ORDER_DOCUMENT_MODE);
		$attr->setValue1(time());

		try{
			self::dao()->insert($attr);
		}catch(Exception $e){
			try{
				self::dao()->update($attr);
			}catch(Exception $e){
				//
			}
		}
	}

	private function dao(){
		static $dao;
		if(is_null($dao)) $dao = SOY2DAOFactory::create("order.SOYShop_OrderDateAttributeDAO");
		return $dao;
	}
}
