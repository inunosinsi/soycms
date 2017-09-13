<?php

class UserGroupListPage extends WebPage{

	private $configObj;

	function __construct(){
		SOY2::imports("module.plugins.user_group.domain.*");
	    SOY2::import("module.plugins.user_group.component.GroupListComponent");
	}

	function execute(){
		parent::__construct();

		DisplayPlugin::toggle("removed", isset($_GET["removed"]));

	    $this->createAdd("group_list", "GroupListComponent", array(
	      "list" => self::get(),
		  "groupingDao" => SOY2DAOFactory::create("SOYShop_UserGroupingDAO")
	    ));
	}

	private function get(){
      $dao = SOY2DAOFactory::create("SOYShop_UserGroupDAO");
      try{
        return $dao->get();
      }catch(Exception $e){
        return array();
      }
    }

	function setConfigObj($configObj){
        $this->configObj = $configObj;
	}
}
