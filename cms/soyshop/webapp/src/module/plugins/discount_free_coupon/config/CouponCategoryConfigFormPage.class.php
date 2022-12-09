<?php

class CouponCategoryConfigFormPage extends WebPage {

	private $configObj;
	private $dao;

	function __construct(){
		SOY2::import("module.plugins.discount_free_coupon.domain.SOYShop_CouponCategoryDAO");
		SOY2::import("module.plugins.discount_free_coupon.component.CategoryListComponent");
		$this->dao = SOY2DAOFactory::create("SOYShop_CouponCategoryDAO");
	}

	function doPost(){
		if(soy2_check_token()){
			if(isset($_POST["register"])){
				$category = SOY2::cast("SOYShop_CouponCategory", $_POST["Category"]);
				try{
					$this->dao->insert($category);
				}catch(Exception $e){
					var_dump($e);
				}
				$this->configObj->redirect("category&updated");
			}
		}

		/** soy2_check_tokenなし **/

		//各設定内容を変更する
		if(isset($_POST["edit_save"])){
			$edit = $_POST["Edit"];

			//idを取得できなかった場合は処理を終了
			if(isset($edit["id"])){
				$id = $edit["id"];

				try{
					$category = $this->dao->getById($id);
				}catch(Exception $e){
					$this->configObj->redirect("category&error");
				}

				$category = SOY2::cast($category, (object)$edit);

				try{
					$this->dao->update($category);
					$this->configObj->redirect("category&updated");
				}catch(Exception $e){
					$this->configObj->redirect("category&error");
				}
			}
		}

		//削除フラグ
		if(isset($_POST["remove"])){
			$edit = $_POST["Edit"];

			//idを取得できなかった場合は処理を終了
			if(isset($edit["id"])){
				$id = $edit["id"];

				try{
					$this->dao->deleteById($id);
					$this->configObj->redirect("category&deleted");
				}catch(Exception $e){
					$this->configObj->redirect("category&error");
				}
			}
		}
	}

	function execute(){
		parent::__construct();

		foreach(array("error", "deleted") as $t){
			DisplayPlugin::toggle($t, isset($_GET[$t]));
		}

		$this->addLink("back_link", array(
			"link" => SOY2PageController::createLink("Config.Detail?plugin=discount_free_coupon")
		));

		$this->addLink("register_link", array(
			"link" => SOY2PageController::createLink("Config.Detail?plugin=discount_free_coupon&category#register")
		));

		self::buildList();
		self::buildRegisterForm();
	}

	private function buildList(){
		$categories = self::getCategories();
		DisplayPlugin::toggle("has_category", (count($categories) > 0));

		$this->createAdd("category_list", "CategoryListComponent", array(
			"list" => $categories
		));
	}

	private function getCategories(){
		try{
			return $this->dao->get();
		}catch(Exception $e){
			return array();
		}
	}

	private function buildRegisterForm(){
		$this->addForm("form");

		$this->addInput("name", array(
			"name" => "Category[name]",
			"value" => "",
			"attr:required" => "required"
		));

		$this->addInput("prefix", array(
			"name" => "Category[prefix]",
			"value" => ""
		));
	}

	function setConfigObj($configObj){
		$this->configObj = $configObj;
	}
}
