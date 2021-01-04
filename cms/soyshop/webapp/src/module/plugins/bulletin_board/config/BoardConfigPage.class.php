<?php

class BoardConfigPage extends WebPage {

	private $configObj;

	function __construct(){
		SOY2::import("module.plugins.bulletin_board._component.GroupListComponent");
	}

	function doPost(){
		if(soy2_check_token()){
			if(isset($_POST["Group"])){
				$groupId = self::_logic()->insert($_POST["Group"]);
				if(is_numeric($groupId)){
					$this->configObj->redirect("updated");
				}else{
					//何もしない
				}
			} else if(isset($_POST["sort"]) && isset($_POST["DisplayOrder"]) && is_array($_POST["DisplayOrder"]) && count($_POST["DisplayOrder"])){
				foreach($_POST["DisplayOrder"] as $groupId => $displayOrder){
					self::_logic()->setDisplayOrder($groupId, $displayOrder);
				}
				$this->configObj->redirect("updated");
			}

		}
		$this->configObj->redirect("failed");
	}

	function execute(){
		//削除
		if(isset($_GET["remove"]) && is_numeric($_GET["remove"]) && soy2_check_token()){
			self::_remove($_GET["remove"]);
			$this->configObj->redirect("updated");
		}

		parent::__construct();

		DisplayPlugin::toggle("failed", isset($_GET["failed"]));

		$this->addForm("create_form");

		$groups = self::_logic()->get();
		$cnt = count($groups);

		$this->addForm("edit_form");

		DisplayPlugin::toggle("groups", $cnt > 0);
		$this->createAdd("group_list", "GroupListComponent", array(
			"list" => $groups
		));
	}

	private function _remove($groupId){
		self::_logic()->delete($groupId);
	}

	function _logic(){
		static $logic;
		if(is_null($logic)) $logic = SOY2Logic::createInstance("module.plugins.bulletin_board.logic.GroupLogic");
		return $logic;
	}

	function setConfigObj($configObj){
		$this->configObj = $configObj;
	}
}
