<?php

class ReturnsSlipNumberLogic extends SOY2LogicBase{

	const MODE_RETURN = "return";
	const MODE_CANCEL = "cancel";

	function __construct(){
		SOY2::import("module.plugins.returns_slip_number.util.ReturnsSlipNumberUtil");
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

	function save(int $orderId, string $value){
		//既に登録されているもの
		$oldList = self::_dao()->getRegisteredNumberListByOrderId($orderId);

		//伝票番号の記録テーブルの記録する
		$numbers = explode(",", $value);
		foreach($numbers as $number){
			$number = trim($number);
			if(!strlen($number)) continue;

			//登録がなければ登録する
			if(!isset($oldList[$number])){
				$obj = new SOYShop_ReturnsSlipNumber();
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

		//最後までフラグが立たなかったものはこの場で削除する
		if(count($oldList)){
			foreach($oldList as $number => $flag){
				if($flag != 1){
					try{
						self::_dao()->deleteBySlipNumber($number);
					}catch(Exception $e){
						//var_dump($e);
					}
				}
			}
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

	function changeStatus($slipId, $mode=self::MODE_RETURN){
		$slipNumber = self::getSlipNumberById($slipId);
		if($mode == self::MODE_RETURN){
			$slipNumber->setIsReturn(SOYShop_ReturnsSlipNumber::IS_RETURN);
		}else{
			$slipNumber->setIsReturn(SOYShop_ReturnsSlipNumber::NO_RETURN);
		}

		try{
			self::_dao()->update($slipNumber);
		}catch(Exception $e){
			return false;
		}

		//一つの注文ですべて返送済みにしたら注文ステータスを返却済みにする @Todo 在庫数
		SOY2::import("domain.order.SOYShop_Order");

		//注文が既にキャンセルの場合は注文状態を変更しない
		if(soyshop_get_order_object($slipNumber->getOrderId())->getStatus() == SOYShop_Order::ORDER_STATUS_CANCELED) return true;

		$orderLogic = SOY2Logic::createInstance("logic.order.OrderLogic");
		if(self::_dao()->countNoReturnByOrderId($slipNumber->getOrderId()) === 0){
			$orderLogic->changeOrderStatus($slipNumber->getOrderId(), ReturnsSlipNumberUtil::STATUS_CODE);
		//戻す
		}else{
			$orderLogic->changeOrderStatus($slipNumber->getOrderId(), SOYShop_Order::ORDER_STATUS_SENDED);
		}
		return true;
	}

	private function getSlipNumberById($slipId){
		try{
			return self::_dao()->getById($slipId);
		}catch(Exception $e){
			return new SOYShop_ReturnsSlipNumber();
		}
	}

	private function _dao(){
		static $dao;
		if(is_null($dao)){
			SOY2::import("module.plugins.returns_slip_number.domain.SOYShop_ReturnsSlipNumberDAO");
			$dao = SOY2DAOFactory::create("SOYShop_ReturnsSlipNumberDAO");
		}
		return $dao;
	}
}
