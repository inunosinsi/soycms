<?php

class CheckLogic extends SOY2LogicBase {

	function __construct(){}

	function get($mode="CustomField", $fieldId){
		switch($mode){
			case "CustomFieldAdvanced":
				return self::_customfieldAdvanced($fieldId);
			case "CustomField":
			default:
				return self::_customfield($fieldId);
		}
	}

	function getConfig($mode="CustomField"){
		$obj = CMSPlugin::loadPluginConfig($mode);
		if(!count($obj->customFields)) return array();

		$fields = array();
		foreach($obj->customFields as $f){
			$fields[$f->getId()] = $f->getLabel();
		}
		return $fields;
	}

	function _customfield($fieldId){
		$obj = CMSPlugin::loadPluginConfig("CustomField");
		$haves = array();	//値がある
		$nones = array(); 	//値がない
		$lim = 100;

		$dao = new SOY2DAO();
		$sql = "SELECT id, title, openPeriodStart, openPeriodEnd, isPublished, custom_field FROM Entry LIMIT " . $lim . " ";

		$i = 1;
		for(;;){
			$res = $dao->executeQuery($sql . "OFFSET " . (($i++ - 1) * $lim));
			if(!count($res)) break;
			foreach($res as $v){
				$fieldV = null;
				if(isset($v["custom_field"]) && strlen($v["custom_field"])){
					$fieldValues = soy2_unserialize($v["custom_field"]);
					if(count($fieldValues)){
						foreach($fieldValues as $fV){
							if($fV->getId() == $fieldId && strlen($fV->getValue())){
								$fieldV = $fV->getValue();
							}
						}
					}
				}

				if(strlen($fieldV)){
					$haves[$v["id"]] = self::_convert($v);
				}else{
					$nones[$v["id"]] = self::_convert($v);
				}
			}
		}

		return array($haves, $nones);
	}

	function _customfieldAdvanced($fieldId){
		$obj = CMSPlugin::loadPluginConfig("CustomFieldAdvanced");
		$haves = array();	//値がある
		$nones = array(); 	//値がない
		$lim = 100;

		$dao = new SOY2DAO();
		//nonesの方のみ拾うSQL
		$noneSql = "SELECT id, title, openPeriodStart, openPeriodEnd, isPublished FROM Entry ".
				"WHERE id NOT IN (" .
					"SELECT entry_id FROM EntryAttribute WHERE entry_field_id = :fieldId ".
				") ".
				"LIMIT " . $lim . " ";
		//havesの方を調べる
		$haveSql = "SELECT ent.id, ent.title, ent.openPeriodStart, ent.openPeriodEnd, ent.isPublished, attr.entry_value FROM Entry ent ".
					"INNER JOIN EntryAttribute attr ".
					"ON ent.id = attr.entry_id ".
					"WHERE attr.entry_field_id = :fieldId ".
					"LIMIT " . $lim . " ";

		$i = 1;
		for(;;){
			$flg = false;
			$res = $dao->executeQuery($noneSql . "OFFSET " . (($i - 1) * $lim), array(":fieldId" => $fieldId));
			if(count($res)){	//すべてがnonesに入る
				$flg = true;
				foreach($res as $v){
					$nones[$v["id"]] = self::_convert($v);
				}
			}

			$res = $dao->executeQuery($haveSql . "OFFSET " . (($i++ - 1) * $lim), array(":fieldId" => $fieldId));
			if(count($res)){
				$flg = true;
				foreach($res as $v){
					if(isset($v["entry_value"]) && strlen($v["entry_value"])){
						$haves[$v["id"]] = self::_convert($v);
					}else{
						$nones[$v["id"]] = self::_convert($v);
					}
				}
			}

			//どちらのSQLも取得出来なかった場合は抜ける
			if(!$flg) break;
		}

		return array($haves, $nones);
	}

	private function _convert($v){
		$title = $v["title"];
		if($v["isPublished"] != 1) $title .= "(非公開)";
		if($v["openPeriodStart"] > time() || $v["openPeriodEnd"] < time()) $title .= "(公開期間外)";
		return $title;
	}
}
