<?php

class TCCategoryPage extends WebPage {

	private $configObj;

	function __construct(){
		SOY2::import("module.plugins.tag_cloud.util.TagCloudUtil");
		SOY2::import("module.plugins.tag_cloud.component.TagCloudCategoryDivListComponent");
		SOY2::import("module.plugins.tag_cloud.domain.SOYShop_TagCloudCategoryDAO");
		SOY2::import("module.plugins.tag_cloud.domain.SOYShop_TagCloudDictionaryDAO");
	}

	function doPost(){
		if(soy2_check_token()){
			if(isset($_POST["label"]) && strlen($_POST["label"])){
				$catDao = SOY2DAOFactory::create("SOYShop_TagCloudCategoryDAO");
				$cat = new SOYShop_TagCloudCategory();
				$cat->setLabel($_POST["label"]);
				try{
					$catDao->insert($cat);
				}catch(Exception $e){
					//
				}
			}

			if(isset($_POST["post"])){
				$dicDao = SOY2DAOFactory::create("SOYShop_TagCloudDictionaryDAO");
				try{
					$dic = $dicDao->getById($_POST["word_id"]);
					$dic->setCategoryId((int)$_POST["category_id"]);
					$dicDao->update($dic);
				}catch(Exception $e){
					//
				}
			}

			$this->configObj->redirect("category&update");
		}
	}

	function execute(){
		parent::__construct();

		//削除
		if(isset($_GET["category_id"]) && is_numeric($_GET["category_id"]) && soy2_check_token()){
			self::_remove($_GET["category_id"]);
		}

		$catList = TagCloudUtil::getTagCategoryList();
		TagCloudUtil::prepareCategory($catList);

		$this->createAdd("category_div_list", "TagCloudCategoryDivListComponent", array(
			"list" => $catList
		));

		$this->addForm("create_form");

		$ids = array_keys($catList);
		$this->addLabel("category_id_list_js", array(
			"text" => implode(",", $ids)
		));

		$this->addForm("form");
	}

	private function _remove(int $categoryId){
		try{
			SOY2DAOFactory::create("SOYShop_TagCloudCategoryDAO")->deleteById($categoryId);
		}catch(Exception $e){
			//
		}
		$this->configObj->redirect("category&update");
	}

	function setConfigObj($configObj){
		$this->configObj = $configObj;
	}
}
