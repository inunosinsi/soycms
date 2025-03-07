<?php
class PaymentPayJpNotification extends SOYShopNotification{

	function execute(){
		$cart = CartLogic::getCart();
		
		$orderId = (int)$cart->getAttribute("order_id");
		if($orderId === 0) {
			echo "error : order id is nothing.";
			exit;
		}
		$order = soyshop_get_order_object($orderId);
		if(!is_numeric($order->getId())){
			echo "error : order object is nothing.";
			exit;
		}

		$attr = $order->getAttribute("payment_pay_jp.id");
		if(!isset($attr["value"])){
			echo "error charge token is nothing";
			exit;
		}

		$payJpId = $attr["value"];

		$logic = SOY2Logic::createInstance("module.plugins.payment_pay_jp.logic.PayJpLogic");
		$logic->initPayJp();
		list($res, $err) = $logic->retrieve($payJpId);

		$ok = true;

		if($res->three_d_secure_status != "verified"){
			if(PayJpUtil::isAttempt() && $res->three_d_secure_status == "attempted"){
				// throw
			}else{
				$ok = false;	
			}
		}

		if(!$ok) {
			// Cart05に戻る
			$cart->setAttribute("page", "Cart05");
			$cart->save();
			soyshop_redirect_cart();
			exit;
		}
		
		$res->tdsFinish();

		$order->setStatus(SOYShop_Order::ORDER_STATUS_REGISTERED);
		$order->setPaymentStatus(SOYShop_Order::PAYMENT_STATUS_CONFIRMED);		
		soyshop_get_hash_table_dao("order")->updateStatus($order);

		$cart->setAttribute("page", "Complete");
		$cart->save();
		soyshop_redirect_cart();		
	}
}
SOYShopPlugin::extension("soyshop.notification", "payment_pay_jp", "PaymentPayJpNotification");
