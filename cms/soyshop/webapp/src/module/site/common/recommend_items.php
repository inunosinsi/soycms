<?php
function soyshop_recommend_items($html, $htmlObj){

	$obj = $htmlObj->create("soyshop_recommend_items", "HTMLTemplatePage", array(
		"arguments" => array("soyshop_recommend_items", $html)
	));

	$items = array();
	$isCallCustomfield = true;

	if(SOYShopPluginUtil::checkIsActive("common_recommend_item")){
		//標準の出力設定は10にしておく
		$lim = 10;
		if(preg_match('/cms:count=\"([\d]*)\"/', $html, $tmp)){
			if(isset($tmp[1]) && is_numeric($tmp[1])) $lim = (int)$tmp[1];
		}

		// cms:customfield="off" の場合はカスタムフィールドの拡張ポイントを実行しない
		if(preg_match('/cms:customfield=\"(.*)\"/', $html, $tmp)){
			if(isset($tmp[1]) && $tmp[1] == "off") $isCallCustomfield = false;
		}

		$ids = SOYShop_DataSets::get("item.recommend_items", array());
		if(count($ids)){
			SOY2::import("module.plugins.common_recommend_item.util.RecommendItemUtil");
			$cnf = RecommendItemUtil::getConfig();

			$sql = "SELECT * FROM soyshop_item ".
				"WHERE id IN (". implode(",", $ids) . ") ".
				"AND item_is_open != 0 ".
				"AND is_disabled != 1 ";

			//ソートの設定
			if(isset($cnf["defaultSort"])){
				//ランダム表示
				if($cnf["defaultSort"] == "random"){
					if(SOY2DAOConfig::type() == "mysql"){
						$sql .= "ORDER BY Rand() ";
					}else{
						$sql .= "ORDER BY Random() ";
					}
				}else{
					//ランダム以外
					switch($cnf["defaultSort"]){
					case "cdate":
						$key = "create_date";
						break;
					case "udate":
						$key = "update_date";
						break;
					default:
						$key = "item_" . $cnf["defaultSort"];
					}
					$sql .= "ORDER BY " . $key . " ";
					$sql .= ($cnf["isReverse"] == 1) ? "ASC " : " DESC ";
				}
			}

			$sql .= "LIMIT " . $lim;

			$dao = soyshop_get_hash_table_dao("item");

			try{
				$res = $dao->executeQuery($sql, array());
			}catch(Exception $e){
				$res = array();
			}

			if(count($res)){
				foreach($res as $map){
					if(!isset($map["id"])) continue;
					$items[$map["id"]] = soyshop_set_item_object($dao->getObject($map));
				}
			}
		}
	}

	SOY2::import("base.site.classes.SOYShop_ItemListComponent");
	$obj->createAdd("recommend_item_list", "SOYShop_ItemListComponent", array(
		"list" => $items,
		"soy2prefix" => "block",
		"isCallCustomfield" => $isCallCustomfield
	));

	// cms:idは互換性維持のため残しておく→廃止
	// $obj->createAdd("recommend_item_list", "SOYShop_ItemListComponent", array(
	// 	"list" => $items,
	// 	"soy2prefix" => SOYSHOP_SITE_PREFIX,
	// ));

	//商品があるときだけ表示
	if(count($items) > 0){
		$obj->display();
	}else{
		ob_start();
		$obj->display();
		ob_end_clean();
	}
}
