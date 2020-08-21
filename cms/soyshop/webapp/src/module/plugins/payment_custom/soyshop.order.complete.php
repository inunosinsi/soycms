<?php
class CustomPaymentOrderComplete extends SOYShopOrderComplete{

	function execute(SOYShop_Order $order){
		if($this->isUse()){
			SOY2::import("module.plugins.payment_custom.util.PaymentCustomUtil");
			$cnf = PaymentCustomUtil::getConfig();

			//支払いステータスを管理画面で設定したものにする
			$order->setPaymentStatus($cnf["status"]);
			try{
				SOY2DAOFactory::create("order.SOYShop_OrderDAO")->updateStatus($order);
			}catch(Exception $e){
				//何もしない
			}
		}
	}
}

SOYShopPlugin::extension("soyshop.order.complete","payment_custom","CustomPaymentOrderComplete");
