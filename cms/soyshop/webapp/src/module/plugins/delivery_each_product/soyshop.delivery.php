<?php

class DeliveryEachProductModule extends SOYShopDelivery{

    function onSelect(CartLogic $cart){
      $module = new SOYShop_ItemModule();
      $module->setId("delivery_each_product");
      $module->setName("送料");
      $module->setType("delivery_module");    //typeを指定しておくといいことがある
      $module->setPrice($this->getPrice());
      $cart->addModule($module);

      //属性の登録
  		$cart->setOrderAttribute("delivery_each_product", MessageManager::get("METHOD_DELIVERY"), $this->getName());

      //お届け日の指定を利用するか？
      SOY2::import("module.plugins.delivery_normal.util.DeliveryNormalUtil");
  		$config = DeliveryNormalUtil::getDeliveryDateConfig();
  		if(isset($config["use_delivery_date"]) && $config["use_delivery_date"] == 1){
  			if(isset($_POST["delivery_date"]) && strlen($_POST["delivery_date"]) > 0){

  				$date = $_POST["delivery_date"];
  				if(strlen($date) > 9){
  					$cart->setOrderAttribute("delivery_each_product.date", "お届け日", $date);
  				}else{
  					$cart->setOrderAttribute("delivery_each_product.date", "お届け日", "指定なし");
  				}
  			}
  		}

  		//配達時間帯の指定を利用するか？
  		$useDeliveryTime = DeliveryNormalUtil::getUseDeliveryTimeConfig();
  		if($useDeliveryTime["use"] == 1){

  			if(isset($_POST["delivery_time"]) && strlen($_POST["delivery_time"]) > 0){
  				$time = $_POST["delivery_time"];
  				if(defined("SOYSHOP_IS_MOBILE") && defined("SOYSHOP_MOBILE_CHARSET") && SOYSHOP_MOBILE_CHARSET == "Shift_JIS"){
  					$time = mb_convert_encoding($time, "UTF-8", "SJIS");
  				}
  				$cart->setOrderAttribute("delivery_each_product.time", MessageManager::get("DELIVERY_TIME"), $time);
  			}else{
  				$cart->setOrderAttribute("delivery_each_product.time", MessageManager::get("DELIVERY_TIME"), MessageManager::get("UNSPECIFIED"));
  			}
  		}
    }

    function getName(){
        return "宅配便";
    }

    function getDescription(){
        SOY2::import("module.plugins.delivery_normal.cart.DeliveryNormalCartPage");
        $form = SOY2HTMLFactory::createInstance("DeliveryNormalCartPage");
    		$form->setConfigObj($this);
    		$form->setCart($this->getCart());
    		$form->execute();
    		return $form->getObject();
    }

    function getPrice(){
        $price = 0;

        SOY2::import("module.plugins.delivery_each_product.util.DeliveryEachProductUtil");

        $address = $this->getCart()->getAddress();

        $itemOrders = $this->getCart()->getItems();
        foreach($itemOrders as $itemOrder){
            $v = DeliveryEachProductUtil::get($itemOrder->getItemId(), DeliveryEachProductUtil::MODE_FEE);
            if(!isset($v) || is_null($v) || !strlen($v)) continue;

            $prices = soy2_unserialize($v);
            if(!isset($prices[$address["area"]])) continue;

            $price += (int)$prices[$address["area"]];
        }

        return $price;
    }
}
SOYShopPlugin::extension("soyshop.delivery", "delivery_each_product", "DeliveryEachProductModule");
