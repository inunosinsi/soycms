<?php

class ECCUBE3CSVImportConfigFormPage extends WebPage{

	private $configObj;

	const TYPE_CUSTOMER = "customer";
	const TYPE_CATEGORY = "category";
	const TYPE_PRODUCT = "product";
	const TYPE_ORDER = "order";

	function __construct(){}

	function doPost(){

		if(!soy2_check_token()) $this->configObj->redirect("failed");

		//インポート
		if(self::checkCSVFileUpload()){

			SOY2::import("logic.csv.ExImportLogicBase");

			//タイプには必ずcaseのどれかが入っている
			switch($this->type){
				//お客様情報
				case self::TYPE_CUSTOMER:
					$logic = SOY2Logic::createInstance("module.plugins.eccube3_csv_import.logic.ImportCustomerInfoLogic");
					break;
				//カテゴリ情報
				case self::TYPE_CATEGORY:
					$logic = SOY2Logic::createInstance("module.plugins.eccube3_csv_import.logic.ImportCategoryInfoLogic");
					break;
				//商品情報
				case self::TYPE_PRODUCT:
					$logic = SOY2Logic::createInstance("module.plugins.eccube3_csv_import.logic.ImportItemInfoLogic");
					break;
				//受注情報
				case self::TYPE_ORDER:
					$logic = SOY2Logic::createInstance("module.plugins.eccube3_csv_import.logic.ImportOrderInfoLogic");
					break;
			}
			$logic->setType($this->type);
			$logic->execute();

			$this->configObj->redirect("successed");
		}

		$this->configObj->redirect("failed");
	}

	function execute(){
		parent::__construct();

		DisplayPlugin::toggle("successed", isset($_GET["successed"]));
		DisplayPlugin::toggle("failed", isset($_GET["failed"]));

		$this->addForm("form", array(
			"enctype" => "multipart/form-data"
		));
	}

	/**
	 * CSVファイルがアップロードされたかチェックする。trueの時は何のCSVをアップロードしているかもプロパティ値に入れておく
	 * @return boolean
	 */
	private function checkCSVFileUpload(){
		foreach($_FILES["CSV"]["name"] as $key => $str){
			if(strlen($str) && strpos($str, ".csv")){
				$this->type = $key;
				return true;
			}
		}

		return false;
	}

	function setConfigObj($configObj){
		$this->configObj = $configObj;
	}
}
