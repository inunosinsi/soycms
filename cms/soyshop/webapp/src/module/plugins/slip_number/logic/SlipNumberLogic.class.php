<?php

class SlipNumberLogic extends SOY2LogicBase{

	const MODE_DELIVERY = "delivery";
	const MODE_CANCEL = "cancel";

	private $orderAttributeDao;
	private $slipDao;

	function __construct(){
		SOY2::import("util.SOYShopPluginUtil");
		SOY2::import("module.plugins.slip_number.util.SlipNumberUtil");
		$this->orderAttributeDao = SOY2DAOFactory::create("order.SOYShop_OrderAttributeDAO");
		SOY2::import("module.plugins.slip_number.domain.SOYShop_SlipNumberDAO");
		$this->slipDao = SOY2DAOFactory::create("SOYShop_SlipNumberDAO");
	}

	function getSlipNumberByOrderId($orderId){
		return self::getAttribute($orderId)->getValue1();
	}

	function getAttribute($orderId){
		try{
			return $this->orderAttributeDao->get($orderId, SlipNumberUtil::PLUGIN_ID);
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
			$attr->setFieldId(SlipNumberUtil::PLUGIN_ID);

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
			$number = trim(trim($number));
			if(!strlen($number)) continue;

			//登録がなければ登録する
			if(!isset($oldList[$number])){
				$obj = new SOYShop_SlipNumber();
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

		//Trackingmore連携プラグインを使用している場合
		SOY2::import("util.SOYShopPluginUtil");
		if(SOYShopPluginUtil::checkIsActive("tracking_more")){
			SOY2Logic::createInstance("module.plugins.tracking_more.logic.TrackLogic")->registSlipNumbers();
		}
	}

	//新しい伝票番号を加える
	function add($orderId, $new){
		$slipNumbers = self::getSlipNumberByOrderId($orderId);
		self::save($orderId, $slipNumbers . "," . $new);
	}

	function delete($orderId){
		try{
			$this->orderAttributeDao->delete($orderId, SlipNumberUtil::PLUGIN_ID);
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

	//slipIdには伝票番号もあり
	function changeStatus($slipId, $mode="delivery"){
		$slipNumber = self::getSlipNumberById($slipId);
		if($mode == self::MODE_DELIVERY){
			$slipNumber->setIsDelivery(SOYShop_SlipNumber::IS_DELIVERY);
		}else{
			$slipNumber->setIsDelivery(SOYShop_SlipNumber::NO_DELIVERY);
		}

		try{
			$this->slipDao->update($slipNumber);
		}catch(Exception $e){
			return false;
		}

		//返送管理プラグインと連携している場合は返送のチェックを行う
		if(SOYShopPluginUtil::checkIsActive("resend_manager")){
			SOY2Logic::createInstance("module.plugins.resend_manager.logic.ResendLogic")->checkResendSlipNumber($slipNumber->getOrderId(), $slipNumber->getSlipNumber());
		}

		//一つの注文ですべて配送済みにしたら注文ステータスを配送済みにする
		SOY2::import("domain.order.SOYShop_Order");
		$orderLogic = SOY2Logic::createInstance("logic.order.OrderLogic");
		$cnt = $this->slipDao->countNoDeliveryByOrderId($slipNumber->getOrderId());
		if($cnt === 0){
			$orderLogic->changeOrderStatus($slipNumber->getOrderId(), SOYShop_Order::ORDER_STATUS_SENDED);
		//戻す
		}else{
			$orderLogic->changeOrderStatus($slipNumber->getOrderId(), SOYShop_Order::ORDER_STATUS_RECEIVED);
		}

		return true;
	}

	private function getSlipNumberById($slipId){
		try{
			return $this->slipDao->getById($slipId);
		}catch(Exception $e){
			//伝票番号として受け取る可能性も加味して、再度取得を試みる
			try{
				return $this->slipDao->getBySlipNumber($slipId);
			}catch(Exception $e){
				return new SOYShop_SlipNumber();
			}
		}
	}
}
