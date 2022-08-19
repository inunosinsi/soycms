<?php

class LabelCustomfieldUtil {

	public static function checkIsEntryField(array $fields){
		static $isEntry;
		if(is_null($isEntry)){
			$isEntry = false;
			if(is_array($fields) && count($fields)){
				foreach($fields as $field){
					if($field->getType() == "entry"){
						$isEntry = true;
						break;
					}
				}
			}
		}
		return $isEntry;
	}

	public static function checkIsLabelField(array $fields){
		static $isLabel;
		if(is_null($isLabel)){
			$isLabel = false;
			if(is_array($fields) && count($fields)){
				foreach($fields as $field){
					if($field->getType() == "label"){
						$isLabel = true;
						break;
					}
				}
			}
		}
		return $isLabel;
	}

	public static function checkIsListField(array $fields){
		static $isList;
		if(is_null($isList)){
			$isList = false;
			if(is_array($fields) && count($fields)){
				foreach($fields as $field){
					if($field->getType() == "list"){
						$isList = true;
						break;
					}
				}
			}
		}
		return $isList;
	}

	public static function checkIsDlListField(array $fields){
		static $isList;
		if(is_null($isList)){
			$isList = false;
			if(is_array($fields) && count($fields)){
				foreach($fields as $field){
					if($field->getType() == "dllist"){
						$isList = true;
						break;
					}
				}
			}
		}
		return $isList;
	}
}
