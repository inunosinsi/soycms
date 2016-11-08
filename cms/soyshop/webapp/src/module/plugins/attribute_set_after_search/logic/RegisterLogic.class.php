<?php

class RegisterLogic extends SOY2LogicBase{
	
	private $userDao;
	
	function __construct(){
		$this->userDao = SOY2DAOFactory::create("user.SOYShop_UserDAO");
	}
	
	function setUserAttribute($users){
		
		//設定した内容を全て保存しておく
		$set = (int)$_POST["AttributeSet"];
		$clear = (isset($_POST["AttributeClear"]) && (int)$_POST["AttributeClear"] === 1) ? 1 : 0;
		$value = htmlspecialchars(trim($_POST["AttributeValue"]), ENT_QUOTES, "UTF-8");
		SOYShop_DataSets::put("attribute_set_selected_before", $set);
		SOYShop_DataSets::put("attribute_clear_selected_before", $clear);
		SOYShop_DataSets::put("attribute_value_selected_before", $value);
		
		$userIds = self::getUserIdListByUsers($users);
		
		//削除を選択した場合は最初に削除しておく
		$this->userDao->begin();
		if($clear){
			try{
				$this->userDao->executeUpdateQuery("UPDATE soyshop_user SET attribute" . $set . " = ''");
			}catch(Exception $e){
				//
			}
		}
		
		try{
			$this->userDao->executeUpdateQuery("UPDATE soyshop_user SET attribute" . $set . " = :val WHERE id IN (" . implode(",", $userIds) . ")", array(":val" => $value));
		}catch(Exception $e){
			var_dump($e);
		}
		$this->userDao->commit();
	}
	
	function getUserIdListByUsers($users){
		if(!count($users)) return array();
		
		$list = array();
		foreach($users as $user){
			if(is_null($user->getId())) continue;
			
			$list[] = $user->getId();
		}
		
		return $list;
	}
	
	function getUserIdListByOrders($orders){
		if(!count($orders)) return array();
		
		$list = array();
		foreach($orders as $order){
			if(array_search($order->getUserId(), $list) === false){
				$list[] = (int)$order->getUserId();
			}
		}
		
		if(!count($list)) return array();
		
		sort($list);
		
		try{
			$res = $this->userDao->executeQuery("SELECT * FROM soyshop_user WHERE id IN (" . implode(",", $list) . ")", array());
		}catch(Exception $e){
			$res = array();
		}
		
		if(!count($res)) return array();
		
		$users = array();
		foreach($res as $v){
			$users[] = $this->userDao->getObject($v);
		}
		
		return $users;
	}
}
?>