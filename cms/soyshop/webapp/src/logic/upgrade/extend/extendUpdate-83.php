<?php
$userDao = SOY2DAOFactory::create("user.SOYShop_UserDAO");
try{
	$results = $userDao->executeQuery("SELECT id, register_date, update_date FROM soyshop_user WHERE register_date IS NULL");
}catch(Exception $e){
	$results = array();
}
if(count($results)){
	foreach($results as $res){
		if(!isset($res["id"])) continue;
		$updateDate = (isset($res["update_date"]) && is_numeric($res["update_date"])) ? (int)$res["update_date"] : time();
		$registerDate = $updateDate;

		$user = soyshop_get_user_object($res["id"]);
		$user->setRegisterDate($registerDate);
		$user->setUpdateDate($updateDate);

		try{
			$userDao->update($user);
		}catch(Exception $e){
			//
		}
	}
}
unset($userDao);
