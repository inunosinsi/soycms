<?php
class PaymentConstructionModule extends SOYShopPayment{

	function onSelect(CartLogic $cart){
		//手数料を加算する
		SOY2::import("module.plugins.payment_construction.util.PaymentConstructionUtil");
		$items = PaymentConstructionUtil::getCommissionItemList();
		if(count($items)){
			foreach($items as $key => $item){
				$moduleId = "payment_commission_" . $key;
				if(isset($_POST["commission_fee"][$key]) && (int)$_POST["commission_fee"][$key] > 0){
					$module = new SOYShop_ItemModule();
					$module->setId($moduleId);
					$module->setType("payment_module_" . $key);//typeを指定しておくといいことがある
					$module->setName($item);
					$module->setPrice((int)$_POST["commission_fee"][$key]);
					$cart->addModule($module);
				}else if((int)$_POST["commission_fee"][$key] === 0){
					$cart->removeModule($moduleId);
				}
			}
		}

        //すべて登録したら、施工費から合算したものを引く
		//施工費は記録
		$moduleId = "payment_construction";
		if(isset($_POST["construction_fee"]) && (int)$_POST["construction_fee"]){
			$cart->removeModule($moduleId);
			$cart->setAttribute("payment_construction_fee", (int)$_POST["construction_fee"]);
			$cart->setOrderAttribute("payment_construction_fee", "施工費", (int)$_POST["construction_fee"]);

			//合算を求める
			$total = (int)$cart->getItemPrice();
			$modules = $cart->getModules();
			if(count($modules)){
				foreach($modules as $mod){
					$total += $mod->getPrice();
				}
			}
			$constructionCommission = (int)$_POST["construction_fee"] - $total;
			$module = new SOYShop_ItemModule();
			$module->setId($moduleId);
			$module->setType("payment_module");//typeを指定しておくといいことがある
			$module->setName("粗利");
			$module->setPrice($constructionCommission);
			$cart->addModule($module);
		}else{
			$cart->clearAttribute("payment_construction_fee");
			$cart->removeModule($moduleId);
			$cart->clearOrderAttribute("payment_construction_fee");
		}
	}

	function getName(){
		return "手数料等の計算";
	}

	function getDescription(){
		SOY2::import("module.plugins.payment_construction.form.CommissionPage");
		$form = SOY2HTMLFactory::createInstance("CommissionPage");
		$form->setConfigObj($this);
		$form->setCart($this->getCart());
		$form->execute();
		return $form->getObject();
	}

	function getPrice(){
		return 0;
	}
}
if(defined("SOYSHOP_ADMIN_PAGE") && SOYSHOP_ADMIN_PAGE){
	SOYShopPlugin::extension("soyshop.payment","payment_construction","PaymentConstructionModule");
}
