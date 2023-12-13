<?php

class CommonCategoryCustomfieldBeforeOutput extends SOYShopSiteBeforeOutputAction{

	private $fieldTable = array();

	function beforeOutput(WebPage $page){
		if(!is_object($page)) return;

		$obj = $page->getPageObject();
		
		//カートページとマイページでは読み込まない
		if(!is_object($obj) || !$obj instanceof SOYShop_Page) return;

		if(
			$obj->getType() == SOYShop_Page::TYPE_COMPLEX ||
			$obj->getType() == SOYShop_Page::TYPE_FREE ||
			$obj->getType() == SOYShop_Page::TYPE_SEARCH
		) return;

		//商品一覧ページ以外では動作しない
		$name = "";
		switch($obj->getType()){
			case SOYShop_Page::TYPE_LIST:
				$current = $obj->getObject()->getCurrentCategory();

				if(!is_null($current)){
					$category = $current;
					$name = $category->getOpenCategoryName();
				}else{
					//カスタムサーチフィールドを調べる
					SOY2::import("util.SOYShopPluginUtil");
					if(SOYShopPluginUtil::checkIsActive("custom_search_field")){
						$category = new SOYShop_Category();
						$args = $page->getArguments();
						$name = (isset($args[1])) ? trim($args[1]) : "";
					//カスタムサーチフィールド以外は空のカテゴリオブジェクト
					}else{
						$category = new SOYShop_Category();
					}
				}

				break;
			case SOYShop_Page::TYPE_DETAIL:
				$current = $obj->getObject()->getCurrentItem();
				if(is_null($current->getCategory())) return;
				if(is_numeric($current->getCategory())){
					$parent = soyshop_get_item_object($current->getCategory());
					$category = soyshop_get_category_object($parent->getCategoryId());
				}else{
					$category = soyshop_get_category_object($current->getCategoryId());
				}
				$name = $category->getOpenCategoryName();
				break;
		}

		$page->addLabel("current_category_name", array(
			"soy2prefix" => SOYSHOP_SITE_PREFIX,
			"text" => $name
		));

		$dummyItem = soyshop_get_item_object(0);

		soyshop_get_hash_table_dao("category_attribute");
		$list = SOYShop_CategoryAttributeConfig::load(true);
		if(count($list)){
			// カスタムフィールドの値をまとめて取得する
			$this->fieldTable = self::_getFieldValues((int)$category->getId());
			
			foreach($list as $fieldId => $config){
				// 多言語化対策
				$field = self::_getFieldObject($fieldId);
				
				$value = (string)$field->getValue();
				$value2 = (string)$field->getValue2();
				
				//空の時の挙動
				if(!is_null($config->getConfig()) && (is_null($value) || !strlen($value))){
					$fieldConf = $config->getConfig();
					if(isset($fieldConf["hideIfEmpty"]) && !$fieldConf["hideIfEmpty"] && isset($fieldConf["emptyValue"])){
						$value = $fieldConf["emptyValue"];
					}
				}

				$valueLength = strlen(trim(strip_tags($value)));

				$page->addModel($config->getFieldId() . "_visible", array(
					"visible" => ($valueLength > 0),
					"soy2prefix" => SOYSHOP_SITE_PREFIX
				));

				$page->addModel($config->getFieldId() . "_is_not_empty", array(
					"visible" => ($valueLength > 0),
					"soy2prefix" => SOYSHOP_SITE_PREFIX
				));

				$page->addModel($config->getFieldId() . "_is_empty", array(
					"visible" => ($valueLength === 0),
					"soy2prefix" => SOYSHOP_SITE_PREFIX
				));

				switch($config->getType()){
					case "image":
						/**
						 * 隠し機能:携帯自動振り分け、多言語化プラグイン用で画像の配置場所を別で用意する
						 * @ToDo 管理画面でもいじれる様にしたい
						 */
						$value = (is_string($value)) ? soyshop_convert_file_path($value, $dummyItem) : null;

						if(is_string($config->getOutput()) && strlen($config->getOutput()) > 0){
							$page->addModel($config->getFieldId(), array(
								"attr:" . htmlspecialchars($config->getOutput()) => $value,
								"soy2prefix" => SOYSHOP_SITE_PREFIX
							));
						}else{
							$page->addImage($config->getFieldId(), array(
								"src" => $value,
								"attr:alt" => $value2,
								"soy2prefix" => SOYSHOP_SITE_PREFIX,
								"visible" => (isset($value))
							));
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
								"attr:" . htmlspecialchars($config->getOutput()) => (is_string($value)) ? soyshop_customfield_nl2br($value) : "",
								"soy2prefix" => SOYSHOP_SITE_PREFIX
							));
						}else{
							$page->addLabel($config->getFieldId(), array(
								"html" => (is_string($value)) ? soyshop_customfield_nl2br($value) : "",
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
	}

	/**
	 * @param int
	 * @return array
	 */
	private function _getFieldValues(int $categoryId){
		static $list, $fieldIds;
		if(is_null($list)) $list = array();
		if(is_null($fieldIds)) {
			SOY2::import("domain.shop.SOYShop_CategoryAttribute");
			$fieldIds = array_keys(SOYShop_CategoryAttributeConfig::load(true));
		}

		if(isset($list[$categoryId])) return $list[$categoryId];
		$list[$categoryId] = soyshop_get_hash_table_dao("category_attribute")->getByCategoryIdAndFieldIds($categoryId, $fieldIds, true);
		return $list[$categoryId];
	}

	/**
	 * @param string
	 * @return SOYShop_CategoryAttribute
	 */
	private function _getFieldObject(string $fieldId){
		$field = null;
		if(SOYSHOP_PUBLISH_LANGUAGE != "jp") {
			if(isset($this->fieldTable[$fieldId . "_" . SOYSHOP_PUBLISH_LANGUAGE]) && is_numeric($this->fieldTable[$fieldId . "_" . SOYSHOP_PUBLISH_LANGUAGE]->getCategoryId())){
				return $this->fieldTable[$fieldId . "_" . SOYSHOP_PUBLISH_LANGUAGE];
			}

			//多言語化のプレフィックスの方でも値を取得してみる
			if(SOYSHOP_PUBLISH_LANGUAGE != SOYSHOP_PUBLISH_LANGUAGE_POSTFIX && isset($this->fieldTable[$fieldId . "_" . SOYSHOP_PUBLISH_LANGUAGE_POSTFIX])){
				return $this->fieldTable[$fieldId . "_" . SOYSHOP_PUBLISH_LANGUAGE_POSTFIX];
			}
		}

		return (isset($this->fieldTable[$fieldId])) ? $this->fieldTable[$fieldId] : new SOYShop_CategoryAttribute();
	}
}
SOYShopPlugin::extension("soyshop.site.beforeoutput","common_category_customfield","CommonCategoryCustomfieldBeforeOutput");
