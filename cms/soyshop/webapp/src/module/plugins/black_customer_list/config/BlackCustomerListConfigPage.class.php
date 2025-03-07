<?php

class BlackCustomerListConfigPage extends WebPage{
	
	private $pluginObj;
	
	function __construct(){}
	
	function doPost(){
	}
	
	function execute(){
		parent::__construct();
		
		$users = SOY2Logic::createInstance("module.plugins.black_customer_list.logic.BlackListLogic")->getBlackList();
		$cnt = count($users);
		
		DisplayPlugin::toggle("no_black_list", !$cnt);
		DisplayPlugin::toggle("display_black_list", $cnt);

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
		
	function setPluginObj($pluginObj){
		$this->pluginObj = $pluginObj;
	}
}