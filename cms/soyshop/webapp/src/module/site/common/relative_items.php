<?php
function soyshop_relative_items(string $html, HTMLPage $htmlObj){
	$obj = $htmlObj->create("soyshop_relative_items", "HTMLTemplatePage", array(
		"arguments" => array("soyshop_relative_items", $html)
	));

	$items = array();
	$isCallCustomfield = true;

	if(SOYShopPluginUtil::checkIsActive("common_relative_item")){
		//標準の出力設定は10にしておく
		$lim = 10;
		if(preg_match('/cms:count=\"([\d]*)\"/', $html, $tmp)){
			if(isset($tmp[1]) && is_numeric($tmp[1])) $lim = (int)$tmp[1];
		}

		// cms:customfield="off" の場合はカスタムフィールドの拡張ポイントを実行しない
		if(preg_match('/cms:customfield=\"(.*)\"/', $html, $tmp)){
			if(isset($tmp[1]) && $tmp[1] == "off") $isCallCustomfield = false;
		}

		//詳細ページを開いている時
		if(method_exists($htmlObj, "getItem")){
			$item = $htmlObj->getItem();
			//商品詳細表示プラグインでも関連商品を取得できるようにした
		}else{
			$alias = substr($_SERVER["REDIRECT_URL"], strrpos($_SERVER["REDIRECT_URL"], "/") + 1);
			$item = soyshop_get_item_object_by_alias($alias);
		}

		SOY2::import("module.plugins.common_relative_item.util.RelativeItemUtil");
		$codes = (is_numeric($item->getId())) ? RelativeItemUtil::getCodesByItemId($item->getId()) : array();
		if(count($codes)){
			SOY2::import("module.plugins.common_relative_item.util.RelativeItemUtil");
			$cnf = RelativeItemUtil::getConfig();

			$sql = "SELECT * FROM soyshop_item ".
				"WHERE item_code IN (\"". implode("\",\"", $codes) . "\") ".
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
					case "add":	//追加順
						$key = null;
						break;
					default:
						$key = "item_" . $cnf["defaultSort"];
					}
					if(is_string($key) && strlen($key)){
						$sql .= "ORDER BY " . $key . " ";
						$sql .= ($cnf["isReverse"] == 1) ? "ASC " : "DESC ";
					}
				}

				$sql .= "LIMIT " . $lim;

				$dao = soyshop_get_hash_table_dao("item");

				try{
					$res = $dao->executeQuery($sql, array());
				}catch(Exception $e){
					$res = array();
				}

				if(count($res)) {
					foreach($res as $v){
						$items[] = soyshop_set_item_object($dao->getObject($v));
					}
				}

				// 管理画面で追加した順
				if($cnf["defaultSort"] == "add"){
					$tmps = array();
					foreach($codes as $code){
						foreach($items as $item){
							if($item->getCode() == $code){
								$tmps[] = $item;
								continue;
							}
						}
					}
					$items = $tmps;
					unset($tmps);
				}
			}
		}
	}

	SOY2::import("base.site.classes.SOYShop_ItemListComponent");
	$obj->createAdd("relative_item_list", "SOYShop_ItemListComponent", array(
		"list" => $items,
		"soy2prefix" => "block",
		"isCallCustomfield" => $isCallCustomfield
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
