<?php

function registerEntry($stmt){
	$dao = SOY2DAOFactory::create("cms.EntryDAO");

	$i = 0;
	for(;;){
		try{
			$dao->setOrder("id ASC");
			$dao->setLimit(RECORD_LIMIT);
			$dao->setOffset(RECORD_LIMIT * $i++);
			$entries = $dao->get();
			if(!count($entries)) break;
		}catch(Exception $e){
			break;
		}

		foreach($entries as $entry){
			$stmt->execute(array(
				":id" => $entry->getId(),
				":title" => $entry->getTitle(),
				":alias" => $entry->getAlias(),
				":content" => $entry->getContent(),
				":more" => $entry->getMore(),
				":cdate" => $entry->getCdate(),
				":udate" => $entry->getUdate(),
				":description" => $entry->getDescription(),
				":openPeriodStart" => $entry->getOpenPeriodStart(),
				":openPeriodEnd" => $entry->getOpenPeriodEnd(),
				":isPublished" => $entry->getIsPublished(),
				":style" => $entry->getStyle(),
				":author" => $entry->getAuthor()
			));
		}
	}
}
