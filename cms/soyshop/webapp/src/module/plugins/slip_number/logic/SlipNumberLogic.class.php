<?php

class SlipNumberLogic extends SOY2LogicBase{

	const MODE_DELIVERY = "delivery";
	const MODE_CANCEL = "cancel";

	function __construct(){
		SOY2::import("util.SOYShopPluginUtil");
		SOY2::import("module.plugins.slip_number.util.SlipNumberUtil");
	}

	function getSlipNumberByOrderId(int $orderId){
		try{
			$slips = self::_dao()->getByOrderId($orderId);
		}catch(Exception $e){
			$slips = array();
		}
		if(!count($slips)) return "";
		$txt = "";
		foreach($slips as $slip){
			$txt .= $slip->getSlipNumber() . ",";
		}
		return trim($txt, ",");
	}

	// modeは使っていない
	function save(int $orderId, string $value, string $mode = "slip"){
		//既に登録されているもの
		$oldList = self::_dao()->getRegisteredNumberListByOrderId($orderId);

		//伝票番号の記録テーブルの記録する
		$numbers = explode(",", $value);
		foreach($numbers as $number){
			$number = trim($number);
			if(!strlen($number)) continue;

			//登録がなければ登録する
			if(!isset($oldList[$number])){
				$obj = new SOYShop_SlipNumber();
				$obj->setSlipNumber($number);
				$obj->setOrderId($orderId);
				try{
					self::_dao()->insert($obj);
				}catch(Exception $e){
					//
				}
			}else if(isset($oldList[$number])){
				$oldList[$number] = 1;	//登録があればフラグを立てる
			}
		}

		SOY2::import("util.SOYShopPluginUtil");

		//最後までフラグが立たなかったものはこの場で削除する
		if(count($oldList)){
			foreach($oldList as $number => $flag){
				//再送設定がある場合は削除しない
				if(SOYShopPluginUtil::checkIsActive("resend_manager")){
					SOY2::import("module.plugins.resend_manager.util.ResendManagerUtil");
					if($number == ResendManagerUtil::getSlipNumber($orderId)) $flag = 1;
				}

				if($flag !== 1){
					try{
						self::_dao()->deleteBySlipNumberWithOrderId($number, $orderId);
					}catch(Exception $e){
						//var_dump($e);
					}
				}
			}
		}

		//Trackingmore連携プラグインを使用している場合
		if(SOYShopPluginUtil::checkIsActive("tracking_more")){
			SOY2Logic::createInstance("module.plugins.tracking_more.logic.TrackLogic")->registSlipNumbers();
		}
	}

	//新しい伝票番号を加える
	function add(int $orderId, string $new){
		$new = trim($new);
		$slipNumberChain = self::getSlipNumberByOrderId($orderId);

		//同じ文字列があった場合はスルーする
		if(is_bool(strpos($slipNumberChain, $new))){
			self::save($orderId, $slipNumberChain . "," . $new);
		}
	}

	function delete(int $orderId){
		$slipNumbers = explode(",", self::getSlipNumberByOrderId($orderId));
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
