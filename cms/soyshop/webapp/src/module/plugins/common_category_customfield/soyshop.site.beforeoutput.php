<?php
/*
 * soyshop.site.beforeoutput.php
 * Created: 2010/03/11
 */

class CommonCategoryCustomfieldBeforeOutput extends SOYShopSiteBeforeOutputAction{

	function beforeOutput($page){

		$obj = $page->getPageObject();

		//カートページとマイページでは読み込まない
		if(!is_object($obj) || get_class($obj) != "SOYShop_Page") return;

		if(
			$obj->getType() == SOYShop_Page::TYPE_COMPLEX ||
			$obj->getType() == SOYShop_Page::TYPE_FREE ||
			$obj->getType() == SOYShop_Page::TYPE_SEARCH
		) return;

		//商品一覧ページ以外では動作しない
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
					$category = soyshop_get_category_object($parent->getCategory());
				}else{
					$category = soyshop_get_category_object($current->getCategory());
				}
				$name = $category->getOpenCategoryName();
				break;
		}

		$page->addLabel("current_category_name", array(
			"soy2prefix" => SOYSHOP_SITE_PREFIX,
			"text" => $name
		));


		$dao = SOY2DAOFactory::create("shop.SOYShop_CategoryAttributeDAO");
		try{
			$array = $dao->getByCategoryId($category->getId());
		}catch(Exception $e){
			$array = array();
		}

		SOY2::import("domain.shop.SOYShop_Item");
		$dummyItem = new SOYShop_Item();

		$list = SOYShop_CategoryAttributeConfig::load();

		foreach($list as $config){
			$value = (isset($array[$config->getFieldId()])) ? $array[$config->getFieldId()]->getValue() : null;
			$value2 = (isset($array[$config->getFieldId()])) ? $array[$config->getFieldId()]->getValue2() : null;

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
					$value = soyshop_convert_file_path($value, $dummyItem);

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
								"visible" => (isset($value))
							));
						}else{
							$page->addImage($config->getFieldId(), array(
								"src" => $value,
								"soy2prefix" => SOYSHOP_SITE_PREFIX,
								"visible" => (isset($value))
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
							"attr:" . htmlspecialchars($config->getOutput()) => soyshop_customfield_nl2br($value),
							"soy2prefix" => SOYSHOP_SITE_PREFIX
						));
					}else{
						$page->addLabel($config->getFieldId(), array(
							"html" => soyshop_customfield_nl2br($value),
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
SOYShopPlugin::extension("soyshop.site.beforeoutput","common_category_customfield","CommonCategoryCustomfieldBeforeOutput");
