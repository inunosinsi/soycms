<?php

class AddItemConfigPage extends WebPage{
	
	private $configObj;
	
	function __construct(){
		SOY2::import("module.plugins.member_special_price.util.MemberSpecialPriceUtil");
		SOY2::import("module.plugins.member_special_price.component.RegisteredItemListComponent");
	}
	
	function doPost(){
		if(soy2_check_token()){
			$cfgs = MemberSpecialPriceUtil::getConfig();

			$v = trim($_POST["Add"]["value"]);
			$a = $_POST["Add"]["attribute"];
			$c = ($a == 4) ? $_POST["Add"]["fieldId"] : "";	//ユーザーカスタムフィールド @ToDo ユーザーカスタムサーチフィールドも加味したい
			
			$cfgs[] = array(
				"hash" => substr(md5($v.$a.$c), 0, 6),
				"label" => $v,
				"attribute" => $a,
				"field_id" => $c
			);
			MemberSpecialPriceUtil::saveConfig($cfgs);
			
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
			$cfgs = MemberSpecialPriceUtil::getConfig();
			unset($cfgs[$_GET["index"]]);
			
			//配列を整形する
			$list = array();
			if(count($cfgs)){
				foreach($cfgs as $cfg){
					$list[] = $cfg;
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

		$fieldIds = MemberSpecialPriceUtil::getUserCustomfieldIdList();

		// ユーザーカスタムフィールド
		$isUserCustomField = (count($fieldIds));
		DisplayPlugin::toggle("user_customfirld", $isUserCustomField);
		$this->addCheckBox("user_attribute_customfield", array(
			"name" => "Add[attribute]",
			"value" => 4,
			"label" => "ユーザーカスタムフィールド",
			//"selected" => ($i === 1)
		));

		$this->addSelect("user_field_id", array(
			"name" => "Add[fieldId]",
			"options" => ($isUserCustomField) ? $fieldIds : array()
		));
		
		$this->addInput("user_attribute_value", array(
			"name" => "Add[value]",
			"value" => "",
			"required" => "required"
		));
	}
	
	private function buildList(){
		$cfgs = MemberSpecialPriceUtil::getConfig();
		
		DisplayPlugin::toggle("list", count($cfgs));
		
		$this->createAdd("item_list", "RegisteredItemListComponent", array(
			"list" => $cfgs
		));
	}
	
	function setConfigObj(MemberSpecialPriceConfig $configObj){
		$this->configObj = $configObj;
	}
}
