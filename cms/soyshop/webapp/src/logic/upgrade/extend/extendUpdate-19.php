<?php
$userDao = SOY2DAOFactory::create("user.SOYShop_UserDAO");

$sql = "SELECT account_id FROM soyshop_user WHERE account_id IS NOT NULL";
try{
	$results = $userDao->executeQuery($sql);
}catch(Exception $e){
	$results = array();
}

foreach($results as $result){
	if(isset($result["account_id"]) && strlen($result["account_id"]) > 0){
		try{
			$user = $userDao->getByAccountId($result["account_id"]);
		}catch(Exception $e){
			continue;
		}
		
		$user->setProfileId($result["account_id"]);
		$user->setAccountId(null);
		try{
			$userDao->update($user);
		}catch(Exception $e){
//			var_dump($e);
			continue;
		}
	}
}
?>