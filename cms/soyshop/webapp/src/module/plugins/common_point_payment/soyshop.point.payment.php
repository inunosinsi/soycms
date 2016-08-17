<?php

class CommonPointPayment extends SOYShopPointPaymentBase{
	private $config;
	
	function doPost($param, $userId){
		
		$cart = $this->getCart();
		
		if(isset($param) && (int)$_POST["point_module"] > 0){
			
			$allpoint = self::getPoint($userId);
			
			if((int)$_POST["point_module"] <= $allpoint){
				$point = (int)$_POST["point_module"];
				$module = new SOYShop_ItemModule();
				$module->setId("point_payment");
				$module->setName(MessageManager::get("MODULE_NAME_POINT_PAYMENT"));
				$module->setType("point_payment_module");	//typeを指定すると同じtypeのモジュールは同時使用できなくなる
		
				$module->setPrice(0 - $point);//負の値
				
				$cart->addModule($module);
				
				//合算が0の場合はクレジット支払を禁止する
				if(self::getTotalPrice($cart->getItems()) == $point){
					foreach($cart->getModules() as $m){
						//モジュール内にクレジットという文字がある場合はエラーを追加
						if(
							strpos($m->getName(), "クレジット") !== false ||
							strpos($m->getName(), "PayPal") !== false
						
						){
							$cart->addErrorMessage("payment", "全額ポイント支払の場合はクレジットカード支払は利用できません。");
						}
					}
				}

				//属性の登録
				$cart->setAttribute("point_payment", $point);
				$cart->setOrderAttribute("point_payment", MessageManager::get("MODULE_NAME_POINT_PAYMENT"), MessageManager::get("MODULE_DESCRIPTION_POINT_PAYMENT", array("point" => $point)));
				
			}else{
				$cart->addErrorMessage("point", "所持しているポイントよりもポイントを多く指定しています。");
				$cart->removeModule("point_payment");
			}
		}
	}
	
	function clear(){
		
		$cart = $this->getCart();
		
		$cart->clearAttribute("point_payment");
		$cart->clearOrderAttribute("point_payment");
		$cart->removeModule("point_payment");
	}
	
	function order(){
		$cart = $this->getCart();
		$cart->clearOrderAttribute("point_paiment");
	}

	function hasError($param){
		$cart = $this->getCart();
	}
	
	function getError(){
		$cart = $this->getCart();
		return $cart->getAttribute("point_payment.error");
	}

	function getName(){
		return MessageManager::get("MODULE_NAME_POINT_PAYMENT");
	}
	
	function getDescription($userId){
		
		$cart = $this->getCart();
		$point = self::displayPoint($userId);
		$value = $cart->getAttribute("point_payment");
		
		$html = array();
		$html[] = "<input type=\"number\" name=\"point_module\" id=\"point_payment\" value=\"" . $value . "\" style=\"width:100px;\">ポイント分使用する<br>";
		$html[] = "<label><input type=\"checkbox\" id=\"use_all_point\">ポイントをすべて使用する</label>";
		$html[] = " 所持ポイント:" . $point;
		$html[] = "<input type=\"hidden\" id=\"have_point\" value=\"" . $point . "\">";
		$html[] = "<script>";
		$html[] = "(function(){";
		$html[] = "	var usePointAll = document.querySelector('#use_all_point');";
		$html[] = "	usePointAll.addEventListener('click', function(){";
		$html[] = "		document.querySelector('#point_payment').value = document.querySelector('#have_point').value;";
		$html[] = "	})";
		$html[] = "})();";
		$html[] = "</script>";		

		return implode("", $html);
	}
	
	private function displayPoint($userId){
		$logic = SOY2Logic::createInstance("module.plugins.common_point_base.logic.PointBaseLogic");
		return $logic->getPointByUserId($userId)->getPoint();
	}
	
	private function getPoint($userId){
		$cart = $this->getCart();
		
		$logic = SOY2Logic::createInstance("module.plugins.common_point_base.logic.PointBaseLogic");
		$point = $logic->getPointByUserId($userId)->getPoint();
		
		$total = $cart->getTotalPrice();
		
		if($point <= $total){
			$price = $point;
		}else{
			$price = $total;
		}
		
		return $price;
	}
	
	private function getTotalPrice($items){
		$total = 0;
		if(count($items)) foreach($items as $item){
			$total += $item->getTotalPrice();
		}
		return $total;
	}
}
SOYShopPlugin::extension("soyshop.point.payment", "common_point_payment", "CommonPointPayment");
?>