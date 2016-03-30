<?php
SOY2::imports("module.plugins.item_list_category_customfield.util.*");
class ItemListCategoryCustomfieldItemList extends SOYShopItemListBase{

	const MODULE_ID = "item_list_category_customfield";
	
	//結果を入れるためのプロパティ
	private $items = array();
	private $total = null;
	
	
	//searchItems用のparams
	function getParams(){
		SOY2::import("domain.shop.SOYShop_Item");
		$now = time();
		return array(
			"item_is_open" => SOYShop_Item::IS_OPEN,
			"is_disabled" => SOYShop_Item::NO_DISABLED,
//			"open_period_start" => $now,
//			"open_period_end" => $now
		);
	}

	/**
	 * @return string
	 */
	function getLabel(){
		return "ItemListCategoryCustomfield";
	}
	
	/**
	 * @return array
	 */
	function getItems($pageObj, $offset, $limit){
		
		$this->search($pageObj, $offset, $limit);
		
		return $this->items;
	}
	
	/**
	 * @return number
	 */
	function getTotal($pageObj){

		if(!is_null($this->total)){
			$this->search($pageObj);
		}
		
		return $this->total;
	}
	
	function search($pageObj, $offset = null, $limit = null){
		
		$pageId = $pageObj->getPage()->getId();
		$config = ItemListCategoryCustomfieldUtil::getPageConfig(self::MODULE_ID, $pageId);
		
		//カスタムフィールドの値からカテゴリIDを取得
		if(isset($config["useParameter"]) && $config["useParameter"] == 0){
			$fieldValue = (isset($config["fieldValue"])) ? $config["fieldValue"] : "";
			
		//引数からカテゴリIDを取得
		}else{
			$requestUri = $_SERVER["REQUEST_URI"];
			//ページャ分を最初に削除
			if(preg_match('/page-[0-9]{1,}.html/', $requestUri, $tmp)){
				$requestUri = rtrim(preg_replace('/page-[0-9]{1,}.html/', "", $requestUri), "/");
			}
			$uri = substr($requestUri, strpos($requestUri, $pageObj->getPage()->getUri()));
			$fieldValue = trim(str_replace($pageObj->getPage()->getUri(), "", $uri), "/");			
		}
		
		$attributeDao = SOY2DAOFactory::create("shop.SOYShop_CategoryAttributeDAO");
			
		$sql = "SELECT cat.id " .
				"FROM soyshop_category_attribute attr ".
				"INNER JOIN soyshop_category cat ".
				"ON attr.category_id = cat.id ".
				"WHERE attr.category_field_id = :fieldId ".
				"AND attr.category_value = :value ".
				"AND cat.category_is_open = 1";
						
		$binds[":fieldId"] = (isset($config["fieldId"])) ? $config["fieldId"] : "";
		$binds[":value"] = $fieldValue;
			
		try{
			$results = $attributeDao->executeQuery($sql, $binds);
		}catch(Exception $e){
			$this->items = array();
			$this->total = 0;
			return;
		}
		
		$categoryIds = array();
		foreach($results as $result){
			if($result["id"]) $categoryIds[] = $result["id"];
		}
		
		if(count($categoryIds) === 0){
			$this->items = array();
			$this->total = 0;
			return;
		}
		
		//SearchItemUtilの作成。ソート順作成のためlistPageオブジェクトを渡す
		$logic = SOY2Logic::createInstance("logic.shop.item.SearchItemUtil", array(
			"sort" => $pageObj
		));
		
		list($items, $total) = $logic->searchItems($categoryIds, array(), $this->getParams(), $offset, $limit, false);
		$this->items = $items;
		$this->total = $total;
		return;
	}
		
	function doPost(){
		if(isset($_POST["Other"])){
			ItemListCategoryCustomfieldUtil::savePageConfig(self::MODULE_ID, $this->getPageId(), $_POST["Other"]);
		}
	}
	
	function getForm(){
		if($this->isUse()){
			include_once(dirname(__FILE__) . "/form/ItemListCategoryCustomfieldSelectFormPage.class.php");
			$form = SOY2HTMLFactory::createInstance("ItemListCategoryCustomfieldSelectFormPage");
			$form->setConfigObj($this);
			$form->setModuleId(self::MODULE_ID);
			$form->setPageId($this->getPageId());
			$form->execute();
			return $form->getObject();
		}
			
	}
}

SOYShopPlugin::extension("soyshop.item.list", "item_list_category_customfield", "ItemListCategoryCustomfieldItemList");
