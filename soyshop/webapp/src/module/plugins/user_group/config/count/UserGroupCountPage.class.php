<?php

class UserGroupCountPage extends WebPage{

	private $configObj;

	function __construct(){
		SOY2::import("module.plugins.user_group.component.grouping.UserListComponent");
	}

	function execute(){
		if(!isset($_GET["group_id"]) || !is_numeric($_GET["group_id"])) SOY2PageController::jump("Extension.user_group");
		$users = SOY2Logic::createInstance("module.plugins.user_group.logic.GroupingLogic")->getUsersByGroupId($_GET["group_id"]);
		if(!count($users)) SOY2PageController::jump("Extension.user_group");

		parent::__construct();

		$this->addLabel("group_name", array(
			"text" => self::getGroupNameById($_GET["group_id"])
		));

		//属性のラベルの変更
		for($i = 1; $i <= 3; $i++){
			$this->addLabel("user_attribute_label_".$i, array(
				"text" => constant("USER_ATTRIBUTE_LABEL_".$i)
			));
		}

		$this->createAdd("user_list", "_common.User.UserListComponent", array(
			"list" => $users
		));
	}

	private function getGroupNameById($groupId){
		SOY2::import("module.plugins.user_group.domain.SOYShop_UserGroupDAO");
		try{
			return SOY2DAOFactory::create("SOYShop_UserGroupDAO")->getById($groupId)->getName();
		}catch(Exception $e){
			return "";
		}
	}

	function setConfigObj($configObj){
		$this->configObj = $configObj;
	}
}
