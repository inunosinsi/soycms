<?php

class AddItemOrderFlagConfigPage extends WebPage{

	private $configObj;

	function __construct(){
		SOY2::import("module.plugins.add_itemorder_flag.util.AddItemOrderFlagUtil");
		SOY2::imports("module.plugins.add_itemorder_flag.component.*");
	}

	function doPost(){

		if(soy2_check_token()){
			$config = array();

			if(isset($_POST["number"]) && count($_POST["number"])){
				for($i = 0; $i < count($_POST["number"]); $i++){
					if(isset($_POST["number"][$i]) && (int)$_POST["number"][$i] > 0){
						if(isset($_POST["label"][$i]) && strlen($_POST["label"][$i])){
							$config[(int)$_POST["number"][$i]] = trim($_POST["label"][$i]);
						}
					}
				}
			}

			//新たに追加する項目
			if((int)$_POST["new_number"] > 0 && strlen($_POST["new_label"])){
				$config[(int)$_POST["new_number"]] = $_POST["new_label"];
			}

			AddItemOrderFlagUtil::saveConfig($config);

			$this->configObj->redirect("updated");
		}
	}

	function execute(){
		parent::__construct();

		$this->addForm("form");

		$this->createAdd("Flag_list", "AddItemOrderFlagListComponent", array(
			"list" => AddItemOrderFlagUtil::getConfig()
		));


		//現在の注文状態の設定状況
		SOY2::import("domain.order.SOYShop_ItemOrder");
		$FlagList = SOYShop_ItemOrder::getFlagList();

		$html = array();
		if(count($FlagList)){
			$html[] = "<table class=\"table table-striped\" style=\"width:50%;\">";
			$html[] = "<caption>フラグの設定状況</caption>";
			$html[] = "<tr><th class=\"col-lg-2\">フラグID</th><th>ラベル</th></tr>";
			foreach($FlagList as $key => $label){
				$html[] = "<tr><td>" . $key . "</td><td>" . $label . "</td></tr>";
			}
			$html[] = "</table>";
			$html[] = "<br style=\"clear:left;\">";
		}

		$this->addLabel("config_detail", array(
			"html" => implode("\n", $html)
		));
	}

	function setConfigObj($configObj){
		$this->configObj = $configObj;
	}
}
