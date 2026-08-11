<?php

class ChildItemLogic extends SOY2LogicBase{

	function __construct(){}

	function getChildItem(int $parentId, array $keys){
		$sql = "SELECT * FROM soyshop_item ".
				"WHERE item_type = :parentId ".
				"AND is_disabled != " . SOYShop_Item::IS_DISABLED . " ".
				"AND item_name LIKE :name ";

		$binds[":parentId"] = $parentId;

		$q = "";
		foreach($keys as $key){
			if(!strlen($key)) continue;
			//POSTで+は許可されていないので、POST時に一度変換して、ここで再度戻す
			if(strpos($key, "itm_std_plus")) $key = str_replace("itm_std_plus", "+", $key);

			//POSTで&は許可されていないので、POST時に一度変換して、ここで再度戻す
			if(strpos($key, "itm_std_and")) $key = str_replace("itm_std_and", "&", $key);
			$q .= " " . $key;
		}

		$binds[":name"] = "%" . $q . "%";

		$sql .= "ORDER BY id ASC ";
		$sql .= "LIMIT 1";

		try{
			$res = soyshop_get_hash_table_dao("item")->executeQuery($sql, $binds);
		}catch(Exception $e){
			$res = array();
		}

		return (isset($res[0]) && isset($res[0]["id"])) ? soyshop_get_hash_table_dao("item")->getObject($res[0]) : new SOYShop_Item();
	}

	function setChildItemName(SOYShop_Item $child, SOYShop_Item $parent, array $keys){
		//名前のセット
		$pname = $parent->getName();
		foreach($keys as $key){
			$pname .= " " . trim($key);
		}
		$child->setName($pname);

		return $child;
	}

	function setParentInfo(SOYShop_Item $child, SOYShop_Item $parent){

		//商品コードのセット
		$pcode = $parent->getCode();
		$postfix = 0;
		for(;;){
			$tmp = soyshop_get_item_object_by_code($pcode . "_" . $postfix);
			if(!is_numeric($tmp->getId())){
				//念の為にエイリアスがないことも確認
				try{
					soyshop_get_hash_table_dao("item")->getByAlias($pcode . "_" . $postfix . ".html");
				}catch(Exception $e){
					break;
				}
			}
			$postfix++;
		}
		$child->setCode($pcode . "_" . $postfix);
		$child->setType($parent->getId());	//親商品の登録
		$child->setIsOpen(SOYShop_Item::IS_OPEN);	//商品は常にopenにしておく
		return $child;
	}
}
