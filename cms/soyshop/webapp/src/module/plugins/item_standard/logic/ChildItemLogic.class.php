<?php

class ChildItemLogic extends SOY2LogicBase{
	
	private $itemDao;
	
	function __construct(){
		$this->itemDao = SOY2DAOFactory::create("shop.SOYShop_ItemDAO");
	}
	
	function getChildItem($parentId, $keys){
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
			$res = $this->itemDao->executeQuery($sql, $binds);
		}catch(Exception $e){
			$res = array();
		}
		
		if(isset($res[0]) && isset($res[0]["id"])){
			$child = $this->itemDao->getObject($res[0]);
		}else{
			$child = new SOYShop_Item();
		}
		
		return $child;
	}
	
	function setChildItemName($child, $parent, $keys){
		//名前のセット
		$pname = $parent->getName();
		foreach($keys as $key){
			$pname .= " " . trim($key);
		}
		$child->setName($pname);
		
		return $child;
	}
	
	function setParentInfo($child, $parent){
		
		//商品コードのセット
		$pcode = $parent->getCode();
		$postfix = 0;
		for(;;){
			try{
				$this->itemDao->getByCode($pcode . "_" . $postfix);
			}catch(Exception $e){
				//念の為にエイリアスがないことも確認
				try{
					$this->itemDao->getByAlias($pcode . "_" . $postfix . ".html");
				}catch(Exception $e){
					break;
				}
				
			}
			$postfix++;
		}
		$child->setCode($pcode . "_" . $postfix);
		
		//親商品の登録
		$child->setType($parent->getId());
			
		//商品は常にopenにしておく
		$child->setIsOpen(SOYShop_Item::IS_OPEN);
		
		return $child;
	}
}
?>