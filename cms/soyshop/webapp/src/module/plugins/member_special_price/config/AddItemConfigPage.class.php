<?php

class AddItemConfigPage extends WebPage{
	
	private $configObj;
	
	function __construct(){
		SOY2::import("module.plugins.member_special_price.util.MemberSpecialPriceUtil");
		SOY2::import("module.plugins.member_special_price.component.RegisteredItemListComponent");
	}
	
	function doPost(){
		if(soy2_check_token()){
			$configs = MemberSpecialPriceUtil::getConfig();
			
			$v = trim($_POST["Add"]["value"]);
			$a = $_POST["Add"]["attribute"];
			
			$Add = array();
			$Add["hash"] = substr(md5($v . $a), 0, 6);
			$Add["label"] = $v;
			$Add["attribute"] = $a;
			
			$configs[] = $Add;
			MemberSpecialPriceUtil::saveConfig($configs);
			
			$this->configObj->redirect("updated");
		}
	}
	
	function execute(){
		if(isset($_GET["index"])){
			self::remove();
		}
		
		parent::__construct();
		
		DisplayPlugin::toggle("removed", isset($_GET["removed"]));
		
		self::buildAddForm();
		self::buildList();
	}
	
	private function remove(){
		if(soy2_check_token()){
			$configs = MemberSpecialPriceUtil::getConfig();
			unset($configs[$_GET["index"]]);
			
			//配列を整形する
			$list = array();
			if(count($configs)){
				foreach($configs as $conf){
					$list[] = $conf;
				}
			}
			
			MemberSpecialPriceUtil::saveConfig($list);
			$this->configObj->redirect("removed");
		}
	}
	
	private function buildAddForm(){
		$this->addForm("form");
		
		foreach(range(1,3) as $i){
			$this->addCheckBox("user_attribute_" . $i, array(
				"name" => "Add[attribute]",
				"value" => $i,
				"label" => "顧客属性" . $i,
				"selected" => ($i === 1)
			));
		}
		
		$this->addInput("user_attribute_value", array(
			"name" => "Add[value]",
			"value" => "",
			"required" => "required"
		));
	}
	
	private function buildList(){
		$configs = MemberSpecialPriceUtil::getConfig();
		
		DisplayPlugin::toggle("list", count($configs));
		
		$this->createAdd("item_list", "RegisteredItemListComponent", array(
			"list" => $configs
		));
	}
	
	function setConfigObj($configObj){
		$this->configObj = $configObj;
	}
}