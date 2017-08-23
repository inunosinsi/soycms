<?php

class SOYCMSUtilMultiLanguageUtil{
	
	const LANGUAGE_JP = "jp";
	const LANGUAGE_EN = "en";
	const LANGUAGE_ZH = "zh";
	
	const MODE_PC = "pc";
	const MODE_SMARTPHONE = "smartphone";
	
	const IS_USE = 1;
	const NO_USE = 0;
	
	public static function allowLanguages($all = true){
		$list = array(
			self::LANGUAGE_JP => "日本語",
			self::LANGUAGE_EN => "英語",
			self::LANGUAGE_ZH => "中国語"
		);
		
		if(!$all){
			
		}
		
		return $list;
	}
}
?>