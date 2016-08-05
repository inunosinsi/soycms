<?php

class IndexPage extends WebPage{
	
	private $update;
	
	function doPost(){
		
		if(soy2_check_token()){
			$dao = SOY2DAOFactory::create("SOYList_ListDAO");
			
			$array = (isset($_POST["list"]) && is_array($_POST["list"])) ? $_POST["list"] : array();
			
			if(count($array) > 0 || (isset($_POST["accordion"]) && $_POST["accordion"] == 1)){
				$array["updateDate"] = time();
				if(isset($_POST["accordion"])){
					$array["accordion"] = 1;
				}else{
					$array["accordion"] = 0;
				}
				
				$list = new SOYList_List();
				$list->setConfig($array);
				
				try{
					$dao->update($list);
				}catch(Exception $e){
					
				}
				
				//ソート順の登録
				$itemDao = SOY2DAOFactory::create("SOYList_ItemDAO");
				$values = $_POST["Item"];
				if(count($values) > 0){
					foreach($values as $key => $value){
						$sort = (int)mb_convert_kana($value,"a");
						if($sort===0)$sort=100000;
						
						try{
							$item = $itemDao->getById($key);
						}catch(Exception $e){
							continue;
						}
						
						$item->setSort($sort);
						
						try{
							$itemDao->update($item);
						}catch(Exception $e){
							var_dump($e);
						}
					}
				}
			}

			CMSApplication::jump("List");	
		}
	}

	function __construct() {
		
		$checked = $this->getList();
		
		if(is_null($checked))$checked = array();
		
		//アコーディオンの設定を取得する。
		$accordion = (isset($checked["accordion"])) ? $checked["accordion"] : 0;
		
		//更新日時を取得する。
		$this->update = (isset($checked["updateDate"])) ? $checked["updateDate"] : null;
		
		//配列で商品IDに関わるもの以外の値をすべて削除
		$checked = $this->convertList($checked);
		
		WebPage::WebPage();
		
		$categoryDao = SOY2DAOFactory::create("SOYList_CategoryDAO");
		  	
		try{
			$categories = $categoryDao->get();
		}catch(Exception $e){
			$categories = array();
		}
		
		$this->createAdd("form","HTMLForm");
		
		$this->createAdd("category_list","CategoryList",array(
			"list" => $categories,
			"checked" => $checked,
			"accordion" => $accordion,
			"itemDao" => SOY2DAOFactory::create("SOYList_ItemDAO")
		));
		
		$this->createAdd("accordion","HTMLCheckbox",array(
			"name" => "accordion",
			"value" => 1,
			"selected" => $accordion == 1
		));
	}
	
	function getList(){
		$listDao = SOY2DAOFactory::create("SOYList_ListDAO");
		$array = $listDao->get();
 		$list = $array->getConfig();
 		return $list;
	}
	
	function convertList($array){
		foreach($array as $key => $value){
			if(!is_numeric($key)){
				unset($array[$key]);
			}
		}
		return $array;
	}
}

class CategoryList extends HTMLList{
	
	private $itemDao;
	private $checked;
	private $accordion;
	
	protected function populateItem($entity){
		
		$this->createAdd("is_accordion","HTMLModel",array(
			"visible" => $this->accordion == 1
		));
		
		$this->createAdd("category_link_name","HTMLModel",array(
			"name" => "#".$entity->getName()
		));
		
		$this->createAdd("category_name","HTMLLabel",array(
			"text" => $entity->getName()
		));
		
		$items = $this->getItemList($entity->getId());
		
		$this->createAdd("item_list","ItemList",array(
			"list" => $items,
			"checked" => $this->checked
		));
		
		$this->createAdd("no_list","HTMLModel",array(
			"visible" => count($items) == 0
		));
	}
	
	function getItemList($category){		
		try{
			$items = $this->itemDao->getByCategory($category);
		}catch(Exception $e){
			$items = array();
		}
		
		return $items;
	}
	
	function setChecked($checked){
		$this->checked = $checked;
	}
	
	function setAccordion($accordion){
		$this->accordion = $accordion;
	}
	function setItemDao($itemDao){
		$this->itemDao = $itemDao;
	}
}

class ItemList extends HTMLList{
	
	private $checked;
	
	protected function populateItem($entity){
		
		$flag = false;
		
		if(array_key_exists($entity->getId(),array_flip($this->checked))){
			$flag = true;
		}
		
		$this->createAdd("item_id","HTMLCheckbox",array(
			"name" => "list[]",
			"value" => $entity->getId(),
			"selected" => $flag == true
		));
		
		$this->createAdd("item_name","HTMLLabel",array(
			"text" => $entity->getName()
		));
		
		$this->createAdd("item_category","HTMLLabel",array(
			"text" => $entity->getCategory()
		));
		
		$this->createAdd("item_price","HTMLLabel",array(
			"text" => number_format($entity->getPrice())
		));
		
		$this->createAdd("item_description","HTMLLabel",array(
			"text" => $entity->getDescription()
		));
		
		$this->createAdd("item_sort","HTMLInput",array(
			"name" => "Item[".$entity->getId()."]",
			"value" => ($entity->getSort() < 100000) ? $entity->getSort() : "",
			"style" => "text-align:right;ime-mode:inactive;"
		));
	}
	
	function setChecked($checked){
		$this->checked = $checked;
	}
}
?>