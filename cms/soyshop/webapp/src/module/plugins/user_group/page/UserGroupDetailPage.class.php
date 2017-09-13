<?php

class UserGroupDetailPage extends WebPage{

	private $configObj;
	private $detailId;

	function __construct(){
		SOY2::imports("module.plugins.user_group.domain.*");
		SOY2::import("module.plugins.user_group.util.UserGroupCustomSearchFieldUtil");
	}

	function doPost(){
		if(soy2_check_token()){
			$old = self::getGroupById($this->detailId);
			$group = SOY2::cast($old, $_POST["Group"]);

			//新規登録
			if(is_null($group->getId())){
				try{
					$this->detailId = self::groupDao()->insert($group);
				}catch(Exception $e){
					var_dump($e);
				}
			//更新
			}else{
				try{
					self::groupDao()->update($group);
				}catch(Exception $e){
					var_dump($e);
				}
			}

			//カスタムサーチフィールド
			if(isset($_POST["user_group_custom"]) && count($_POST["user_group_custom"])){
				SOY2Logic::createInstance("module.plugins.user_group.logic.DataBaseLogic")->save($this->detailId, $_POST["user_group_custom"]);
			}

			SOY2PageController::jump("Extension.Detail.user_group." . $this->detailId . "?updated");
		}
		SOY2PageController::jump("Extension.Detail.user_group." . $this->detailId . "?error");
	}

	function execute(){
		parent::__construct();

		DisplayPlugin::toggle("error", isset($_GET["error"]));

		self::buildForm();
	}

	private function buildForm(){

		$group = self::getGroupById($this->detailId);

		$this->addForm("form");

		$this->addInput("name", array(
			"name" => "Group[name]",
			"value" => $group->getName()
		));

		//カスタムサーチフィールド
		$html = array();
		SOY2::import("module.plugins.user_group.component.GroupFieldFormComponent");
		$configs = UserGroupCustomSearchFieldUtil::getConfig();
		if(count($configs)){
			$values = SOY2Logic::createInstance("module.plugins.user_group.logic.DataBaseLogic")->getByGroupId($this->detailId);

			foreach($configs as $key => $field){
				if(!isset($field["label"]) || !strlen($field["label"])) continue;
				$value = (isset($values[$key])) ? $values[$key] : null;

				$html[] = "<dt>" . htmlspecialchars($field["label"], ENT_QUOTES, "UTF-8") . "</dt>\n" .
							"<dd>" . GroupFieldFormComponent::buildForm($key, $field, $value) . "</dd>";
			}
		}

		$this->addLabel("build_form", array(
			"html" => implode("\n", $html)
		));
	}

	private function getGroupById($groupId){
		try{
			return self::groupDao()->getById($groupId);
		}catch(Exception $e){
			return new SOYShop_UserGroup();
		}
	}

	private function groupDao(){
		static $dao;
		if(is_null($dao)) $dao = SOY2DAOFactory::create("SOYShop_UserGroupDAO");
		return $dao;
	}

	function setConfigObj($configObj){
        $this->configObj = $configObj;
	}

	function setDetailId($detailId){
    	$this->detailId = $detailId;
    }
}
