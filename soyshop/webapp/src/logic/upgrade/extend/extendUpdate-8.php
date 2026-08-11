<?php

$dao = SOY2DAOFactory::create("user.SOYShop_UserDAO");

$userFlag = true;
try{
	$users = $dao->get();
}catch(Exception $e){
	$userFlag = false;
}

if($userFlag){
	foreach($users as $user){
		$array = $user->getAttributesArray();
		$nickname = (isset($array["nickname"])&&strlen($array["nickname"]) > 0) ? $array["nickname"] : "";
		$url = (isset($array["url"])&&strlen($array["url"]) > 0) ? $array["url"] : "";
		
		//どちらの値もない場合
		if(strlen($nickname)==0&&strlen($url) == 0){
			//何もしない
		}else{
			$user->setNickname($nickname);
			$user->setUrl($url);
		}		
		
		$user->setAttributes(null);
		try{
			$dao->update($user);
		}catch(Exception $e){
		}
	}
}

?>