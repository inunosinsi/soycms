<?php

function register_page($stmt){
	$dao = SOY2DAOFactory::create("site.SOYShop_PageDAO");
	//$stmt = self::buildStatememt($sql);

	$i = 0;
	for(;;){
		$dao->setOrder("id ASC");
		$dao->setLimit(RECORD_LIMIT);
		$dao->setOffset(RECORD_LIMIT * $i++);
		try{
			$pages = $dao->get();
			if(!count($pages)) break;
		}catch(Exception $e){
			break;
		}

		foreach($pages as $page){
			$stmt->execute(array(
				":id" => $page->getId(),
				":uri" => $page->getUri(),
				":name" => $page->getName(),
				":type" => $page->getType(),
				":template" => $page->getTemplate(),
				":config" => $page->getConfig(),
				":create_date" => $page->getCreateDate(),
				":update_date" => $page->getUpdateDate()
			));
		}
	}
}
