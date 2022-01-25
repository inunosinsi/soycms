<?php

class MigrateConfigLogic extends SOY2LogicBase{


	const OFFSET = 100;
	private $pluginObj;

	function __construct(){}

	function import(){
		set_time_limit(0);
		$obj = CMSPlugin::loadPluginConfig("CustomField");

		//設定がない場合は何もしない。
		if(count($obj->customFields) === 0) return;

		foreach($obj->customFields as $customField){
			if(strlen($customField->getId()) > 0){
				$this->pluginObj->insertField($customField);
			}
		}

		$dao = soycms_get_hash_table_dao("entry_attribute");
		$attr = new EntryAttribute();

		//記事数を取得
		try{
			$res = $dao->executeQuery("SELECT id FROM Entry ORDER BY id DESC LIMIT 1;");
		}catch(Exception $e){
			$res = array();
		}
		
		if(isset($res[0]["id"]) && is_numeric($res[0]["id"])){
			$lastEntryId = (int)$res[0]["id"];
			if($lastEntryId > 0){
				$exeCnt = (int)ceil($lastEntryId / self::OFFSET);
				
				$latestEntryId = self::OFFSET;
				for($i = 0; $i < $exeCnt; $i++){
					try{
						$res = $dao->executeQuery(
							"SELECT id FROM Entry WHERE id >= :startEntryId AND id < :endEntryId AND custom_field IS NOT NULL;", 
							array(
								":startEntryId" => ($latestEntryId - self::OFFSET),
								":endEntryId" => $latestEntryId
							)
						);
					}catch(Exception $e){
						$res = array();
					}
					$latestEntryId += self::OFFSET;
					if(!count($res)) continue;

					foreach($res as $v){
						if(!isset($v["id"]) || !is_numeric($v["id"])) continue;
						$fields = $obj->getCustomFields($v["id"]);
						if(!count($fields)) continue;

						foreach($fields as $field){
							if(!strlen((string)$field->getValue())) continue;	//処理の節約

							$attr = new EntryAttribute();
							$attr->setEntryId($v["id"]);
							$attr->setFieldId($field->getId());
							$attr->setValue($field->getValue());
							if(is_array($field->getExtraValues()) && count($field->getExtraValues())) $attr->setExtraValuesArray($field->getExtraValues());
							soycms_save_entry_attribute_object($attr);
						}
					}	
				}
			}
		}

		CMSUtil::notifyUpdate();
		CMSPlugin::redirectConfigPage();
	}

	function setPluginObj($pluginObj){
		$this->pluginObj = $pluginObj;
	}
}
