<?php

class PayJpRecurringOperateCredit extends SOYShopOperateCreditBase{

	private $recurringLogic;

	function doPostOnOrderDetailPage(SOYShop_Order $order){}
	function doPostOnUserDetailPage(SOYShop_User $user){}
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
