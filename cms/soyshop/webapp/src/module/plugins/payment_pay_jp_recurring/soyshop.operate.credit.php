<?php

class PayJpRecurringOperateCredit extends SOYShopOperateCreditBase{

	private $recurringLogic;

	function doPostOnOrderDetailPage(SOYShop_Order $order){}

	function doPostOnUserDetailPage(SOYShop_User $user){

		self::prepare();

		//キャンセル
		if(isset($_POST["recurring_cancel"])){
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

		//プランの変更
		if(isset($_POST["recurring_change"])){
			list($res, $err) = $this->recurringLogic->changePlan($_POST["Subscribe"], $_POST["Plan"]);
			if(isset($err["error"]["message"])){
				//エラーメッセージを表示
				PayJpRecurringUtil::save("change_plan_error", $err["error"]["message"]);
			}
		}
	}

	function getFormOnOrderDetailPageTitle(SOYShop_Order $order){}
	function getFormOnOrderDetailPageContent(SOYShop_Order $order){}

	function getFormOnUserDetailPageTitle(SOYShop_User $user){
		return "定期課金プラン詳細";
	}

	function getFormOnUserDetailPageContent(SOYShop_User $user){
		self::prepare();

		SOY2::import("module.plugins.payment_pay_jp_recurring.form.user.RecurringMemberPage");
		$form = SOY2HTMLFactory::createInstance("RecurringMemberPage");
		$form->setUser($user);
		$form->execute();
		return $form->getObject();
	}

	private function prepare(){

		$this->recurringLogic = SOY2Logic::createInstance("module.plugins.payment_pay_jp_recurring.logic.RecurringLogic");
		$this->recurringLogic->initPayJp();
	}
}

SOYShopPlugin::extension("soyshop.operate.credit", "payment_pay_jp_recurring", "PayJpRecurringOperateCredit");
