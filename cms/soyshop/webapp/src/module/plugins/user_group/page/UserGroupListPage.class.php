<?php

class UserGroupListPage extends WebPage{

	private $configObj;

	function __construct(){
		SOY2::imports("module.plugins.user_group.domain.*");
	    SOY2::import("module.plugins.user_group.component.GroupListComponent");
	}

	function doPost(){
		if(soy2_check_token()){
			$groupDao = SOY2DAOFactory::create("SOYShop_UserGroupDAO");

			if(isset($_POST["Group"])){
				foreach($_POST["Group"] as $groupId => $int){
					if((int)$int < 1) $int = SOYShop_UserGroup::DISPLAY_ORDER_MAX;

					try{
						$group = $groupDao->getById($groupId);
					}catch(Exception $e){
						continue;
					}

					if($int != $group->getOrder()){
						$group->setOrder($int);

						try{
							$groupDao->update($group);
						}catch(Exception $e){

						}
					}
				}
			}

			SOY2PageController::jump("Extension.user_group?updated");
		}
	}

	function execute(){
		parent::__construct();

		DisplayPlugin::toggle("removed", isset($_GET["removed"]));

		$this->addForm("form");

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
