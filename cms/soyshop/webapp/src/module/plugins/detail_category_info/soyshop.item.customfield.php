<?php
/*
 */
class DetailCategoryInfoCustomField extends SOYShopItemCustomFieldBase{

	/**
	 * onOutput
	 */
	function onOutput($htmlObj, SOYShop_Item $item){
		$categoryId = $item->getCategory();
		
		$categoryName = "";
		$categoryAlias = "";
		$categoryTree = "";
		
		//カテゴリIDが取得出来た時
		if(!is_null($categoryId)){
			$categoryDao = $this->getCategoryDao();
			
			try{
				$category = $categoryDao->getById($categoryId);
			}catch(Exception $e){
				$category = new SOYShop_Category();
			}
			
			//カテゴリ名の取得
			$categoryName = $category->getName();
			
			//カテゴリエイリアスの取得
			$categoryAlias = $category->getAlias();
			
			//カテゴリツリーの取得
			$categoryTree = $this->getCategoryRelation($category);
		}
		
		$htmlObj->addLabel("category_name", array(
			"soy2prefix" => SOYSHOP_SITE_PREFIX,
			"text" => $categoryName
		));
		
		$htmlObj->addLabel("category_alias", array(
			"soy2prefix" => SOYSHOP_SITE_PREFIX,
			"text" => $categoryAlias
		));
		
		$htmlObj->addLabel("category_tree", array(
			"soy2prefix" => SOYSHOP_SITE_PREFIX,
			"text" => $categoryTree
		));
			
		
		//ここからカテゴリのカスタムフィールドの値を取得する
		if(!is_null($categoryId)){
			$categoryAttributeDao = $this->getCategoryAttributeDao();
			
			try{
				$array = $categoryAttributeDao->getByCategoryId($categoryId);
			}catch(Exception $e){
				$array = array();
			}
			
			if(count($array) > 0){
				$list = SOYShop_CategoryAttributeConfig::load();
				
				foreach($list as $config){
					$value = (isset($array[$config->getFieldId()])) ? $array[$config->getFieldId()]->getValue() : null;
		
					$htmlObj->createAdd($config->getFieldId() . "_visible","HTMLModel", array(
						"visible" => (strlen(strip_tags($value)) > 0),
						"soy2prefix" => SOYSHOP_SITE_PREFIX
					));
		
					switch($config->getType()){
		
						case "image":
							if(strlen($config->getOutput()) > 0){
								$htmlObj->createAdd($config->getFieldId(),"HTMLModel", array(
									"attr:" . htmlspecialchars($config->getOutput()) => $value,
									"soy2prefix" => SOYSHOP_SITE_PREFIX
								));
							}else{
								$htmlObj->createAdd($config->getFieldId(),"HTMLImage", array(
									"src" => $value,
									"soy2prefix" => SOYSHOP_SITE_PREFIX,
									"visible" => ($value)?true:false
								));
							}
							$htmlObj->createAdd($config->getFieldId() . "_link","HTMLLink", array(
								"link" => $value,
								"soy2prefix" => SOYSHOP_SITE_PREFIX
							));
							break;
							
						case "textarea":
							if(strlen($config->getOutput()) > 0){
								$htmlObj->createAdd($config->getFieldId(),"HTMLModel", array(
									"attr:" . htmlspecialchars($config->getOutput()) => nl2br($value),
									"soy2prefix" => SOYSHOP_SITE_PREFIX
								));
							}else{
								$htmlObj->createAdd($config->getFieldId(),"HTMLLabel", array(
									"html" => nl2br($value),
									"soy2prefix" => SOYSHOP_SITE_PREFIX
								));
							}
							break;
		
						default:
							if(strlen($config->getOutput()) > 0){
								$htmlObj->createAdd($config->getFieldId(),"HTMLModel", array(
									"attr:" . htmlspecialchars($config->getOutput()) => $value,
									"soy2prefix" => SOYSHOP_SITE_PREFIX
								));
							}else{
								$htmlObj->createAdd($config->getFieldId(),"HTMLLabel", array(
									"html" => $value,
									"soy2prefix" => SOYSHOP_SITE_PREFIX
								));
							}
					}
				}
			}
		}
	}
	
	function getCategoryRelation($category){
		$array = array();
		$text = "";
		
		$categoryDao = $this->getCategoryDao();
		
		try{
			if(isset($category)){
				$array[] = $category->getName();
				if(!is_null($category->getParent())){
					$parent = $categoryDao->getById($category->getParent());
					$array[] = $parent->getName();
					if(!is_null($parent->getParent())){
						$grandParent = $categoryDao->getById($parent->getParent());
						$array[] = $grandParent->getName(); 
					}
				}
			}
		}catch(Exception $e){
			//do nothing
		}
		
		if(array_key_exists(0,$array)){
			$text = implode(" > ",array_reverse($array));
		}
		
		return $text;
	}
	
	function getCategoryDao(){
		static $categoryDao;
		if(!$categoryDao)$categoryDao = SOY2DAOFactory::create("shop.SOYShop_CategoryDAO");
		return $categoryDao;
	}
	
	function getCategoryAttributeDao(){
		static $categoryAttributeDao;
		if(!$categoryAttributeDao)$categoryAttributeDao = SOY2DAOFactory::create("shop.SOYShop_CategoryAttributeDAO");
		return $categoryAttributeDao;
	}
}

SOYShopPlugin::extension("soyshop.item.customfield","detail_category_info","DetailCategoryInfoCustomField");
?>
