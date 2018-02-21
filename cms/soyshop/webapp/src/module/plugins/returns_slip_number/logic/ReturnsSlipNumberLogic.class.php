<?php

class ReturnsSlipNumberLogic extends SOY2LogicBase{

	private $orderAttributeDao;

	function __construct(){
		SOY2::import("module.plugins.returns_slip_number.util.ReturnsSlipNumberUtil");
		$this->orderAttributeDao = SOY2DAOFactory::create("order.SOYShop_OrderAttributeDAO");
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
		$attr->setValue1(trim($value));

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
	}

	function delete($orderId){
		try{
			$this->orderAttributeDao->delete($orderId, ReturnsSlipNumberUtil::PLUGIN_ID);
		}catch(Exception $e){
			var_dump($e);
		}
	}
}
