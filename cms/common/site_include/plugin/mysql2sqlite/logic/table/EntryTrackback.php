<?php

function registerEntryTrackback($stmt){
	$dao = SOY2DAOFactory::create("cms.EntryTrackbackDAO");

	$i = 0;
	for(;;){
		$dao->setOrder("id ASC");
		$dao->setLimit(RECORD_LIMIT);
		$dao->setOffset(RECORD_LIMIT * $i++);
		try{
			$trackbacks = $dao->get();
			if(!count($trackbacks)) break;
		}catch(Exception $e){
			break;
		}

		foreach($trackbacks as $trackback){
			$stmt->execute(array(
				":id" => $trackback->getId(),
				":entry_id" => $trackback->getEntryId(),
				":title" => $trackback->getTitle(),
				":url" => $trackback->getUrl(),
				":blog_name" => $trackback->getBlogName(),
				":excerpt" => $trackback->getExcerpt(),
				":submitdate" => $trackback->getSubmitDate(),
				":certification" => $trackback->getCertification()
			));
		}
	}
}
