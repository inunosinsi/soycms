<?php

class SlipNumberLogic extends SOY2LogicBase{

	private $orderAttributeDao;

	function __construct(){
		SOY2::import("module.plugins.slip_number.util.SlipNumberUtil");
		$this->orderAttributeDao = SOY2DAOFactory::create("order.SOYShop_OrderAttributeDAO");
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
}
