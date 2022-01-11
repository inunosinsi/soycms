<?php

class DetailCategoryInfoCustomField extends SOYShopItemCustomFieldBase{

	/**
	 * onOutput
	 */
	function onOutput($htmlObj, SOYShop_Item $item){
		$category = soyshop_get_category_object((int)$item->getCategory());
		$categoryName = $category->getName();					//カテゴリ名の取得
		$categoryAlias = $category->getAlias();					//カテゴリエイリアスの取得
		$categoryTree = self::_getCategoryRelation($category);	//カテゴリツリーの取得

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
		if(is_numeric($category->getId())){
			$attrDao = soyshop_get_hash_table_dao("category_attribute");

			try{
				$array = $attrDao->getByCategoryId($category->getId());
			}catch(Exception $e){
				$array = array();
			}

			if(count($array) > 0){
				$list = SOYShop_CategoryAttributeConfig::load();

				foreach($list as $config){
					$value = (isset($array[$config->getFieldId()])) ? (string)$array[$config->getFieldId()]->getValue() : "";

					$htmlObj->addModel($config->getFieldId() . "_visible", array(
						"visible" => (strlen(strip_tags($value)) > 0),
						"soy2prefix" => SOYSHOP_SITE_PREFIX
					));

					switch($config->getType()){

						case "image":
							if(strlen($config->getOutput()) > 0){
								$htmlObj->addModel($config->getFieldId(), array(
									"attr:" . htmlspecialchars((string)$config->getOutput()) => $value,
									"soy2prefix" => SOYSHOP_SITE_PREFIX
								));
							}else{
								$htmlObj->addImage($config->getFieldId(), array(
									"src" => $value,
									"soy2prefix" => SOYSHOP_SITE_PREFIX,
									"visible" => (strlen($value))
								));
							}
							$htmlObj->addLink($config->getFieldId() . "_link", array(
								"link" => $value,
								"soy2prefix" => SOYSHOP_SITE_PREFIX
							));
							break;

						case "textarea":
							if(strlen((string)$config->getOutput()) > 0){
								$htmlObj->addModel($config->getFieldId(), array(
									"attr:" . htmlspecialchars($config->getOutput()) => nl2br($value),
									"soy2prefix" => SOYSHOP_SITE_PREFIX
								));
							}else{
								$htmlObj->addLabel($config->getFieldId(), array(
									"html" => nl2br($value),
									"soy2prefix" => SOYSHOP_SITE_PREFIX
								));
							}
							break;

						default:
							if(strlen((string)$config->getOutput()) > 0){
								$htmlObj->addModel($config->getFieldId(), array(
									"attr:" . htmlspecialchars($config->getOutput()) => $value,
									"soy2prefix" => SOYSHOP_SITE_PREFIX
								));
							}else{
								$htmlObj->addLabel($config->getFieldId(), array(
									"html" => $value,
									"soy2prefix" => SOYSHOP_SITE_PREFIX
								));
							}
					}
				}
			}
		}
	}

	private function _getCategoryRelation(SOYShop_Category $category){
		$array = array();
		$text = "";

		try{
			if(isset($category)){
				$array[] = $category->getName();
				if(!is_null($category->getParent())){
					$parent = soyshop_get_category_object($category->getParent());
					$array[] = $parent->getName();
					if(!is_null($parent->getParent())){
						$grandParent = soyshop_get_category_object($parent->getParent());
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
}

SOYShopPlugin::extension("soyshop.item.customfield", "detail_category_info", "DetailCategoryInfoCustomField");
