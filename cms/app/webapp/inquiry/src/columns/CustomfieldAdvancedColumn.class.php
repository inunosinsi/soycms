<?php

class CustomfieldAdvancedColumn extends SOYInquiry_ColumnBase{

    /**
	 * ユーザに表示するようのフォーム
	 */
	function getForm($attributes = array()){

		// $attributes = $this->getAttributes();
		// $required = $this->getRequiredProp();
		//
		// $values = $this->getValue();

		//サイトid
		$site = self::_getSiteObject();
		$v = self::_getEntryAttrValue($site->getDataSourceName());

		//記事を取得

		$html = array();
		$html[] = $v;
		return implode("\n", $html);
	}

	private function _getSiteObject(){
		$siteId = trim(substr(_SITE_ROOT_, strrpos(_SITE_ROOT_, "/")), "/");

		CMSApplication::switchAdminMode();
		try{
			$site = SOY2DAOFactory::create("admin.SiteDAO")->getBySiteId($siteId);
		}catch(Exception $e){
			$site = new Site();
		}
		CMSApplication::switchAppMode();

		return $site;
	}

	private function _getEntryAttrValue($dsn){
		$old["dsn"] = SOY2DAOConfig::dsn();
		$old["user"] = SOY2DAOConfig::user();
		$old["pass"] = SOY2DAOConfig::pass();

		SOY2DAOConfig::dsn($dsn);
		if(strpos($dsn, "mysql") === 0){
			include_once(_CMS_COMMON_DIR_ . "/config/db/mysql.php");
			SOY2DAOConfig::user(ADMIN_DB_USER);
			SOY2DAOConfig::pass(ADMIN_DB_PASS);
		}

		$pathInfo = trim($_SERVER["PATH_INFO"], "/");
		$alias = trim(substr($pathInfo, strrpos($pathInfo, "/")), "/");

		try{
			$entryId = SOY2DAOFactory::create("cms.EntryDAO")->getByAlias($alias)->getId();
		}catch(Exception $e){
			$entryId = null;
		}

		SOY2DAOConfig::dsn($old["dsn"]);
		SOY2DAOConfig::user($old["user"]);
		SOY2DAOConfig::pass($old["pass"]);

		return "usa";
	}

	function getAttributes(){
		$attributes = array();

		//設定したattributeを挿入
		if(isset($this->attribute) && strlen($this->attribute) > 0){
			$attribute = str_replace("&quot;","\"",$this->attribute);	//"が消えてしまうから、htmlspecialcharsができない
			$attributes[] = trim($attribute);
		}

		return $attributes;
	}

	function getRequiredProp(){
		return (!SOYINQUIRY_FORM_DESIGN_PAGE && $this->requiredProp) ? " required" : "";
	}

	/**
	 * 確認画面で呼び出す
	 */
	function getView(){
		$values = $this->getValue();
		if(!isset($values["year"]) || !isset($values["month"]) || !isset($values["day"])){
			return "----/--/--";
		}else{
			return htmlspecialchars($values["year"] . "/" . $values["month"] . "/" . $values["day"], ENT_QUOTES, "UTF-8");
		}
	}

	/**
	 * 設定画面で表示する用のフォーム
	 */
	function getConfigForm(){
		$html = array();
		return implode("\n", $html);
	}

	/**
	 * 保存された設定値を渡す
	 */
	function setConfigure($config){
		SOYInquiry_ColumnBase::setConfigure($config);
		//$this->attribute = (isset($config["attribute"])) ? str_replace("\"","&quot;",$config["attribute"]) : null;
	}

	function getConfigure(){
		$config = parent::getConfigure();
		//$config["attribute"] = $this->attribute;
		return $config;
	}

	function validate(){
		return true;
	}


    function getLinkagesSOYMailTo() {
		return array(
			SOYMailConverter::SOYMAIL_NONE  => "連携しない",
			SOYMailConverter::SOYMAIL_BIRTHDAY => "生年月日",
			SOYMailConverter::SOYMAIL_ATTR1 => "属性A",
			SOYMailConverter::SOYMAIL_ATTR2 => "属性B",
			SOYMailConverter::SOYMAIL_ATTR3 => "属性C",
			SOYMailConverter::SOYMAIL_MEMO  => "備考"
		);
	}

	function getLinkagesSOYShopFrom() {
		return array(
			SOYShopConnector::SOYSHOP_NONE  => "連携しない",
			SOYShopConnector::SOYSHOP_BIRTHDAY => "生年月日",
		);
	}

	// function factoryConverter() {
	// 	return new DateConverter();
	// }
	//
	// function factoryConnector(){
	// 	return new DateConnector();
	// }
}
