<?php

class TCCategoryPage extends WebPage {

	private $configObj;

	function __construct(){
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

		$catList = self::_getCategoryList();
		self::_prepare($catList);

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

	// soyshop_tag_cloud_dictionaryに格納していないタグも格納しておく
	private function _prepare(array $categoryList){
		SOY2::import("module.plugins.tag_cloud.util.TagCloudUtil");
		$cnf = TagCloudUtil::getConfig();
		$tags = explode(",", $cnf["tags"]);

		$dicDao = SOY2DAOFactory::create("SOYShop_TagCloudDictionaryDAO");
		foreach($tags as $tag){
			$tag = trim($tag);
			try{
				$tagObj = $dicDao->getByWord($tag);
			}catch(Exception $e){
				$tagObj = new SOYShop_TagCloudDictionary();
				$tagObj->setWord($tag);
				try{
					$dicDao->insert($tagObj);
				}catch(Exception $e){
					var_dump($e);
					//
				}
			}
		}

		$categoryIds = array_keys($categoryList);
		try{
			$res = $dicDao->executeQuery("SELECT id FROM soyshop_tag_cloud_dictionary WHERE category_id NOT IN (" . implode(",", $categoryIds) . ")");
		}catch(Exception $e){
			$res = array();
		}
		if(!count($res)) return;

		//該当するタグは未分類にする
		$wordIds = array();
		foreach($res as $v){
			$wordIds[] = (int)$v["id"];
		}
		try{
			$dicDao->executeUpdateQuery("UPDATE soyshop_tag_cloud_dictionary SET category_id = 0 WHERE id IN (" . implode(",", $wordIds) . ")");
		}catch(Exception $e){
			//
		}
	}

	private function _getCategoryList(){
		try{
			$categories = SOY2DAOFactory::create("SOYShop_TagCloudCategoryDAO")->get();
		}catch(Exception $e){
			$categories = array();
		}
		$list = array();
		if(count($categories)){
			foreach($categories as $cat){
				$list[$cat->getId()] = $cat->getLabel();
			}
		}
		$list[0] = "未分類";
		return $list;
	}

	function setConfigObj($configObj){
		$this->configObj = $configObj;
	}
}
