<?php

class CommonPointPayment extends SOYShopPointPaymentBase{
	private $config;

	function doPost(int $param, int $userId){

		$cart = $this->getCart();

		//使用したいポイント
		$usePoint = (int)$param;

		if(isset($param) && $usePoint > 0){

			//所有するポイントよりも指定するポイントが多かった場合
			$ownedPoint = self::getPointObjByUserId($userId);
			if($usePoint > $ownedPoint){
				$cart->addErrorMessage("point", "所持しているポイントよりもポイントを多く指定しています。");
				$cart->removeModule("point_payment");
			}else{
				$allpoint = self::getPoint($userId);

				if($usePoint <= $allpoint){
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

					//使用ポイント数を保持しておく
					$cart->setAttribute("point_payment", $point);

					//ポイント支払い：○○ポイントを使用する
					$cart->setOrderAttribute("point_payment", MessageManager::get("MODULE_NAME_POINT_PAYMENT"), MessageManager::get("MODULE_DESCRIPTION_POINT_PAYMENT", array("point" => $point)));
				}else{
					$cart->addErrorMessage("point", "合計金額以上のポイントを使用しています。");
					$cart->removeModule("point_payment");
				}
			}
		}
	}

	function clear(){

		$cart = $this->getCart();

		$cart->clearAttribute("point_payment");
		$cart->clearAttribute("point_payment.error");
		$cart->clearOrderAttribute("point_payment");
		$cart->removeModule("point_payment");
	}

	/**
	 * 注文実行前に実行される
	 * {@inheritDoc}
	 * @see SOYShopPointPaymentBase::order()
	 */
	function order(){
		//ポイント数に不足がないかチェックする
		//同時に処理が走った場合の不正操作防止はsoyshop.order.customfieldでやっている

		$cart = $this->getCart();

		//使用ポイント数はポイント履歴として保存するので、注文実行前にorderAttributeからは消しておく
		$cart->clearOrderAttribute("point_paiment");
		SOY2Logic::createInstance("module.plugins.common_point_base.logic.PointBaseLogic", array("cart" => $cart))->checkIfPointIsEnoughAndValidBeforeOrder();
	}

	/**
	 * 選択時に実行される
	 * {@inheritDoc}
	 * @see SOYShopPointPaymentBase::hasError()
	 */
	function hasError(array $param){
		//ここでのチェックはあまり意味がないのでやらない
		return false;
	}

	function getError(int $userId){
		$cart = $this->getCart();
		return $cart->getAttribute("point_payment.error");
	}

	function getName(int $userId){
		$pointObj = SOY2Logic::createInstance("module.plugins.common_point_base.logic.PointBaseLogic")->getPointObjByUserId($userId);

		//ログインしている場合だけポイントを表示する
		if(strlen($pointObj->getUserId())){
			return MessageManager::get("MODULE_NAME_POINT_PAYMENT");
		}else{
			return "";
		}
	}

	function getDescription(int $userId){

		$cart = $this->getCart();
		$user = $cart->getCustomerInformation();
		$point = self::getPointObjByUserId($userId);
		$value = $cart->getAttribute("point_payment");

		//決済時にポイントが不足して戻ってきたときのためのエラー
		if($value > 0 && $point < $value){
			$cart->log("not enough point: owned=".$point." < needed=".$value);
			$cart->setAttribute("point_payment.error", "ポイントが不足しています。");
		}else{
			$cart->clearAttribute("point_payment.error");
		}

		$html = array();
		$html[] = "<input type=\"number\" name=\"point_module\" id=\"point_payment\" value=\"" . $value . "\" style=\"width:100px;\">ポイント分使用する<br>";
		$html[] = "<label><input type=\"checkbox\" id=\"use_all_point\">ポイントをすべて使用する</label>";
		$html[] = " 所持ポイント:" . $point;
		$html[] = "<input type=\"hidden\" id=\"have_point\" value=\"" . self::getPoint($userId) . "\">";
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

	/**
	 * 使用可能な所有ポイント
	 * @param unknown $userId
	 * @return unknown
	 */
	private function getPointObjByUserId(int $userId){
		$pointObj = SOY2Logic::createInstance("module.plugins.common_point_base.logic.PointBaseLogic")->getPointObjByUserId($userId);

		//有効期限チェック
		$timeLimit = $pointObj->getTimeLimit();
		$timeLimit = (isset($timeLimit)) ? (int)$timeLimit : null;
		if(isset($timeLimit) && $timeLimit >0 && $timeLimit < time()){
			//期限切れの場合は0にする
			return 0;
		}else{
			return (int)$pointObj->getPoint();
		}
	}

	private function getPoint(int $userId){
		$cart = $this->getCart();

		$point = self::getPointObjByUserId($userId);
		$total = $cart->getTotalPrice(true);	//外税を省いた合算
		return ($point <= $total) ? $point : $total;
	}

	private function getTotalPrice(array $items){
		$total = 0;
		if(count($items)) foreach($items as $item){
			$total += $item->getTotalPrice();
		}
		return $total;
	}
}
SOYShopPlugin::extension("soyshop.point.payment", "common_point_payment", "CommonPointPayment");
