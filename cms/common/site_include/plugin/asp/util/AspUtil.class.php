<?php

class AspUtil {

	const MODE_REGISTER = 0;
	const MODE_CONFIRM = 1;
	const MODE_PRE_REGISTRATION = 2;
	const MODE_COMPLETE = 3;

	const MAIL_PRE = "pre";
	const MAIL_REGISTER = "register";

	public static function save($values){
		//端のスペースを削除
		foreach($values as $key => $v){
			$values[$key] = trim(str_replace("　", "", $v));
		}
		SOY2ActionSession::getUserSession()->setAttribute("soycms_asp", soy2_serialize($values));
	}

	//セッション内の値を取得
	public static function get($row=false){
		$v = SOY2ActionSession::getUserSession()->getAttribute("soycms_asp");
		$values = (isset($v) && strlen($v)) ? soy2_unserialize($v) : array();

		//オブジェクトにしないで配列のままで取得
		if($row) return $values;

		$old = CMSUtil::switchDsn();
		SOY2::import("domain.admin.Administrator");

		if(count($values)){
			$admin = SOY2::cast("Administrator", $values);
		}else{
			$admin = new Administrator();
		}

		CMSUtil::resetDsn($old);

		return $admin;
	}

	public static function saveSiteId($siteId){
		//端のスペースを削除
		SOY2ActionSession::getUserSession()->setAttribute("soycms_asp_site_id", trim($siteId));
	}

	public static function getSiteId(){
		return SOY2ActionSession::getUserSession()->getAttribute("soycms_asp_site_id");
	}

	public static function clear(){
		SOY2ActionSession::getUserSession()->setAttribute("soycms_asp", null);
		SOY2ActionSession::getUserSession()->setAttribute("soycms_asp_site_id", null);
	}

	public static function getPageUri($mode=self::MODE_REGISTER, $isAbsolute=false){
		static $baseUri;
		if(is_null($baseUri)){
			$baseUri = $_SERVER["REQUEST_URI"];
			if(strpos($baseUri, "?")){
				$baseUri = trim(substr($baseUri, 0, strpos($baseUri, "?")));
			}

			$pageType = self::_getPageType();
			switch(self::_getPageType()){
				case self::MODE_CONFIRM:
					$baseUri = str_replace("/confirm", "", $baseUri);
					break;
				case self::MODE_PRE_REGISTRATION:
					$baseUri = str_replace("/pre", "", $baseUri);
					break;
				case self::MODE_COMPLETE:
					$baseUri = str_replace("/complete", "", $baseUri);
					break;
				default:
					//何もしない
			}
		}

		if($isAbsolute){
			$baseUri = self::_getSiteUrl() . trim($baseUri, "/");
		}

		switch($mode){
			case self::MODE_CONFIRM:
				return $baseUri . "/confirm";
			case self::MODE_PRE_REGISTRATION:
				return $baseUri . "/pre";
			case self::MODE_COMPLETE:
				return $baseUri . "/complete";
			default:
				return $baseUri;
		}
	}

	public static function getLoginFormUrl(){
		$url = self::_getSiteUrl();
		$cmsDir = dirname(_CMS_COMMON_DIR_);
		$cmsDir = trim(substr($cmsDir, strrpos($cmsDir, "/")), "/");
		return $url . $cmsDir . "/admin";
	}

	public static function getSiteUrl(){
		return self::_getSiteUrl();
	}

	private static function _getSiteUrl(){
		static $url;
		if(is_null($url)){
			$http = (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on") ? "https" : "http";
			$url = $http . "://" . $_SERVER["HTTP_HOST"] . "/";
		}
		return $url;
	}

	public static function getPageType(){
		return self::_getPageType();
	}

	private static function _getPageType(){
		static $mode;
		if(isset($mode)) return $mode;
		$requri = $_SERVER["REQUEST_URI"];
		if(strpos($requri, "?")){
			$requri = substr($requri, 0, strpos($requri, "?"));
		}

		//最後の文字列でタイプを取得
		$alias = trim(substr($requri, strrpos($requri, "/")), "/");

		switch($alias){
			case "confirm":
				$mode = self::MODE_CONFIRM;
				break;
			case "pre":
				$mode = self::MODE_PRE_REGISTRATION;
				break;
			case "complete":
				$mode = self::MODE_COMPLETE;
				break;
			default:
				$mode = self::MODE_REGISTER;
		}

		return $mode;
	}

	public static function buildPasswordString($str){
		$txt = "";
		for($i = 0; $i < strlen($str); $i++){
			$txt .= "*";
		}
		return $txt;
	}

	public static function generateToken(AspPreRegister $obj){
		$d = $obj->getDataArray();
		return md5($d["email"] . $obj->getSiteId() . date("Ymd"));
	}

	public static function saveMailConfig($mode, $mail){
		SOY2::import("domain.cms.DataSets");
		DataSets::put("asp.mail." . $mode . ".config", $mail);
	}

	public static function getMailConfig($mode = self::MAIL_PRE){
		SOY2::import("domain.cms.DataSets");
		$mail = DataSets::get("asp.mail." . $mode . ".config", array());
		foreach(array("title", "content") as $t){
			if(!isset($mail[$t])){
				$mail[$t] = file_get_contents(dirname(dirname(__FILE__)) . "/template/" . $mode . "/" . $t . ".txt");
			}
		}

		return $mail;
	}
}
