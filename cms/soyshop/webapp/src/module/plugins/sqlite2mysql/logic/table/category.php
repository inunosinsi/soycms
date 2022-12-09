<?php

function register_category($stmt){
	$dao = SOY2DAOFactory::create("shop.SOYShop_CategoryDAO");

	$i = 0;
	for(;;){
		$dao->setOrder("id ASC");
		$dao->setLimit(RECORD_LIMIT);
		$dao->setOffset(RECORD_LIMIT * $i++);
		try{
			$categories = $dao->get();
			if(!count($categories)) break;
		}catch(Exception $e){
			break;
		}

		foreach($categories as $category){
			$stmt->execute(array(
				":id" => $category->getId(),
				":category_name" => $category->getName(),
				":category_alias" => $category->getAlias(),
				":category_order" => $category->getOrder(),
				":category_parent" => $category->getParent(),
				":category_config" => $category->getConfig(),
				":category_is_open" => $category->getIsOpen()
			));
		}
	}
}
