<?php
/*
 * Created on 2009/07/28
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */

class OrderInvoiceCustomfieldModule extends SOYShopOrderCustomfield{

	function display($orderId){
		$attr = self::getAttributeByOrderId($orderId);
		if(strlen($attr->getValue1())){
			return array(array("name" => "納品書の最終出力日", "value" => date("Y-m-d H:i:s", $attr->getValue1())));
		}
	}

	private function getAttributeByOrderId($orderId){
		try{
			return self::dao()->get($orderId, "order_invoice_mode_delivery");
		}catch(Exception $e){
			return new SOYShop_OrderDateAttribute();
		}

	}

	private function dao(){
		static $dao;
		if(is_null($dao)) $dao = SOY2DAOFactory::create("order.SOYShop_OrderDateAttributeDAO");
		return $dao;
	}
}
SOYShopPlugin::extension("soyshop.order.customfield", "order_invoice", "OrderInvoiceCustomfieldModule");
