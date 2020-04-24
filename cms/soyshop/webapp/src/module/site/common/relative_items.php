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

	try{
		$dao = SOY2DAOFactory::create("shop.SOYShop_ItemAttributeDAO");
		$attr = $dao->get($item->getId(), "_relative_items");
		$codes = soy2_unserialize($attr->getValue());
		if(!is_array($codes)) $codes = array();
	}catch(Exception $e){
		$codes = array();
	}

    $items = array();

    if(count($codes)){
        
        SOY2::import("module.plugins.common_relative_item.util.RelativeItemUtil");
		$config = RelativeItemUtil::getConfig();
		
		$sql = "SELECT * FROM soyshop_item ".
             "WHERE item_code IN (\"". implode("\",\"", $codes) . "\") ".
             "AND item_is_open != 0 ".
             "AND is_disabled != 1 ";
				
		//ソートの設定
		if(isset($config["defaultSort"])){
            //ランダム表示
            if($config["defaultSort"] == "random"){
                if(SOY2DAOConfig::type() == "mysql"){
                    $sql .= "ORDER BY Rand() ";
                }else{
                    $sql .= "ORDER BY Random() ";
                }
            }else{
                //ランダム以外
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
                $res = $itemDao->executeQuery($sql, array());
            }catch(Exception $e){
                $res = array();
            }

            if(count($res)) foreach($res as $v){
                    $items[] = $itemDao->getObject($v);
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