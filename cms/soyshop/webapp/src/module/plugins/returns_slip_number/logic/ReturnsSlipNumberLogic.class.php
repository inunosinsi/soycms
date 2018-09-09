<?php

class ReturnsSlipNumberLogic extends SOY2LogicBase{

	const MODE_RETURN = "return";
	const MODE_CANCEL = "cancel";

	private $orderAttributeDao;
	private $slipDao;

	function __construct(){
		SOY2::import("module.plugins.returns_slip_number.util.ReturnsSlipNumberUtil");
		$this->orderAttributeDao = SOY2DAOFactory::create("order.SOYShop_OrderAttributeDAO");
		SOY2::import("module.plugins.returns_slip_number.domain.SOYShop_ReturnsSlipNumberDAO");
		$this->slipDao = SOY2DAOFactory::create("SOYShop_ReturnsSlipNumberDAO");
	}

	function getSlipNumberByOrderId($orderId){
		return self::getAttribute($orderId)->getValue1();
	}

	function getAttribute($orderId){
		try{
			return $this->orderAttributeDao->get($orderId, ReturnsSlipNumberUtil::PLUGIN_ID);
		}catch(Exception $e){
			return new SOYShop_OrderAttribute();
		}
	}

	function save($orderId, $value){

		$attr = self::getAttribute($orderId);
		$attr->setValue1(self::convert($value));

		//新規登録
		if(is_null($attr->getOrderId())){
			$attr->setOrderId($orderId);
			$attr->setFieldId(ReturnsSlipNumberUtil::PLUGIN_ID);

			try{
				$this->orderAttributeDao->insert($attr);
			}catch(Exception $e){
				var_dump($e);
			}
		}else{
			try{
				$this->orderAttributeDao->update($attr);
			}catch(Exception $e){
				var_dump($e);
			}
		}

		//既に登録されているもの
		$oldList = $this->slipDao->getRegisteredNumberListByOrderId($orderId);

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
					$this->slipDao->insert($obj);
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
						$this->slipDao->deleteBySlipNumber($number);
					}catch(Exception $e){
						var_dump($e);
					}
				}
			}
		}
	}

	//新しい伝票番号を加える
	function add($orderId, $new){
		$slipNumbers = self::getSlipNumberByOrderId($orderId);
		self::save($orderId, $slipNumbers . "," . $new);
	}

	function delete($orderId){
		try{
			$this->orderAttributeDao->delete($orderId, ReturnsSlipNumberUtil::PLUGIN_ID);

			//返送伝票番号の方も削除
			$this->slipDao->deleteByOrderId($orderId);
		}catch(Exception $e){
			var_dump($e);
		}
	}

	function convert($str){
		$str = str_replace("、", ",", $str);
		$str = str_replace(array(" ", "　"), "", $str);
		$str = preg_replace('/,+/', ",", $str);
		$str = trim($str, ",");
		return trim($str);
	}

	function changeStatus($slipId, $mode="return"){
		$slipNumber = self::getSlipNumberById($slipId);
		if($mode == self::MODE_RETURN){
			$slipNumber->setIsReturn(SOYShop_ReturnsSlipNumber::IS_RETURN);
		}else{
			$slipNumber->setIsReturn(SOYShop_ReturnsSlipNumber::NO_RETURN);
		}

		try{
			$this->slipDao->update($slipNumber);
		}catch(Exception $e){
			var_dump($e);
			return false;
		}

		//一つの注文ですべて返送済みにしたら注文ステータスを返却済みにする @Todo 在庫数
		SOY2::import("domain.order.SOYShop_Order");
		$orderLogic = SOY2Logic::createInstance("logic.order.OrderLogic");
		$cnt = $this->slipDao->countNoReturnByOrderId($slipNumber->getOrderId());
		if($cnt === 0){
			$orderLogic->changeOrderStatus($slipNumber->getOrderId(), 21);
		//戻す
		}else{
			$orderLogic->changeOrderStatus($slipNumber->getOrderId(), SOYShop_Order::ORDER_STATUS_SENDED);
		}
		return true;
	}

	private function getSlipNumberById($slipId){
		try{
			return $this->slipDao->getById($slipId);
		}catch(Exception $e){
			return new SOYShop_ReturnsSlipNumber();
		}
	}
}
