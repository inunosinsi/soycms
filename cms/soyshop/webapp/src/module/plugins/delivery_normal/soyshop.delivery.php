<?php
/*
 * Created on 2009/07/28
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */

class DeliveryNormalModule extends SOYShopDelivery{

	function prepare(){
		SOY2::import("module.plugins.delivery_normal.util.DeliveryNormalUtil");
	}

	function onSelect(CartLogic $cart){
		$this->prepare();

		$module = new SOYShop_ItemModule();
		$module->setId("delivery_normal");
		$module->setName(MessageManager::get("LABEL_POSTAGE"));
		$module->setType("delivery_module");	//typeを指定しておくといいことがある
		$module->setPrice($this->getPrice());
		$cart->addModule($module);

		//属性の登録
		$cart->setOrderAttribute("delivery_normal", MessageManager::get("METHOD_DELIVERY"), $this->getName());

		//お届け日の指定を利用するか？
		$config = DeliveryNormalUtil::getDeliveryDateConfig();
		if(isset($config["use_delivery_date"]) && $config["use_delivery_date"] == 1){
			if(isset($_POST["delivery_date"]) && strlen($_POST["delivery_date"]) > 0){

				$date = $_POST["delivery_date"];
				if(strlen($date) > 9){
					$cart->setOrderAttribute("delivery_normal.date", "お届け日", $date);
				}else{
					$cart->setOrderAttribute("delivery_normal.date", "お届け日", "指定なし");
				}
			} else {
				//カレンダーモードの場合は空文字の場合は指定なしにする
				if(isset($config["use_format_calendar"]) && $config["use_format_calendar"] == 1){
					$cart->setOrderAttribute("delivery_normal.date", "お届け日", "指定なし");
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
				$cart->setOrderAttribute("delivery_normal.time", MessageManager::get("DELIVERY_TIME"), $time);
			}else{
				$cart->setOrderAttribute("delivery_normal.time", MessageManager::get("DELIVERY_TIME"), MessageManager::get("UNSPECIFIED"));
			}
		}
	}

	function getName(){
		$this->prepare();
		return DeliveryNormalUtil::getTitle();
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
		$this->prepare();

		$prices = DeliveryNormalUtil::getPrice();

		$cart = $this->getCart();

		$free = DeliveryNormalUtil::getFreePrice();

		if(isset($free["free"]) && $cart->getItemPrice() >= $free["free"]){
			$price = 0;
		}else{
			$customer = $cart->getCustomerInformation();
			$address = $cart->getAddress();
			$area = $address["area"];
			$price = (isset($prices[$area])) ? (int)$prices[$area] : 0;
		}
		return $price;
	}

	/** マイページ **/
	function edit(){
		$forms = array();
		$attrs = $this->getOrder()->getAttributeList();

		//配送時間帯
		$useDeliveryTime = DeliveryNormalUtil::getUseDeliveryTimeConfig();
		if((isset($useDeliveryTime["use"]) && $useDeliveryTime["use"] == 1)){
			$times = DeliveryNormalUtil::getDeliveryTimeConfig();
			if(count($times)){
				$timeValue = (isset($attrs["delivery_normal.time"]["value"])) ? $attrs["delivery_normal.time"]["value"] : null;

				$f = array();
				$f[] = "<select name=\"delivery_time\">";
				foreach($times as $time){
					if($time == $timeValue){
						$f[] = "<option selected>" . $time . "</option>";
					}else{
						$f[] = "<option>" . $time . "</option>";
					}
				}
				$f[] = "</select>";
				$forms[] = array("label" => "配送時間帯", "form" => implode("\n", $f));
			}
		}

		//お届け日指定
		$config = DeliveryNormalUtil::getDeliveryDateConfig();
		if(isset($config["use_delivery_date"]) && $config["use_delivery_date"] == 1){
			$dates = DeliveryNormalUtil::getDeliveryDateOptions($config);
			if(count($dates)){
				$dateValue = (isset($attrs["delivery_normal.date"]["value"])) ? $attrs["delivery_normal.date"]["value"] : null;

				$f = array();
				$f[] = "<select name=\"delivery_date\">";
				foreach($dates as $key => $date){
					if($key == $dateValue){
						$f[] = "<option value=\"" . $key . "\" selected>" . $date . "</option>";
					}else{
						$f[] = "<option value=\"" . $key . "\">" . $date . "</option>";
					}
				}
				$f[] = "</select>";
				$forms[] = array("label" => "お届け日の指定", "form" => implode("\n", $f));
			}
		}

		return $forms;
	}

	function update(){
		$order = $this->getOrder();
		$attrList = $order->getAttributeList();

		$doUpdate = false;
		$changes = array();

		if(isset($_POST["delivery_date"]) && strlen($_POST["delivery_date"]) && $attrList["delivery_normal.date"]["value"] != $_POST["delivery_date"]){
			$v = htmlspecialchars($_POST["delivery_date"], ENT_QUOTES, "UTF-8");
			$changes[] = array("label" => "お届け日の指定", "old" => $attrList["delivery_normal.date"]["value"], "new" => $v);
			$attrList["delivery_normal.date"]["name"] = "お届け日";
			$attrList["delivery_normal.date"]["value"] = $v;

			$doUpdate = true;
		}

		if(isset($_POST["delivery_time"]) && strlen($_POST["delivery_time"]) && $attrList["delivery_normal.time"]["value"] != $_POST["delivery_time"]){
			$v = htmlspecialchars($_POST["delivery_time"], ENT_QUOTES, "UTF-8");
			$changes[] = array("label" => "配送時間の指定", "old" => $attrList["delivery_normal.time"]["value"], "new" => $v);
			$attrList["delivery_normal.time"]["name"] = "配達時間";
			$attrList["delivery_normal.time"]["value"] = $v;
			$doUpdate = true;
		}

		if($doUpdate){
			$order->setAttributes($attrList);
			try{
				SOY2DAOFactory::create("order.SOYShop_OrderDAO")->update($order);
			}catch(Exception $e){
				var_dump($e);
			}
		}

		return $changes;
	}
}
SOYShopPlugin::extension("soyshop.delivery", "delivery_normal", "DeliveryNormalModule");
