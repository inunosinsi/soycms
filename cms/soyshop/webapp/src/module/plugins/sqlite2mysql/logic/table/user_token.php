<?php

function register_user_token($stmt){
	$dao = SOY2DAOFactory::create("user.SOYShop_UserTokenDAO");

	$i = 0;
	for(;;){
		$dao->setOrder("id ASC");
		$dao->setLimit(RECORD_LIMIT);
		$dao->setOffset(RECORD_LIMIT * $i++);
		try{
			$tokens = $dao->get();
			if(!count($tokens)) break;
		}catch(Exception $e){
			break;
		}

		foreach($tokens as $token){
			$stmt->execute(array(
				":user_id" => $token->getUserId(),
				":token" => $token->getToken(),
				":time_limit" => $token->getLimit()
			));
		}
	}
}
