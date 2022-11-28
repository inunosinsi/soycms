<?php

class AttributeOrderTotalConfigPage extends WebPage{

	private $configObj;
	const MAX = 2147483647;

	function __construct(){
		SOY2::import("module.plugins.attribute_order_total.util.AttributeOrderTotalUtil");
		SOY2::imports("module.plugins.attribute_order_total.component.*");
	}

	function doPost(){
		if(soy2_check_token()){

			//テスト実行
			if(isset($_POST["execute"])){
				SOY2Logic::createInstance("module.plugins.attribute_order_total.logic.SortingLogic")->execute();
				$this->configObj->redirect("successed");
			}

			$values = array();

			if(isset($_POST["total"]) && count($_POST["total"])) for ($i = 0; $i < count($_POST["total"]); $i++){
				//更新
				if(
					(isset($_POST["total"][$i]) && (int)$_POST["total"][$i] >= 0) &&
					(isset($_POST["label"][$i]) && strlen($_POST["label"][$i]) > 0)
				){
					$values[] = array("total" => (int)$_POST["total"][$i], "label" => trim($_POST["label"][$i]));
				}
			}

			//新規登録
			if(
				(isset($_POST["add"]["total"]) && (int)$_POST["add"]["total"] >= 0) &&
				(isset($_POST["add"]["label"]) && strlen($_POST["add"]["label"]) > 0)
			){
				$values[] = $_POST["add"];
			}

			if(isset($_POST["last"]["label"]) && strlen($_POST["last"]["label"]) > 0){
				$values[] = array("total" => self::MAX, "label" => trim($_POST["last"]["label"]));
			}

			//ソート
			$cnts = array();
			foreach($values as $key => $v){
				$cnts[$key] = $v;
			}

			array_multisort($cnts, SORT_ASC, SORT_NUMERIC, $values);
			AttributeOrderTotalUtil::saveConfig($values);

			//属性値設定
			$checked = (isset($_POST["attribute"])) ? (int)$_POST["attribute"] : 1;
			AttributeOrderTotalUtil::saveAttrConfig($checked);

			//期間設定
			AttributeOrderTotalUtil::savePeriodConfig(soyshop_convert_number($_POST["period"], null));

			$this->configObj->redirect("updated");
		}
		$this->configObj->redirect("error");
	}

	function execute(){
		parent::__construct();

		$this->addForm("form");

		$configs = AttributeOrderTotalUtil::getConfig();
		DisplayPlugin::toggle("notice_zero_orders", (!isset($configs[0]["total"])) || $configs[0]["total"] > 0);

		DisplayPlugin::toggle("over_orders", count($configs));

		$this->createAdd("attribute_list", "AttributeListComponent", array(
			"list" => $configs
		));

		$this->addInput("order_total", array(
			"name" => "add[total]",
			"value" => "",
			"style" => "width:90px;text-align:right;"
		));

		$this->addInput("order_label", array(
			"name" => "add[label]",
			"value" => "",
			"style" => "width:70%;"
		));

		$lastLabel = "";
		foreach($configs as $conf){
			if($conf["total"] === self::MAX){
				$lastLabel = $conf["label"];
			}
		}
		$this->addInput("last_label", array(
			"name" => "last[label]",
			"value" => $lastLabel,
			"style" => "width:70%;"
		));

		$checked = AttributeOrderTotalUtil::getAttrConfig();
		foreach(range(1,3) as $i){
			$this->addCheckBox("insert_attribute" . $i, array(
				"name" => "attribute",
				"value" => $i,
				"selected" => ($i == $checked),
				"label" => "属性" . $i
			));
		}


		$this->addInput("analyze_period", array(
			"name" => "period",
			"value" => AttributeOrderTotalUtil::getPeriodConfig(),
			"style" => "width:150px;text-align:right;"
		));

		$this->addLabel("job_path", array(
			"text" => self::buildPath(). " " . SOYSHOP_ID
		));

		$this->addLabel("site_id", array(
			"text" => SOYSHOP_ID
		));
	}

	private function buildPath(){
		return dirname(dirname(__FILE__)) . "/job/exe.php";
	}

	function setConfigObj($configObj){
		$this->configObj = $configObj;
	}
}
?>
