<?php

class UserLogic extends SOY2LogicBase{

    function remove($userId){
    	$userDao = SOY2DAOFactory::create("SOYMailUserDAO");

    	try{
    		$user = $userDao->getById($userId);
    	}catch(Exception $e){
    		$user = new SOYMailUser();
    	}

    	$mailAddress = $user->getMailAddress();

    	//ユーザが存在していた場合
    	if(isset($mailAddress)){
    		for($i=0;$i<=100;$i++){
    			try{
    				$checkUser = $userDao->getIdByEmail($mailAddress."_delete_".$i);
    			}catch(Exception $e){
    				$deleteAddress = $mailAddress."_delete_".$i;
    				break;
    			}
    		}
    		$user->setName("(削除)".$user->getName());
    		$user->setMailAddress($deleteAddress);
    		$user->setIsDisabled(SOYMailUser::USER_IS_DISABLED);
    		$user->setUpdateDate(time());

    		try{
    			$userDao->update($user);
    			$res = true;
    		}catch(Exception $e){
    			$res = false;
    		}
    	}
    }
}
?>