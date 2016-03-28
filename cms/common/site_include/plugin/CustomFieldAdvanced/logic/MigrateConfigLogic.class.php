<?php

class MigrateConfigLogic extends SOY2LogicBase{
	
	private $pluginObj;
	
	function MigrateConfigLogic(){
		
	}
	
	function import(){
		set_time_limit(0);
		$obj = CMSPlugin::loadPluginConfig("CustomField");
		
		//設定がない場合は何もしない。
		if(count($obj->customFields) === 0) return;
		
		foreach($obj->customFields as $customField){
			if(strlen($customField->getId())>0){
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
			$entryId = $entry->getId();
			$fields = $obj->getCustomFields($entry->getId());
			
			foreach($fields as $field){
				$attr = new EntryAttribute();
				$attr->setEntryId($entryId);
				$attr->setFieldId($field->getId());
				$attr->setValue($field->getValue());
				$attr->setExtraValuesArray($field->getExtraValues());
				$dao->insert($attr);
			}
		}
		
		CMSUtil::notifyUpdate();
		CMSPlugin::redirectConfigPage();
	}
			
	function setPluginObj($pluginObj){
		$this->pluginObj = $pluginObj;
	}
}
?>