<?php

class PayJpRecurringOperateCredit extends SOYShopOperateCreditBase{

	private $recurringLogic;

	function doPostOnOrderDetailPage(SOYShop_Order $order){}

	function doPostOnUserDetailPage(SOYShop_User $user){

		//キャンセル
		if(isset($_POST["recurring_cancel"])){
			self::prepare();
			list($res, $err) = $this->recurringLogic->cancel($_POST["Subscribe"]);
			if(isset($res) && $res->canceled_at > 0){
				//SOY Shopの方の注文もキャンセルにする
				$orderDao = SOY2DAOFactory::create("order.SOYShop_OrderDAO");
				try{
					$order = $orderDao->getById($_POST["OrderId"]);
				}catch(Exception $e){
					return;
				}
				$order->setStatus(SOYShop_Order::ORDER_STATUS_CANCELED);
				$orderDao->updateStatus($order);
			}
		}
	}

	function getFormOnOrderDetailPageTitle(SOYShop_Order $order){}
	function getFormOnOrderDetailPageContent(SOYShop_Order $order){}

	function getFormOnUserDetailPageTitle(){
		return "定期課金プラン詳細";
	}

	function getFormOnUserDetailPageContent(SOYShop_User $user){
		SOY2::import("module.plugins.payment_pay_jp_recurring.form.user.RecurringMemberPage");
		$form = SOY2HTMLFactory::createInstance("RecurringMemberPage");
		$form->setUser($user);
		//$form->setParams($params);
		$form->execute();
		return $form->getObject();
	}

	private function prepare(){
		$this->recurringLogic = SOY2Logic::createInstance("module.plugins.payment_pay_jp_recurring.logic.RecurringLogic");
		$this->recurringLogic->initPayJp();
	}
}

SOYShopPlugin::extension("soyshop.operate.credit", "payment_pay_jp_recurring", "PayJpRecurringOperateCredit");
