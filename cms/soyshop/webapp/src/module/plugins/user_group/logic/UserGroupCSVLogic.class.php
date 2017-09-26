<?php

class UserGroupCSVLogic extends SOY2LogicBase {

	function __construct(){
		SOY2::import("module.plugins.user_group.util.UserGroupCustomSearchFieldUtil");
	}

	function getLabels(){
		$list = array();
		$list[] = "id";
		$list[] = "グループ名";

		$configs = UserGroupCustomSearchFieldUtil::getConfig();
		if(count($configs)){
			foreach($configs as $conf){
				if(isset($conf["label"]) && strlen($conf["label"])){
					$list[] = $conf["label"];
				}
			}
		}
		return $list;
	}

	function getLines(){
		SOY2::imports("module.plugins.user_group.domain.*");
		try{
			$groups = SOY2DAOFactory::create("SOYShop_UserGroupDAO")->get();
		}catch(Exception $e){
			return array();
		}

		if(!count($groups)) return array();

		$lines = array();

		$dbLogic = SOY2Logic::createInstance("module.plugins.user_group.logic.DataBaseLogic");
		foreach($groups as $group){
			$values = $dbLogic->getByGroupId($group->getId());
			$line = array();
			$line[] = $group->getId();
			$line[] = $group->getName();

			if(count($values)) {
				foreach($values as $fieldId => $v){
					if($fieldId == "group_id") continue;
					$line[] = "\"" . $v . "\"";
				}
			}

			$lines[] = implode(",", $line);
		}

		return $lines;
	}
}
