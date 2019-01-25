<?php

function registerEntryComment($stmt){
	$dao = SOY2DAOFactory::create("cms.EntryCommentDAO");

	$i = 0;
	for(;;){
		try{
			$dao->setOrder("id ASC");
			$dao->setLimit(RECORD_LIMIT);
			$dao->setOffset(RECORD_LIMIT * $i++);
			$comments = $dao->get();
			if(!count($comments)) break;
		}catch(Exception $e){
			break;
		}

		foreach($comments as $comment){
			$stmt->execute(array(
				":id" => $comment->getId(),
				":entry_id" => $comment->getEntryId(),
				":title" => $comment->getTitle(),
				":author" => $comment->getAuthor(),
				":body" => $comment->getBody(),
				":is_approved" => $comment->getIsApproved(),
				":mail_address" => $comment->getMailAddress(),
				":url" => $comment->getUrl(),
				":submitdate" => $comment->getSubmitDate()
			));
		}
	}
}
