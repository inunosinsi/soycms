<?php

class AttributeOrderCountConfigPage extends WebPage{
	
	private $configObj;
	const MAX = 2147483647;
		
	function __construct(){
		SOY2::import("module.plugins.attribute_order_count.util.AttributeOrderCountUtil");
		SOY2::imports("module.plugins.attribute_order_count.component.*");
	}
	
	function doPost(){
		if(soy2_check_token()){
			
			//テスト実行
			if(isset($_POST["execute"])){
				SOY2Logic::createInstance("module.plugins.attribute_order_count.logic.SortingLogic")->execute();
				$this->configObj->redirect("successed");
			}
			
			$values = array();
			
			if(isset($_POST["count"]) && count($_POST["count"])) for ($i = 0; $i < count($_POST["count"]); $i++){
				//更新
				if(
					(isset($_POST["count"][$i]) && (int)$_POST["count"][$i] >= 0) &&
					(isset($_POST["label"][$i]) && strlen($_POST["label"][$i]) > 0)
				){
					$values[] = array("count" => (int)$_POST["count"][$i], "label" => trim($_POST["label"][$i]));
				}
			}
			
			//新規登録
			if(
				(isset($_POST["add"]["count"]) && (int)$_POST["add"]["count"] >= 0) &&
				(isset($_POST["add"]["label"]) && strlen($_POST["add"]["label"]) > 0)
			){
				$values[] = $_POST["add"];
			}
			
			if(isset($_POST["last"]["label"]) && strlen($_POST["last"]["label"]) > 0){
				$values[] = array("count" => self::MAX, "label" => trim($_POST["last"]["label"]));
			}
			
			//ソート
			$cnts = array();
			foreach($values as $key => $v){
				$cnts[$key] = $v;
			}
			
			array_multisort($cnts, SORT_ASC, SORT_NUMERIC, $values);
			AttributeOrderCountUtil::saveConfig($values);
			
			//属性値設定
			$checked = (isset($_POST["attribute"])) ? (int)$_POST["attribute"] : 1;
			AttributeOrderCountUtil::saveAttrConfig($checked);
			
			//期間設定
			AttributeOrderCountUtil::savePeriodConfig(soyshop_convert_number($_POST["period"], null));
			
			$this->configObj->redirect("updated");
		}
		$this->configObj->redirect("error");
	}
	
	function execute(){
		WebPage::__construct();
				
		$this->addForm("form");
		
		$configs = AttributeOrderCountUtil::getConfig();	
		DisplayPlugin::toggle("notice_zero_orders", (!isset($configs[0]["count"])) || $configs[0]["count"] > 0);
		
		DisplayPlugin::toggle("over_orders", count($configs));
		
		$this->createAdd("attribute_list", "AttributeListComponent", array(
			"list" => $configs
		));
		
		$this->addInput("order_count", array(
			"name" => "add[count]",
			"value" => "",
			"style" => "width:70px;text-align:right;"
		));
		
		$this->addInput("order_label", array(
			"name" => "add[label]",
			"value" => "",
			"style" => "width:70%;"
		));
		
		$lastLabel = "";
		foreach($configs as $conf){
			if($conf["count"] === self::MAX){
				$lastLabel = $conf["label"];
			}
		}
		$this->addInput("last_label", array(
			"name" => "last[label]",
			"value" => $lastLabel,
			"style" => "width:70%;"
		));
		
		$checked = AttributeOrderCountUtil::getAttrConfig();
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
			"value" => AttributeOrderCountUtil::getPeriodConfig(),
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