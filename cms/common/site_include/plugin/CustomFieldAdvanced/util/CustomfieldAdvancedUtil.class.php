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
}
