<?php

class ReturnsSlipNumberLogic extends SOY2LogicBase{

	const MODE_RETURN = "return";
	const MODE_CANCEL = "cancel";

	function __construct(){
		SOY2::import("module.plugins.returns_slip_number.util.ReturnsSlipNumberUtil");
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

	function save(int $orderId, string $chain){
		//既に登録されている伝票番号
		$registeredSlipNumbers = self::_getSlipNumberListByOrderId($orderId);

		//伝票番号の記録テーブルの記録する
		$numbers = soyshop_trim_values_on_array(explode(",", $chain));

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
	}

	//新しい伝票番号を加える
	function add(int $orderId, string $new){
		$dao = self::_dao();
		$obj = new SOYShop_ReturnsSlipNumber();
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

	function changeStatus(int $slipId, string $mode=self::MODE_RETURN){
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

	private function getSlipNumberById(int $slipId){
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
