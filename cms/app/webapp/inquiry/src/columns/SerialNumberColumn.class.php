<?php

class SerialNumberColumn extends SOYInquiry_ColumnBase{

	private $serialNumber = 1;
	private $prefix;		//接頭語
	private $digits = 0;	//桁数

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
		$html[] = "次回お問い合わせ時に生成する番号：<input type=\"number\" name=\"Column[config][serialNumber]\" value=\"" . $this->serialNumber . "\"><br><br>";
		$html[] = "番号の桁数：<input type=\"number\" name=\"Column[config][digits]\" value=\"" . $this->digits . "\" style=\"width:80px;\"><br>";
		$html[] = "※桁数が4の場合、1を出力する時は「0001」に変更してから出力する。桁数が0の場合は何もしない。<br><br>";
		$html[] = "接頭語の設定：<input type=\"text\" name=\"Column[config][prefix]\" value=\"" . $this->prefix . "\" placeholder=\"下記の置換文字列を使用できます\" style=\"width:40%;\"><br>";
		$html[] = "※使用可能な置換文字列：##YEAR##、##MONTH##、##DAY##<br>";

		if(strlen($this->prefix)){
			$html[] = "<br>次回お問い合わせ時の出力例：<strong style=\"font-size:1.2em;\">" . SOYInquiryUtil::buildSerialNumber(self::getConfigure()) . "</strong><br><br>";
		}

		return implode("\n", $html);
	}

	/**
	 * 保存された設定値を渡す
	 */
	function setConfigure($config){
		SOYInquiry_ColumnBase::setConfigure($config);
		$this->serialNumber = (isset($config["serialNumber"]) && is_numeric($config["serialNumber"])) ? (int)$config["serialNumber"] : 1;
		$this->prefix = (isset($config["prefix"])) ? $config["prefix"] : "";
		$this->digits = (isset($config["digits"]) && is_numeric($config["digits"])) ? (int)$config["digits"] : 0;
	}

	function getConfigure(){
		$config = parent::getConfigure();
		$config["serialNumber"] = $this->serialNumber;
		$config["prefix"] = $this->prefix;
		$config["digits"] = $this->digits;
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
