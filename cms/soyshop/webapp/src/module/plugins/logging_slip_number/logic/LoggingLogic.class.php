<?php

class LoggingLogic extends SOY2LogicBase{
	
	private $orderAttributeDao;
	
	function __construct(){
		SOY2::import("module.plugins.logging_slip_number.util.LoggingSlipNumberUtil");
		$this->orderAttributeDao = SOY2DAOFactory::create("order.SOYShop_OrderAttributeDAO");
	}
	
	function getAttribute($orderId){
		try{
			return $this->orderAttributeDao->get($orderId, LoggingSlipNumberUtil::PLUGIN_ID);
		}catch(Exception $e){
			return new SOYShop_OrderAttribute();
		}
	}
	
	function save($orderId, $value){
		$attr = $this->getAttribute($orderId);
		$attr->setValue1(trim($value));
			
		//新規登録
		if(is_null($attr->getOrderId())){
			$attr->setOrderId($orderId);
			$attr->setFieldId(LoggingSlipNumberUtil::PLUGIN_ID);
				
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
			$this->orderAttributeDao->delete($orderId, LoggingSlipNumberUtil::PLUGIN_ID);
		}catch(Exception $e){
			var_dump($e);
		}
	}
}
?>