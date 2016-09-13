<?php

class BlackListLogic extends SOY2LogicBase{
	
	const PLUGIN_ID = "black_customer_list_plugin";
	private $userAttributeDao;
	
	function __construct(){
		$this->userAttributeDao = SOY2DAOFactory::create("user.SOYShop_UserAttributeDAO");
	}
	
	function getAttribute($userId){
		try{
			return $this->userAttributeDao->get($userId, self::PLUGIN_ID);
		}catch(Exception $e){
			return new SOYShop_UserAttribute();
		}
	}
	
	function save($userId, $value){
		$attr = self::getAttribute($userId);
		$attr->setValue($value);
			
		//新規登録
		if(is_null($attr->getUserId())){
			$attr->setUserId($userId);
			$attr->setFieldId(self::PLUGIN_ID);
				
			try{
				$this->userAttributeDao->insert($attr);
			}catch(Exception $e){
				var_dump($e);
			}
		}else{
			try{
				$this->userAttributeDao->update($attr);
			}catch(Exception $e){
				var_dump($e);
			}
		}
	}
	
	function getUserIdByOrderId($orderId){
		try{
			return SOY2DAOFactory::create("order.SOYShop_OrderDAO")->getById($orderId)->getUserId();
		}catch(Exception $e){
			return null;
		}
	}
}
?>