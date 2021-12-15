<?php

class SlipNumberLogic extends SOY2LogicBase{

	const MODE_DELIVERY = "delivery";
	const MODE_CANCEL = "cancel";

	function __construct(){
		SOY2::import("util.SOYShopPluginUtil");
		SOY2::import("module.plugins.slip_number.util.SlipNumberUtil");
	}

	function getSlipNumberByOrderId(int $orderId){
		$slipNumbers = self::_getSlipNumberListByOrderId($orderId);
		if(!count($slipNumbers)) return "";

		$txt = "";
		foreach($slipNumbers as $num){
			$txt .= $num . ",";
		}
		return trim($txt, ",");
	}

	private function _getSlipNumberListByOrderId(int $orderId){
		try{
			$slips = self::_dao()->getByOrderId($orderId);
		}catch(Exception $e){
			return array();
		}

		if(!count($slips)) return array();

		$list = array();
		foreach($slips as $slip){
			$list[] = $slip->getSlipNumber();
		}
		return $list;
	}

	// modeは使っていない
	function save(int $orderId, string $chain, string $mode = "slip"){
		//既に登録されている伝票番号
		$registeredSlipNumbers = self::_getSlipNumberListByOrderId($orderId);

		//伝票番号の記録テーブルの記録する
		$numbers = soyshop_trim_values_on_array(explode(",", $chain));

		//返送プラグインがある場合は返送の方の伝票を加える
		if(SOYShopPluginUtil::checkIsActive("resend_manager")){
			SOY2::import("module.plugins.resend_manager.util.ResendManagerUtil");
			$resendManSlipNumberChain = ResendManagerUtil::getSlipNumbers($orderId);
			if(strlen($resendManSlipNumberChain)){
				$arr = explode(",", $resendManSlipNumberChain);
				$numbers = array_merge($numbers, $arr);
			}
		}

		$removeDiff = array_diff($registeredSlipNumbers, $numbers);
		$addDiff = array_diff($numbers, $registeredSlipNumbers);
		if(count($removeDiff)){
			foreach($removeDiff as $number){
				self::remove($orderId, $number);
			}
		}
		if(count($addDiff)){
			foreach($addDiff as $number){
				self::add($orderId, $number);
			}
		}

		//Trackingmore連携プラグインを使用している場合
		if(SOYShopPluginUtil::checkIsActive("tracking_more")){
			SOY2Logic::createInstance("module.plugins.tracking_more.logic.TrackLogic")->registerSlipNumbers();
		}
	}

	//新しい伝票番号を加える
	function add(int $orderId, string $new){
		$dao = self::_dao();
		$obj = new SOYShop_SlipNumber();
		$obj->setOrderId($orderId);
		$obj->setSlipNumber(trim($new));
		try{
			$dao->insert($obj);
		}catch(Exception $e){
			//
		}
	}

	function delete(int $orderId){
		$slipNumbers = self::_getSlipNumberListByOrderId($orderId);
		if(!count($slipNumbers)) return;

		foreach($slipNumbers as $slipNumber){
			try{
				self::_dao()->deleteBySlipNumberWithOrderId(trim($slipNumber), $orderId);
			}catch(Exception $e){
				//
			}
		}

		try{
			self::_dao()->deleteByOrderId($orderId);
		}catch(Exception $e){
			//
		}
	}

	//一つだけ取り除く
	function remove(int $orderId, string $number){
		try{
			self::_dao()->deleteBySlipNumberWithOrderId($number, $orderId);
		}catch(Exception $e){
			//
		}
	}

	function convert(string $str){
		return self::_convert($str);
	}

	private function _convert(string $str){
		$str = str_replace("、", ",", $str);
		$str = str_replace(array(" ", "　"), "", $str);
		$str = preg_replace('/,+/', ",", $str);
		$str = trim($str, ",");
		return trim($str);
	}

	//slipIdには伝票番号もあり
	function changeStatus(int $slipId, string $mode="delivery"){
		$slipNumber = self::getSlipNumberById($slipId);
		if($mode == self::MODE_DELIVERY){
			$slipNumber->setIsDelivery(SOYShop_SlipNumber::IS_DELIVERY);
		}else{
			$slipNumber->setIsDelivery(SOYShop_SlipNumber::NO_DELIVERY);
		}

		try{
			self::_dao()->update($slipNumber);
		}catch(Exception $e){
			return false;
		}

		//返送管理プラグインと連携している場合は返送のチェックを行う
		$isResend = false;
		if(SOYShopPluginUtil::checkIsActive("resend_manager")){
			$isResend = SOY2Logic::createInstance("module.plugins.resend_manager.logic.ResendLogic")->checkResendSlipNumber($slipNumber->getOrderId(), $slipNumber->getSlipNumber());
		}

		//一つの注文ですべて配送済みにしたら注文ステータスを配送済みにする
		SOY2::import("domain.order.SOYShop_Order");
		$orderLogic = SOY2Logic::createInstance("logic.order.OrderLogic");
		$cnt = self::_dao()->countNoDeliveryByOrderId($slipNumber->getOrderId());
		if($cnt === 0){
			$orderLogic->changeOrderStatus($slipNumber->getOrderId(), SOYShop_Order::ORDER_STATUS_SENDED);
		//戻す。ただし再送を行っている場合は見ない
		}else if(!$isResend){
			$orderLogic->changeOrderStatus($slipNumber->getOrderId(), SOYShop_Order::ORDER_STATUS_RECEIVED);
		}

		return true;
	}

	private function getSlipNumberById(int $slipId){
		try{
			return self::_dao()->getById($slipId);
		}catch(Exception $e){
			//伝票番号として受け取る可能性も加味して、再度取得を試みる
			try{
				return self::_dao()->getBySlipNumber($slipId);
			}catch(Exception $e){
				return new SOYShop_SlipNumber();
			}
		}
	}

	private function _dao(){
		static $dao;
		if(is_null($dao)){
			SOY2::import("module.plugins.slip_number.domain.SOYShop_SlipNumberDAO");
			$dao = SOY2DAOFactory::create("SOYShop_SlipNumberDAO");
		}
		return $dao;
	}
}
