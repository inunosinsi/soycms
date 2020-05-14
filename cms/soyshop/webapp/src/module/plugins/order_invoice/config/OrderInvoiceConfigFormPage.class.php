<?php

class OrderInvoiceConfigFormPage extends WebPage{

	private $configObj;

	function __construct(){
		SOY2::import("module.plugins.order_invoice.common.OrderInvoiceCommon");
	}

	function doPost(){

		if(soy2_check_token()){

			$configs = $_POST["Config"];

			//画像の登録
			foreach($_FILES as $key => $file){
				if(strlen($file["type"]) > 0){
					//ファイルの拡張子をチェックする
					if(preg_match('/(jpg|jpeg|gif|png)$/', $file["name"])){
						$fname = $file["name"];

						$dest_name = OrderInvoiceCommon::getFileDirectory() . $fname;

						if(@move_uploaded_file($file["tmp_name"], $dest_name)){
							$configs[$key] = htmlspecialchars($fname, ENT_QUOTES, "UTF-8");
						}
					}
				}
			}

			//画像の削除
			if(count($_POST["Delete"])){
				foreach($_POST["Delete"] as $key => $v){
					if($v == 1){
						$configs[$key] = null;
					}
				}
			}

			OrderInvoiceCommon::saveTemplateName($_POST["Template"]);
			OrderInvoiceCommon::saveConfig($configs);

			$this->configObj->redirect("updated");
		}

	}

	function execute(){
		parent::__construct();
		
		$this->addForm("form", array(
			"enctype" => "multipart/form-data"
		));

		$config = OrderInvoiceCommon::getConfig();

		/** 画像系 **/
		foreach(array("logo", "stamp") as $t){
			DisplayPlugin::toggle("no_" . $t, (!isset($config[$t]) || !strlen($config[$t])));
			DisplayPlugin::toggle("is_" . $t, (isset($config[$t]) && strlen($config[$t])));

			$this->addImage($t, array(
				"src" => (isset($config[$t]) && strlen($config[$t])) ? OrderInvoiceCommon::getFileUrl() . $config[$t] : ""
			));

			$this->addInput($t . "_hidden", array(
				"name" => "Config[" . $t . "]",
				"value" => (isset($config[$t])) ? $config[$t] : null
			));

			$this->addCheckBox($t . "_delete", array(
				"name" => "Delete[" . $t . "]",
				"value" => 1,
				"label" => "削除する"
			));
		}

		$this->addSelect("template", array(
			"name" => "Template",
			"options" => OrderInvoiceCommon::getTemplateList(),
			"selected" => OrderInvoiceCommon::getTemplateName()
		));



		$this->addCheckBox("payment", array(
			"name" => "Config[payment]",
			"value" => 1,
			"selected" => (isset($config["payment"]) && $config["payment"] == 1),
			"label" => " 表示する"
		));

		$this->addCheckBox("first_order", array(
			"name" => "Config[firstOrder]",
			"value" => 1,
			"selected" => (isset($config["firstOrder"]) && $config["firstOrder"] == 1),
			"label" => " 表示する"
		));

		$this->addInput("title", array(
			"name" => "Config[title]",
			"value" => (isset($config["title"])) ? $config["title"] : ""
		));

		$this->addTextArea("content", array(
			"name" => "Config[content]",
			"value" => (isset($config["content"])) ? $config["content"] : "",
			"style" => "height:150px;"
		));
	}

	function setConfigObj($configObj){
		$this->configObj = $configObj;
	}
}
?>
