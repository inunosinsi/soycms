<?php
class PaymentConstructionOrderModule extends SOYShopOrderModule{


	function edit($module){
		//施工費も直接取得
		if(!isset($_POST["Attribute"]["payment_construction_fee"]) || !is_numeric($_POST["Attribute"]["payment_construction_fee"])) return $module;
		$constFee = (int)$_POST["Attribute"]["payment_construction_fee"];

		$order = self::getOrder();
		if(is_null($order->getId())) return $module;

		$total = $this->getTotal();	//商品のトータル
		$mods = $order->getModuleList();	//注文オブジェクトに保管されているモジュール
		foreach($mods as $modId => $mod){
			if($modId == "payment_construction" || (int)$mod->getPrice() === 0) continue;
			if(!isset($_POST["Module"][$mod->getId()]["price"])) continue;
			//変更した値を直接取得
			$total += (int)$_POST["Module"][$mod->getId()]["price"];
		}

		$module->setPrice($constFee - $total);
		return $module;
	}

	private function getOrder(){
		try{
			return SOY2DAOFactory::create("order.SOYShop_OrderDAO")->getById($this->getOrderId());
		}catch(Exception $e){
			return new SOYShop_Order();
		}
	}
}

SOYShopPlugin::extension("soyshop.order.module","payment_construction","PaymentConstructionOrderModule");
