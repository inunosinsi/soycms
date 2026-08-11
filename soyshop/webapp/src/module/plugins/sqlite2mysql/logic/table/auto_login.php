<?php

function register_auto_login($stmt){
	$dao = SOY2DAOFactory::create("user.SOYShop_AutoLoginSessionDAO");

	$i = 0;
	for(;;){
		$dao->setOrder("id ASC");
		$dao->setLimit(RECORD_LIMIT);
		$dao->setOffset(RECORD_LIMIT * $i++);
		try{
			$logins = $dao->get();
			if(!count($logins)) break;
		}catch(Exception $e){
			break;
		}

		foreach($logins as $login){
			$stmt->execute(array(
				":id" => $login->getId(),
				":user_id" => $login->getUserId(),
				":session_token" => $login->getToken(),
				":time_limit" => $login->getLimit()
			));
		}
	}
}
