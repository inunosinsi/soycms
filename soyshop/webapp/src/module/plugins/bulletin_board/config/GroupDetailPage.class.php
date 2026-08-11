<?php

class GroupDetailPage extends WebPage {

	private $groupId;
	private $configObj;

	function __construct(){
		SOY2::import("module.plugins.bulletin_board.util.BulletinBoardUtil");
	}

	function doPost(){
		if(soy2_check_token()){
			$logic = SOY2Logic::createInstance("module.plugins.bulletin_board.logic.GroupLogic");
			$logic->saveGroupAbstract($this->groupId, $_POST["Group"]["abstract"]);
			$logic->saveGroupDescription($this->groupId, $_POST["Group"]["description"]);
			$this->configObj->redirect("updated&group_id=" . $this->groupId);
		}
		$this->configObj->redirect("failed&group_id=" . $this->groupId);
	}

	function execute(){
		if(!isset($_GET["group_id"]) || !is_numeric($_GET["group_id"])){
			$this->configObj->redirect("");
		}

		$logic = SOY2Logic::createInstance("module.plugins.bulletin_board.logic.GroupLogic");
		$group = $logic->getById($_GET["group_id"]);
		if(!is_numeric($group->getId())) $this->configObj->redirect("");

		$this->groupId = (int)$group->getId();

		parent::__construct();

		$this->addLink("back_link", array(
			"link" => SOY2PageController::createLink("Config.Detail?plugin=bulletin_board")
		));

		$this->addLabel("group_name", array(
			"text" => $group->getName()
		));

		$this->addForm("form");

		$this->addTextArea("group_abstract", array(
			"name" => "Group[abstract]",
			"value" => $logic->getGroupAbstractById($group->getId()),
			"style" => "height:60px;"
		));

		$this->addTextArea("group_description", array(
			"name" => "Group[description]",
			"value" => $logic->getGroupDescriptionById($group->getId()),
			"style" => "height:150px;"
		));

	}

	function setConfigObj($configObj){
		$this->configObj = $configObj;
	}
}
