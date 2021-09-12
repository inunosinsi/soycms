<?php
function soyshop_relative_items($html, $htmlObj){

	$itemDao = SOY2DAOFactory::create("shop.SOYShop_ItemDAO");

	//詳細ページを開いている時
	if(method_exists($htmlObj, "getItem")){
		$item = $htmlObj->getItem();
        //商品詳細表示プラグインでも関連商品を取得できるようにした
	}else{
		$alias = substr($_SERVER["REDIRECT_URL"], strrpos($_SERVER["REDIRECT_URL"], "/") + 1);
		try{
			$item = $itemDao->getByAlias($alias);
		}catch(Exception $e){
			$item = new SOYShop_Item();
		}
	}

	SOY2::import("module.plugins.common_relative_item.util.RelativeItemUtil");
	$codes = RelativeItemUtil::getCodesByItemId($item->getId());

    $items = array();

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
				if(strlen($key)){
					$sql .= "ORDER BY " . $key . " ";
	                $sql .= ($cnf["isReverse"] == 1) ? "ASC" : "DESC";
				}
            }

            try{
                $res = $itemDao->executeQuery($sql, array());
            }catch(Exception $e){
                $res = array();
            }

            if(count($res)) foreach($res as $v){
                $items[] = $itemDao->getObject($v);
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

	$obj = $htmlObj->create("soyshop_relative_items", "HTMLTemplatePage", array(
		"arguments" => array("soyshop_relative_items", $html)
	));

	$obj->createAdd("relative_item_list", "SOYShop_ItemListComponent", array(
		"list" => $items,
		"soy2prefix" => SOYSHOP_SITE_PREFIX,//cms:idは互換性維持のため残しておく
	));
	$obj->createAdd("relative_item_list", "SOYShop_ItemListComponent", array(
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
