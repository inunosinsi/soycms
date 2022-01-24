<?php

class MigrateConfigLogic extends SOY2LogicBase{

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

		$entryDao = SOY2DAOFactory::create("cms.EntryDAO");
		$entryDao->setOrder("id ASC");
		try{
			$entries = $entryDao->get();
		}catch(Exception $e){
			return;
		}

		$dao = SOY2DAOFactory::create("cms.EntryAttributeDAO");
		foreach($entries as $entry){
			$fields = $obj->getCustomFields($entry->getId());
			if(!count($fields)) continue;

			foreach($fields as $field){
				if( (is_null($field->getValue()) || !strlen($field->getValue())) && !count($field->getExtraValuesArray())) continue;	//処理の節約

				try{
					$attr = $dao->get($entry->getId(), $entry->getFieldId);
				}catch(Exception $e){
					$attr = new EntryAttribute();
					$attr->setEntryId($entry->getId());
					$attr->setFieldId($field->getId());
					$attr->setValue($field->getValue());
					$attr->setExtraValuesArray($field->getExtraValues());
					try{
						$dao->insert($attr);
					}catch(Exception $e){
						//
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

