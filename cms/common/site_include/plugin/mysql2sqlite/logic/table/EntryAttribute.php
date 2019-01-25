<?php

function registerEntryAttribute($stmt){
	try{
		$attrs = SOY2DAOFactory::create("cms.EntryAttributeDAO")->getAll();
		if(!count($attrs)) return;
	}catch(Exception $e){
		return;
	}

	foreach($attrs as $attr){
		$stmt->execute(array(
			":entry_id" => $attr->getEntryId(),
			":entry_field_id" => $attr->getFieldId(),
			":entry_value" => $attr->getValue(),
			":entry_extra_values" => $attr->getExtraValues()
		));
	}
}
