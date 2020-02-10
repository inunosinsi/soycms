<?php

SOY2::import("module.plugins.payment_pay_jp_recurring.util.PayJpRecurringUtil");
class PayJpRecurringMypageCard extends SOYShopMypageCard{

	private $recurringLogic;

	function hasOptionPage(){
		//カード情報があればtrueとかの制御が欲しい
		return self::_isCustomerToken();
	}

	function getOptionPage(){
		//出力
		if(self::_isCustomerToken()){
			SOY2::import("module.plugins.payment_pay_jp_recurring.option.PayJpRecurringOptionPage");
			$form = SOY2HTMLFactory::createInstance("PayJpRecurringOptionPage");
			$form->execute();
			echo $form->getObject();
		}else{
			echo "カード情報がありません";
		}
	}

	function onPostOptionPage(){

		if(soy2_check_token() && isset($_POST["token"])){
			self::prepare();

			$mypage = MypageLogic::getMyPage();
			$userId = $mypage->getUser()->getId();

			//カード情報を削除して、再登録
			return $this->recurringLogic->updateCardInfo($userId, $_POST["token"], $_POST["name"]);
		}
		return false;
	}

	private function _isCustomerToken(){
		static $res;
		if(is_null($res)){
			$mypage = MypageLogic::getMypage();
			$token = SOY2Logic::createInstance("module.plugins.payment_pay_jp_recurring.logic.RecurringLogic")->getCustomerTokenByUserId($mypage->getUser()->getId());
			$res = (isset($token) && strlen($token));
		}
		return $res;
	}

	private function prepare(){
		$this->recurringLogic = SOY2Logic::createInstance("module.plugins.payment_pay_jp_recurring.logic.RecurringLogic");
		$this->recurringLogic->initPayJp();
	}
}

SOYShopPlugin::extension("soyshop.mypage.card", "payment_pay_jp_recurring", "PayJpRecurringMypageCard");
