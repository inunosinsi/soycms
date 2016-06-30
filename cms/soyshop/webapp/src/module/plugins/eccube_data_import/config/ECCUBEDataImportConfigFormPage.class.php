<?php

class ECCUBEDataImportConfigFormPage extends WebPage{
	
	const TYPE_CUSTOMER = "customer";
	const TYPE_POINT = "point";
	const TYPE_CATEGORY = "category";
	const TYPE_PRODUCT = "product";
	const TYPE_GRANT = "grant";
	const TYPE_ORDER = "order";
	
	private $configObj;
	private $type;
	
	function ECCUBEDataImportConfigFormPage(){
		SOY2::import("module.plugins.eccube_data_import.util.EccubeDataImportUtil");
	}
	
	function doPost(){
		
		if(!soy2_check_token()) $this->configObj->redirect("failed");
				
		//ダンプ時のデータを保存
		if(isset($_POST["save"]) && isset($_POST["Config"])){
			EccubeDataImportUtil::saveConfig($_POST["Config"]);
			$this->configObj->redirect("saved");
		}
		
		//データベースに接続してダンプを実行
		if(isset($_POST["execute"])){
			$logic = SOY2Logic::createInstance("module.plugins.eccube_data_import.logic.DumpDatabaseLogic");
			$res = $logic->execute();
			if($res){
				$this->configObj->redirect("successed");
			}else{
				$this->configObj->redirect("defect");
			}
			
		}
		
		if(isset($_POST["auth_magic"])){
			EccubeDataImportUtil::saveAuthMagic($_POST["auth_magic"]);
			$this->configObj->redirect("saved");
		}
		
		//パスワードの生成
		if(isset($_POST["create"])){
			$logic = SOY2Logic::createInstance("module.plugins.eccube_data_import.logic.CreatePasswordLogic");
			$logic->execute();
			$this->configObj->redirect("successed");
		}
		
		if(isset($_POST["mail_save"])){
			EccubeDataImportUtil::saveMailConfig($_POST["Mail"]);
			$this->configObj->redirect("saved");
		}
		
		//インポート
		if(self::checkCSVFileUpload()){
			
			SOY2::import("logic.csv.ExImportLogicBase");
			
			//タイプには必ずcaseのどれかが入っている
			switch($this->type){
				//お客様情報
				case self::TYPE_CUSTOMER:
					$logic = SOY2Logic::createInstance("module.plugins.eccube_data_import.logic.ImportCustomerInfoLogic");
					break;
				//ポイント情報
				case self::TYPE_POINT:
					$logic = SOY2Logic::createInstance("module.plugins.eccube_data_import.logic.ImportPointLogic");
					break;
				//カテゴリ情報
				case self::TYPE_CATEGORY:
					$logic = SOY2Logic::createInstance("module.plugins.eccube_data_import.logic.ImportCategoryInfoLogic");
					break;
				//商品情報
				case self::TYPE_PRODUCT:
					$logic = SOY2Logic::createInstance("module.plugins.eccube_data_import.logic.ImportItemInfoLogic");
					break;
				//商品毎のポイント付与設定
				case self::TYPE_GRANT:
					$logic = SOY2Logic::createInstance("module.plugins.eccube_data_import.logic.ImportGrantLogic");
					break;
				//受注情報
				case self::TYPE_ORDER:
					$logic = SOY2Logic::createInstance("module.plugins.eccube_data_import.logic.ImportOrderInfoLogic");
					break;
			}
			
			$logic->setType($this->type);
			$logic->execute();
			
			$this->configObj->redirect("successed");
		}
	}
	
	function execute(){
		WebPage::WebPage();
		
		DisplayPlugin::toggle("saved", isset($_GET["saved"]));
		DisplayPlugin::toggle("successed", isset($_GET["successed"]));
		DisplayPlugin::toggle("failed", isset($_GET["failed"]));
		DisplayPlugin::toggle("defect", isset($_GET["defect"]));
		
		$pointPluginActive = (class_exists("SOYShopPluginUtil") && SOYShopPluginUtil::checkIsActive("common_point_base"));
		
		DisplayPlugin::toggle("point_plugin_inactive", !$pointPluginActive);
		
		self::buildDumpForm();
		
		$this->addForm("form", array(
			"enctype" => "multipart/form-data"
		));
		
		$this->addForm("conf_form");
		
		$this->addInput("auth_magic", array(
			"name" => "auth_magic",
			"value" => EccubeDataImportUtil::getAuthMagic()
		));
		
		$this->addForm("pass_form");

		self::buildMailForm();
		
		//不要になったコードたち
		DisplayPlugin::toggle("necessary", false);
	}
	
	private function buildDumpForm(){
		$config = EccubeDataImportUtil::getConfig();
		
		$this->addForm("dump_form");
		
		foreach(array("host", "port", "db", "user") as $val){
			$this->addInput($val, array(
				"name" => "Config[" . $val . "]",
				"value" => $config[$val]
			));	
		}
		
		DisplayPlugin::toggle("display_exe_button", strlen($config["user"]));
	}
	
	private function buildMailForm(){
		$mail = EccubeDataImportUtil::getMailConfig();
		
		$this->addForm("mail_form");
		
		$this->addInput("mail_title", array(
			"name" => "Mail[title]",
			"value" => (isset($mail["title"])) ? $mail["title"] : ""
		));
		
		$this->addTextArea("mail_content", array(
			"name" => "Mail[content]",
			"value" => (isset($mail["content"])) ? $mail["content"] : ""
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