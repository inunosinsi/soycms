<?php
SOY2::import("util.SOYShopPluginUtil");
function soyshop_new_items($html, $htmlObj){

	$obj = $htmlObj->create("soyshop_new_items", "HTMLTemplatePage", array(
		"arguments" => array("soyshop_new_items", $html)
	));

	$items = array();

	if(SOYShopPluginUtil::checkIsActive("common_new_item")){
        //標準の出力設定は10にしておく
        $lim = 10;
        if(preg_match('/cms:count=\"(.*)\"/', $html, $tmp)){
            if(isset($tmp[1]) && is_numeric($tmp[1])) $lim = (int)$tmp[1];
        }

        $c = $lim;

		$itemDao = SOY2DAOFactory::create("shop.SOYShop_ItemDAO");

        $ids = array();

        $sql = "SELECT id FROM soyshop_item ".
             "WHERE item_is_open != " . SOYShop_Item::NO_OPEN . " ".
             "AND is_disabled != " . SOYShop_Item::IS_DISABLED . " ".
             "AND open_period_start < " . time() . " ".
             "AND open_period_end > " . time() . " ".
             "AND (create_date > :edate AND create_date <= :cdate) ";

        //データの取得回数
        SOY2::import("module.plugins.common_new_item.util.NewItemUtil");
        $config = NewItemUtil::getConfig();
        $try = (isset($config["tryCount"])) ? (int)$config["tryCount"] : 1;

        //登録時刻を一ヶ月ずつ前にして商品が登録されているか調べる
        $cdate = time();
        do{
            if($try-- === 0) break;

            try{
                $res = $itemDao->executeQuery($sql, array(":cdate" => $cdate, ":edate" => $cdate - 31*24*60*60));
            }catch(Exception $e){
                $res = array();
            }

            //次回用に更新
            $cdate -= 31*24*60*60;

            if(!count($res)) continue;

            foreach($res as $v){
                if(isset($v["id"]) && is_numeric($v["id"])){
                    $ids[] = (int)$v["id"];
                    if($c > 0) $c--;
                }
            }

        }while($c > 0);

        if(count($ids)){

            //すでに公開に関する諸々の条件は調べてある
            $sql = "SELECT * FROM soyshop_item ".
                 "WHERE id IN (" . implode(",", $ids) . ") ";

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
                    $sql .= ($config["isReverse"] == 1) ? "ASC " : "DESC ";
                }
            }

            $sql .= "LIMIT " . $lim;

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

    $obj->createAdd("new_item_list", "SOYShop_ItemListComponent", array(
		"list" => $items,
		"soy2prefix" => "block"
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
