<?php

class OrderMailInsertTemplateOrderMail extends SOYShopOrderMail{

	/**
	 * メール本文を取得
	 * @return string
	 */
	function getMailBody(SOYShop_Order $order){
		//商品に紐付いたメールテンプレートを取得
		try{
			$itemOrders = SOY2DAOFactory::create("order.SOYShop_ItemOrderDAO")->getByOrderId($order->getId());
		}catch(Exception $e){
			$itemOrders = array();
		}

		if(!count($itemOrders)) return "";

		$bodies = array();

		SOY2::import("module.plugins.order_mail_insert_template.util.InsertStringTemplateUtil");
		foreach($itemOrders as $itemOrder){
			$fieldId = InsertStringTemplateUtil::getMailFieldIdByItemId($itemOrder->getItemId());
			if(!strlen($fieldId)) continue;
			$txt = InsertStringTemplateUtil::getTextByFieldId($fieldId);
			if(!strlen($txt)) continue;

			$bodies[] = $txt;
		}

		return implode("\n", $bodies);
	}


	function getDisplayOrder(){
		return 10; //@ToDo 番号を決めてない
	}
}

SOYShopPlugin::extension("soyshop.order.mail.user", "order_mail_insert_template", "OrderMailInsertTemplateOrderMail");
SOYShopPlugin::extension("soyshop.order.mail.admin", "order_mail_insert_template", "OrderMailInsertTemplateOrderMail");
