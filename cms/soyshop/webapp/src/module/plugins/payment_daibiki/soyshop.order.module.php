<?php

class PaymentDaibikiOrderModule extends SOYShopOrderModule{

	/**
	 * メール本文を取得
	 * @return string
	 */
	function edit($module){

		//自動計算モード
		SOY2::import("module.plugins.payment_daibiki.util.PaymentDaibikiUtil");
		$config = PaymentDaibikiUtil::getConfig();
		if(isset($config["auto_calc"]) && (int)$config["auto_calc"] === 1){

			$total = $this->getTotal();

			$logic = SOY2Logic::createInstance("module.plugins.payment_daibiki.logic.DaibikiLogic");

			//代金合計から
			if($logic->checkNoFobiddenItem($this->getItemOrders())){
				$module->setPrice($logic->calcReturnValue($total));
			//代引きなしの商品がある場合
			}else{
				$module->setPrice(0);
			}
		}

		return $module;
	}
}

SOYShopPlugin::extension("soyshop.order.module","payment_daibiki","PaymentDaibikiOrderModule");
