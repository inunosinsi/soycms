<?php

class SerialNumberColumn extends SOYInquiry_ColumnBase{

	private $serialNumber = 1;

    /**
	 * ユーザに表示するようのフォーム
	 */
	function getForm($attr = array()){
		return null;
	}

	/**
	 * データ投入用
	 *
	 */
	function getContent(){
		return $this->serialNumber;
	}

	/**
	 * 設定画面で表示する用のフォーム
	 */
	function getConfigForm(){
		$html = array();
		$html[] = "次回お問い合わせ時に生成する番号：<input type=\"number\" name=\"Column[config][serialNumber]\" value=\"" . $this->serialNumber . "\">";
		return implode("\n", $html);
	}

	/**
	 * 保存された設定値を渡す
	 */
	function setConfigure($config){
		SOYInquiry_ColumnBase::setConfigure($config);
		$this->serialNumber = (isset($config["serialNumber"]) && is_numeric($config["serialNumber"])) ? (int)$config["serialNumber"] : 1;
	}

	function getConfigure(){
		$config = parent::getConfigure();
		$config["serialNumber"] = $this->serialNumber;
		return $config;
	}

	/**
	 * 確認画面で呼び出す
	 */
//	function getView(){
//		//絵文字削除
//		$value = $this->deleteEmoji($this->getValue());
//		return htmlspecialchars((string)$value, ENT_QUOTES, "UTF-8");
//	}

	function getLinkagesSOYMailTo() {
		return array(
			SOYMailConverter::SOYMAIL_NONE  	=> "連携しない",
			SOYMailConverter::SOYMAIL_NAME 		=> "名前",
			SOYMailConverter::SOYMAIL_READING 	=> "フリガナ",
			SOYMailConverter::SOYMAIL_TEL		=> "電話番号",
			SOYMailConverter::SOYMAIL_FAX		=> "FAX番号",
			SOYMailConverter::SOYMAIL_CELLPHONE	=> "携帯電話番号",
			SOYMailConverter::SOYMAIL_JOB_TEL	=> "勤務先電話番号",
			SOYMailConverter::SOYMAIL_JOB_FAX	=> "勤務先FAX番号",
			SOYMailConverter::SOYMAIL_JOB_NAME	=> "勤務先名称・職種",
			SOYMailConverter::SOYMAIL_ATTR1 	=> "属性A",
			SOYMailConverter::SOYMAIL_ATTR2 	=> "属性B",
			SOYMailConverter::SOYMAIL_ATTR3 	=> "属性C",
			SOYMailConverter::SOYMAIL_MEMO  	=> "備考"
		);
	}

	function getLinkagesSOYShopFrom() {
		return array(
			SOYShopConnector::SOYSHOP_NONE  	=> "連携しない",
			SOYShopConnector::SOYSHOP_NAME 		=> "名前",
			SOYShopConnector::SOYSHOP_READING 	=> "フリガナ",
			SOYShopConnector::SOYSHOP_NICKNAME	=> "ニックネーム",
			SOYShopConnector::SOYSHOP_TEL		=> "電話番号",
			SOYShopConnector::SOYSHOP_FAX		=> "FAX番号",
			SOYShopConnector::SOYSHOP_URL		=> "URL",
			SOYShopConnector::SOYSHOP_CELLPHONE	=> "携帯電話番号",
			SOYShopConnector::SOYSHOP_JOB_TEL	=> "勤務先電話番号",
			SOYShopConnector::SOYSHOP_JOB_FAX	=> "勤務先FAX番号",
			SOYShopConnector::SOYSHOP_JOB_NAME	=> "勤務先名称・職種",
			SOYShopConnector::SOYSHOP_ATTR1 	=> "属性A",
			SOYShopConnector::SOYSHOP_ATTR2 	=> "属性B",
			SOYShopConnector::SOYSHOP_ATTR3 	=> "属性C",
			SOYShopConnector::SOYSHOP_MEMO  	=> "備考"
		);
	}
}
