<?php
/*
 * soyshop.site.beforeoutput.php
 * Created: 2010/03/11
 */

class CommonCategoryCustomfieldBeforeOutput extends SOYShopSiteBeforeOutputAction{

	function beforeOutput($page){

		$obj = $page->getPageObject();

		//カートページとマイページでは読み込まない
		if(get_class($obj) != "SOYShop_Page"){
			return;
		}

		if($obj->getType() == SOYShop_Page::TYPE_COMPLEX || $obj->getType() == SOYShop_Page::TYPE_FREE || $obj->getType() == SOYShop_Page::TYPE_SEARCH){
			return;
		}


		//商品一覧ページ以外では動作しない
		switch($obj->getType()){
			case SOYShop_Page::TYPE_LIST:
				$current = $obj->getObject()->getCurrentCategory();
				if(is_null($current)){
					return;
				}
				$category = $current;
				break;
			case SOYShop_Page::TYPE_DETAIL:
				$current = $obj->getObject()->getCurrentItem();
				if(is_null($current->getCategory())) return;
				if(is_numeric($current->getCategory())){
					$parent = $this->getItem($current->getCategory());
					$category = $this->getCategory($parent->getCategory());
				}else{
					$category = $this->getCategory($current->getCategory());
				}
				break;
		}

		$page->addLabel("current_category_name", array(
			"soy2prefix" => SOYSHOP_SITE_PREFIX,
			"text" => $category->getName()
		));


		$dao = SOY2DAOFactory::create("shop.SOYShop_CategoryAttributeDAO");
		try{
			$array = $dao->getByCategoryId($category->getId());
		}catch(Exception $e){
			return;
		}

		$list = SOYShop_CategoryAttributeConfig::load();

		foreach($list as $config){
			$value = (isset($array[$config->getFieldId()])) ? $array[$config->getFieldId()]->getValue() : null;
			$value2 = (isset($array[$config->getFieldId()])) ? $array[$config->getFieldId()]->getValue2() : null;

			$page->addModel($config->getFieldId() . "_visible", array(
				"visible" => (strlen(strip_tags($value)) > 0),
				"soy2prefix" => SOYSHOP_SITE_PREFIX
			));

			switch($config->getType()){

				case "image":
					if(strlen($config->getOutput()) > 0){
						$page->addModel($config->getFieldId(), array(
							"attr:" . htmlspecialchars($config->getOutput()) => $value,
							"soy2prefix" => SOYSHOP_SITE_PREFIX
						));
					}else{
						//imgタグにalt属性を追加するか？
						if(isset($value2) && strlen($value2) > 0){
							$page->addImage($config->getFieldId(), array(
								"src" => $value,
								"attr:alt" => $value2,
								"soy2prefix" => SOYSHOP_SITE_PREFIX,
								"visible" => ($value) ? true : false
							));
						}else{
							$page->addImage($config->getFieldId(), array(
								"src" => $value,
								"soy2prefix" => SOYSHOP_SITE_PREFIX,
								"visible" => ($value)?true:false
							));
						}
					}
					$page->addLink($config->getFieldId() . "_link", array(
						"link" => $value,
						"soy2prefix" => SOYSHOP_SITE_PREFIX
					));
					
					$page->addLabel($config->getFieldId() . "_text", array(
						"text" => $value,
						"soy2prefix" => SOYSHOP_SITE_PREFIX
					));
					break;
					
				case "textarea":
					if(strlen($config->getOutput()) > 0){
						$page->addModel($config->getFieldId(), array(
							"attr:" . htmlspecialchars($config->getOutput()) => nl2br($value),
							"soy2prefix" => SOYSHOP_SITE_PREFIX
						));
					}else{
						$page->addLabel($config->getFieldId(), array(
							"html" => nl2br($value),
							"soy2prefix" => SOYSHOP_SITE_PREFIX
						));
					}
					break;
				
				case "link":
					if(strlen($config->getOutput()) > 0){
						$page->addModel($config->getFieldId(), array(
							"attr:" . htmlspecialchars($config->getOutput()) => $value,
							"soy2prefix" => SOYSHOP_SITE_PREFIX
						));
					}else{
						$page->addLink($config->getFieldId(), array(
							"link" => $value,
							"soy2prefix" => SOYSHOP_SITE_PREFIX
						));
					}
					
					$page->addLabel($config->getFieldId() . "_text", array(
						"text" => $value,
						"soy2prefix" => SOYSHOP_SITE_PREFIX
					));
					break;

				default:
					if(strlen($config->getOutput()) > 0){
						if($config->getOutput() == "href" && $config->getType() != "link"){
							$page->addLink($config->getFieldId(), array(
								"link" => $value,
								"soy2prefix" => SOYSHOP_SITE_PREFIX
							));
						}else{
							$page->addModel($config->getFieldId(), array(
								"attr:" . htmlspecialchars($config->getOutput()) => $value,
								"soy2prefix" => SOYSHOP_SITE_PREFIX
							));
						}
					}else{
						$page->addLabel($config->getFieldId(), array(
							"html" => $value,
							"soy2prefix" => SOYSHOP_SITE_PREFIX
						));
					}
			}
		}
	}

	function getItem($itemId){
		$itemDao = SOY2DAOFactory::create("shop.SOYShop_ItemDAO");
		try{
			$item = $itemDao->getById($itemId);
		}catch(Exception $e){
			$item = new SOYShop_Item();
		}

		return $item;
	}
	function getCategory($categoryId){
		$categoryDao = SOY2DAOFactory::create("shop.SOYShop_CategoryDAO");
		try{
			$category = $categoryDao->getById($categoryId);
		}catch(Exception $e){
			$category = new SOYShop_Category();
		}
		return $category;
	}
}
SOYShopPlugin::extension("soyshop.site.beforeoutput","common_category_customfield","CommonCategoryCustomfieldBeforeOutput");