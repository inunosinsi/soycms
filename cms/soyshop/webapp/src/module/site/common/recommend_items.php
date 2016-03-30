<?php
function soyshop_recommend_items($html, $htmlObj){

	$obj = $htmlObj->create("soyshop_recommend_items", "HTMLTemplatePage", array(
		"arguments" => array("soyshop_recommend_items", $html)
	));

	$dao = SOY2DAOFactory::create("shop.SOYShop_ItemDAO");
	$ids = SOYShop_DataSets::get("item.recommend_items", array());
	$items = array();
	
	if(count($ids)){
		
		SOY2::import("module.plugins.common_recommend_item.util.RecommendItemUtil");
		$config = RecommendItemUtil::getConfig();
		
		$sql = "SELECT * FROM soyshop_item ".
				"WHERE id IN (". implode(",", $ids) . ") ".
				"AND item_is_open != 0 ".
				"AND is_disabled != 1 ";
				
		//ソートの設定
		
		if(isset($config["defaultSort"])){
			switch($config["defaultSort"]){
				case "cdate":
					$key = "create_date";
					break;
				case "udate":
					$key = "update_date";
					break;
				default:
					$key = "item_" . $config["defaultSort"];
			}
			$sql .= "ORDER BY " . $key . " ";
			$sql .= ($config["isReverse"] == 1) ? "ASC" : "DESC";
		}
		
		
		
		try{
			$res = $dao->executeQuery($sql, array());
		}catch(Exception $e){
			$res = array();
		}
		
		if(count($res)){
			foreach($res as $map){
				if(!isset($map["id"])) continue;
				$items[$map["id"]] = $dao->getObject($map);
			}
		}
	}

	$obj->createAdd("recommend_item_list", "SOYShop_ItemListComponent", array(
		"list" => $items,
		"soy2prefix" => SOYSHOP_SITE_PREFIX,//cms:idは互換性維持のため残しておく
	));
	$obj->createAdd("recommend_item_list", "SOYShop_ItemListComponent", array(
		"list" => $items,
		"soy2prefix" => "block",
	));

	//商品があるときだけ表示
	if(count($items) > 0){
		$obj->display();
	}else{
		ob_start();
		$obj->display();
		ob_end_clean();
	}
}
?>