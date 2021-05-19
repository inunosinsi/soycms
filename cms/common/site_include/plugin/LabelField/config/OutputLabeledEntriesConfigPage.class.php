<?php

class OutputLabeledEntriesConfigPage extends WebPage {

	private $pluginObj;

	function __construct(){

	}

	function doPost(){
		if(soy2_check_token()){
			if(isset($_POST["create"])){
				$cnfs = self::_configs();
				$arr = array("label" => trim($_POST["label"]), "postfix" => trim($_POST["id"]));
				$cnfs[$arr["postfix"]] = $arr;	//同じpostfixの場合は上書き
				$this->pluginObj->setConfigs($cnfs);
				CMSPlugin::savePluginConfig(LabelFieldPlugin::PLUGIN_ID,$this->pluginObj);
			}
		}

		//並び替え
		if(isset($_POST["move_up"]) || isset($_POST["move_down"])){
			if(isset($_POST["field_id"])){
				$diff = (isset($_POST["move_up"])) ? -1 : 1;
				$cnfs = self::_configs();
				if(isset($cnfs[$_POST["field_id"]])){
					$keys = array_keys($cnfs);
					$currentKey = array_search($_POST["field_id"], $keys);
					$swap = ($diff > 0) ? $currentKey+1 :$currentKey-1;

					if($swap >= 0 && $swap < count($keys)){
						$tmp = $keys[$currentKey];
						$keys[$currentKey] = $keys[$swap];
						$keys[$swap] = $tmp;

						$tmpArray = array();
						foreach($keys as $index => $value){
							$field = $cnfs[$value];
							$tmpArray[$field["postfix"]] = $field;
						}

						$this->pluginObj->setConfigs($tmpArray);
						CMSPlugin::savePluginConfig(LabelFieldPlugin::PLUGIN_ID,$this->pluginObj);
					}
				}
			}
		}

		//削除
		if(isset($_POST["delete_submit"])){
			$cnfs = self::_configs();
			if(isset($cnfs[$_POST["delete_submit"]])){
				unset($cnfs[$_POST["delete_submit"]]);
				$this->pluginObj->setConfigs($cnfs);
				CMSPlugin::savePluginConfig(LabelFieldPlugin::PLUGIN_ID,$this->pluginObj);
			}
		}

		CMSUtil::notifyUpdate();
		CMSPlugin::redirectConfigPage();
	}

	function execute(){
		parent::__construct();

		$cnfs = self::_configs();
		$cnt = count($cnfs);
		DisplayPlugin::toggle("field_table", $cnt > 0);
		DisplayPlugin::toggle("add_field", $cnt === 0);

		SOY2::import("site_include.plugin.LabelField.component.LabelFieldListComponent");
		$this->createAdd("field_list","LabelFieldListComponent",array(
			"list" => $cnfs
		));

		self::_buildCreateForm();
	}

	private function _buildCreateForm(){
        $this->addForm("create_form");
    }

	private function _configs(){
		$cnfs = $this->pluginObj->getConfigs();
		if(!is_array($cnfs)) return array();

		$list = array();
		foreach($cnfs as $cnf){
			if(!isset($cnf["label"]) || !strlen($cnf["label"])) continue;
			if(!isset($cnf["postfix"]) || !strlen($cnf["postfix"])) continue;
			$list[$cnf["postfix"]] = $cnf;
		}

		return $list;
	}

	function setPluginObj($pluginObj){
		$this->pluginObj = $pluginObj;
	}
}
