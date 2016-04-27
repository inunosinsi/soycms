<?php
class ItemListRandom extends SOYShopItemListBase{

	/**
	 * @return string
	 */
	function getLabel(){
		return "ItemListRandom";
	}
	
	/**
	 * @return array
	 */
	function getItems($pageObj,$offset,$limit){
        return self::getRandomItems($limit);
	}
	
	/**
	 * @return number
	 */
	function getTotal($pageObj){
        return count(self::getRandomItems());
	}

    private function getRandomItems($limit = 10){
        static $items;

        if(is_null($items)){
            $itemDao = SOY2DAOFactory::create("shop.SOYShop_ItemDAO");
            $sql = "SELECT * FROM soyshop_item ".
                 "WHERE item_is_open != " . SOYShop_Item::NO_OPEN . " ".
                 "AND is_disabled != " . SOYShop_Item::IS_DISABLED . " ".
                 "AND open_period_start < " . time() . " ".
                 "AND open_period_end > " . time() . " ";

            //Mysql
            if(SOY2DAOConfig::type() == "mysql"){
                $sql .= "ORDER BY Rand() ";
                //SQLite
            }else{
                $sql .= "ORDER BY Random() ";
            }

            $sql .= "LIMIT " . $limit;

            try{
                $res = $itemDao->executeQuery($sql, array());
            }catch(Exception $e){
                $res = array();
            }

            $items = array();
            
            if(!count($res)){
                return $items;
            }

            foreach($res as $v){
                $items[] = $itemDao->getObject($v);
            }
        }

        return $items;
    }
}

SOYShopPlugin::extension("soyshop.item.list", "item_list_random", "ItemListRandom");
