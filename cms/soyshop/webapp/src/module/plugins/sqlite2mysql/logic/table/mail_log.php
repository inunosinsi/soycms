<?php

function register_mail_log($stmt){
	$dao = SOY2DAOFactory::create("logging.SOYShop_MailLogDAO");

	$i = 0;
	for(;;){
		$dao->setOrder("id ASC");
		$dao->setLimit(RECORD_LIMIT);
		$dao->setOffset(RECORD_LIMIT * $i++);
		try{
			$logs = $dao->get();
			if(!count($logs)) break;
		}catch(Exception $e){
			break;
		}

		foreach($logs as $log){
			$stmt->execute(array(
				":id" => $log->getId(),
				":recipient" => $log->getRecipient(),
				":order_id" => (!is_null($log->getOrderId())) ? (int)$log->getOrderId() : null,
				":user_id" => (!is_null($log->getUserId())) ? (int)$log->getUserId() : null,
				":title" => $log->getTitle(),
				":content" => $log->getContent(),
				":is_success" => (int)$log->getIsSuccess(),
				":send_date" => (!is_null($log->getSendDate())) ? (int)$log->getSendDate() : null
			));
		}
	}
}
