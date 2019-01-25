<?php

function register_favorite_item($stmt){
	SOY2::import("module.plugins.common_favorite_item.domain.SOYShop_FavoriteItemDAO");
	$dao = SOY2DAOFactory::create("SOYShop_FavoriteItemDAO");

	$i = 0;
	for(;;){
		$dao->setOrder("id ASC");
		$dao->setLimit(RECORD_LIMIT);
		$dao->setOffset(RECORD_LIMIT * $i++);
		try{
			$favs = $dao->get();
			if(!count($favs)) break;
		}catch(Exception $e){
			break;
		}

		foreach($favs as $fav){
			$stmt->execute(array(
				":id" => $fav->getId(),
				":item_id" => $fav->getItemId(),
				":user_id" => $fav->getUserId(),
				":purchased" => $fav->getPurchased(),
				":create_date" => $fav->getCreateDate(),
				":update_date" => $fav->getUpdateDate(),
			));
		}
	}
}
