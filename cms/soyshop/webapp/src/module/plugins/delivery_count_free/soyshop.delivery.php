<?php
/*
 * 無料配送モジュール
 */
class DeliveryCountFreeModule extends SOYShopDelivery{
	
	function onSelect(CartLogic $cart){
		include_once(dirname(__FILE__) . "/util.php");

		$module = new SOYShop_ItemModule();
		$module->setId("delivery_count_free");
		$module->setName("送料");
		$module->setType("delivery_module");	//typeを指定しておくといいことがある
		$module->setPrice($this->getPrice());
		$cart->addModule($module);

		//属性の登録
		$cart->setOrderAttribute("delivery_count_free","配送方法",$this->getName());

		if(isset($_POST["delivery_count_free_date"]) && strlen($_POST["delivery_count_free_date"]) > 0){
			$cart->setOrderAttribute("delivery_count_free.date","配達希望日", $_POST["delivery_count_free_date"]);
		}else{
			$cart->setOrderAttribute("delivery_count_free.date","配達希望日","指定なし");
		}

		if(isset($_POST["delivery_count_free_time"]) && strlen($_POST["delivery_count_free_time"]) > 0){
			$cart->setOrderAttribute("delivery_count_free.time","配達時間", $_POST["delivery_count_free_time"]);
		}else{
			$cart->setOrderAttribute("delivery_count_free.time","配達時間","指定なし");
		}
		
	}

	function getName(){
		include_once(dirname(__FILE__) . "/util.php");
		return DeliveryCountFreeConfigUtil::getTitle();
	}

	function getDescription(){
		include_once(dirname(__FILE__) . "/util.php");
		include_once(dirname(__FILE__) . "/cart.php");
		$form = SOY2HTMLFactory::createInstance("DeliveryCountFreeCartFormPage");
		$form->setCart($this->getCart());
		$form->execute();
		return $form->getObject();
	}
	
	function getPrice(){
		
		//カートに入っている商品の総合計数を計算する
		$totalCount = 0;
		$items = $this->getCart()->getItems();
		if(count($items) > 0){
			foreach($items as $item){
				$totalCount = $totalCount + (int)$item->getItemCount();
			}
		}
		
		return DeliveryCountFreeConfigUtil::getShippingFee($totalCount, $this->getCart()->getAddress());
	}

}
SOYShopPlugin::extension("soyshop.delivery","delivery_count_free","DeliveryCountFreeModule");
