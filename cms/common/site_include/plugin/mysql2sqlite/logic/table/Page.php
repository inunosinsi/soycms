<?php

function registerPage($stmt){
	$dao = SOY2DAOFactory::create("cms.PageDAO");

	$i = 0;
	for(;;){
		$dao->setOrder("id ASC");
		$dao->setLimit(RECORD_LIMIT);
		$dao->setOffset(RECORD_LIMIT * $i++);
		try{
			$pages = $dao->get();
			if(!count($pages)) return;
		}catch(Exception $e){
			return;
		}

		foreach($pages as $page){
			$stmt->execute(array(
				":id" => $page->getId(),
				":title" => $page->getTitle(),
				":template" => $page->getTemplate(),
				":uri" => $page->getUri(),
				":page_type" => $page->getPageType(),
				":page_config" => $page->getPageConfig(),
				":openPeriodStart" => $page->getOpenPeriodStart(),
				":openPeriodEnd" => $page->getOpenPeriodEnd(),
				":isPublished" => $page->getIsPublished(),
				":isTrash" => $page->getIsTrash(),
				":parent_page_id" => $page->getParentPageId(),
				":udate" => $page->getUdate(),
				":icon" => $page->getIcon()
			));
		}
	}
}
