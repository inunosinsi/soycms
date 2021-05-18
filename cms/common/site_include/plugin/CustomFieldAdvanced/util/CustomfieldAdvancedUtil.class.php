<?php

class CustomfieldAdvancedUtil {

	public static function createHash($v){
		return substr(md5($v), 0, 6);
	}

	//カスタムフィールドアドバンスドの設定内に記事フィールドはあるか？
	public static function checkIsEntryField($fields){
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

	public static function checkIsLabelField($fields){
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
}
