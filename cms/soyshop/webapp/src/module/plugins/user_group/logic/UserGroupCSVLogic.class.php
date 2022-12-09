<?php

SOY2::import("logic.csv.ExImportLogicBase");
class UserGroupCSVLogic extends ExImportLogicBase {

	function __construct(){
		SOY2::import("module.plugins.user_group.util.UserGroupCustomSearchFieldUtil");
	}

	function getLabels(){
		$list = array();
		$list[] = "id";
		$list[] = "グループ名";
		$list[] = "グループコード";

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

		$dbLogic = SOY2Logic::createInstance("module.plugins.user_group.logic.UserGroupDataBaseLogic");
		SOY2::import("module.plugins.user_group.util.UserGroupCustomSearchFieldUtil");
		$configs = UserGroupCustomSearchFieldUtil::getConfig();
		$fieldIdList = self::buildFieldIdList($configs);

		foreach($groups as $group){
			$values = $dbLogic->getByGroupId($group->getId());
			$line = array();
			$line[] = $group->getId();
			$line[] = $group->getName();
			$line[] = $group->getCode();

			if(count($values)) {
				//バリューの整列が必要
				foreach($fieldIdList as $fieldId){
					$v = (isset($values[$fieldId])) ? $values[$fieldId] : "";
					switch($configs[$fieldId]["type"]){
						case UserGroupCustomSearchFieldUtil::TYPE_CHECKBOX:
							$v = "\"" . str_replace(",", "\n", $v) . "\"";
							break;
						case UserGroupCustomSearchFieldUtil::TYPE_DATE:
							if(strlen($v)){
								$v = date("Y-m-d", $v);
							}
							break;
						default:
							//何もしない
					}
					$line[] = $v;
				}
			}

			$lines[] = implode(",", $line);
		}

		return $lines;
	}

	private function buildFieldIdList($configs){
		$list = array();
		foreach($configs as $fieldId => $conf){
			$list[] = $fieldId;
		}
		return $list;
	}
}
