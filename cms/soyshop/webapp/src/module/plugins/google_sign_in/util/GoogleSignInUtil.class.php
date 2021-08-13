<?php

class GoogleSignInUtil {

	public static function getConfig(){
		return SOYShop_DataSets::get("google_sign_in.config", array());
	}

	public static function saveConfig($values){
		$values["pre_register_mode"] = (isset($values["pre_register_mode"])) ? (int)["pre_register_mode"] : 0;
		return SOYShop_DataSets::put("google_sign_in.config", $values);
	}

	public static function getButtonHTML(string $clientId=""){
		$path = self::_customButtonHtmlPath();
		if(file_exists($path)){
			$btn = file_get_contents($path);
			if(strlen($clientId)) $btn = str_replace("##YOUR_CLIENT_ID##", $clientId, $btn);
			return $btn;
		}
		return self::_buttonHtml($clientId);
	}

	public static function saveButtonHTML($html){
		$old = self::_buttonHtml();
		if(trim($html) != $old){	//更新
			file_put_contents(self::_customButtonHtmlPath(), $html);
		}else{	//削除
			self::_removeButtonHtml();
		}
	}

	public static function returnButtonHtml(){
		self::_removeButtonHtml();
	}

	public static function setSampleButtonHtml(){
		$html = trim(file_get_contents(dirname(dirname(__FILE__)) . "/template/sample.html"));
		file_put_contents(self::_customButtonHtmlPath(), $html);
	}

	private static function _buttonHtml(string $clientId=""){
		$btn = trim(file_get_contents(dirname(dirname(__FILE__)) . "/template/base.html"));
		if(strlen($clientId)) $btn = str_replace("##YOUR_CLIENT_ID##", $clientId, $btn);
		return $btn;
	}

	private static function _customButtonHtmlPath(){
		$dir = self::_getButtonDirectory();
		return $dir . "google_sign_in.html";
	}

	private static function _removeButtonHtml(){
		$path = self::_customButtonHtmlPath();
		if(file_exists($path)) unlink($path);
	}

	private static function _getButtonDirectory(){
		$dir = SOYSHOP_SITE_DIRECTORY . ".parts/";
		if(!file_exists($dir)) mkdir($dir);
		$dir .= "sns/";
		if(!file_exists($dir)) mkdir($dir);
		return $dir;
	}
}
