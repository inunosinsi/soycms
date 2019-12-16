<?php
class CustomPaymentOrderComplete extends SOYShopOrderComplete{

	function execute(SOYShop_Order $order){
		if($this->isUse()){
			
			if(!class_exists("PaymentCustomCommon")){
				include_once(dirname(__FILE__) . "/common.php");
			}
		
			$custom = PaymentCustomCommon::getCustomConfig();
			
			//支払いステータスを管理画面で設定したものにする
			$dao = SOY2DAOFactory::create("order.SOYShop_OrderDAO");
			
			$order->setPaymentStatus($custom["status"]);
			try{
				$dao->updateStatus($order);
			}catch(Exception $e){
				//何もしない
			}
		}
	}
}

SOYShopPlugin::extension("soyshop.order.complete","payment_custom","CustomPaymentOrderComplete");
?>