<?php

class SOYShopConnector {
	const SOYSHOP_NONE 		=	"none";
	const SOYSHOP_NAME		= 	"name";
	const SOYSHOP_READING	=	"reading";
	const SOYSHOP_NICKNAME	=	"nickname";
	const SOYSHOP_TEL		=	"telephone_number";
	const SOYSHOP_FAX		=	"fax_number";
	const SOYSHOP_CELLPHONE	=	"cellphone_number";
	const SOYSHOP_URL		=	"url";
	const SOYSHOP_JOB_TEL	=	"job_telephone_number";
	const SOYSHOP_JOB_FAX	=	"job_fax_number";
	const SOYSHOP_JOB_NAME	=	"job_name";
	const SOYSHOP_ATTR1		=	"attribute1";
	const SOYSHOP_ATTR2		=	"attribute2";
	const SOYSHOP_ATTR3		=	"attribute3";
	const SOYSHOP_MEMO		=	"memo";
	const SOYSHOP_ADDRESS 	=	"address";
	const SOYSHOP_JOBADDRESS=	"job_address";
	const SOYSHOP_BIRTHDAY 	=	"birthday";
	const SOYSHOP_GENDER	=	"gender";
	const SOYSHOP_MAIL		=	"mail_address";
	const SOYSHOP_AREA		=	"area";
	const SOYSHOP_JOB_AREA	=	"job_area";
	const SOYSHOP_MAIL_SEND	=	"mail_send";

	private $connectLogic;
	public $user;

	function __construct(){
		if(!$this->connectLogic){
			$this->connectLogic = SOY2Logic::createInstance("logic.SOYShopConnectLogic");
			$this->user = $this->connectLogic->getSOYShopUser();
		}
	}

	function insert($connectFrom){
		$user = $this->user;

		switch($connectFrom){
			case SOYShopConnector::SOYSHOP_NAME:
				$value = $user->getName();
				break;
			case SOYShopConnector::SOYSHOP_READING:
				$value = $user->getReading();
				break;
			case SOYShopConnector::SOYSHOP_NICKNAME:
				$value = $user->getNickName();
				break;
			case SOYShopConnector::SOYSHOP_TEL:
				$value = $user->getTelephoneNumber();
				break;
			case SOYShopConnector::SOYSHOP_FAX:
				$value = $user->getFaxNumber();
				break;
			case SOYShopConnector::SOYSHOP_URL:
				$value = $user->getUrl();
				break;
			case SOYShopConnector::SOYSHOP_CELLPHONE:
				$value = $user->getCellphoneNumber();
				break;
			case SOYShopConnector::SOYSHOP_JOB_NAME:
				$value = $user->getJobName();
				break;
			case SOYShopConnector::SOYSHOP_JOB_TEL:
				$value = $user->getJobTelephoneNumber();
				break;
			case SOYShopConnector::SOYSHOP_JOB_FAX:
				$value = $user->getJobFaxNumber();
				break;
			case SOYShopConnector::SOYSHOP_MAIL:
				$value = $user->getMailAddress();
				break;
			case SOYShopConnector::SOYSHOP_MEMO:
				$value = $user->getMemo();
				break;
			case SOYShopConnector::SOYSHOP_NONE:
			default:
				$value = null;
				break;
		}

		return $value;
	}
}

class AddressConnector extends SOYShopConnector {

	private $prefecture = array(
			"1" => "北海道",
			"2" => "青森県",
			"3" => "岩手県",
			"4" => "宮城県",
			"5" => "秋田県",
			"6" => "山形県",
			"7" => "福島県",
			"8" => "茨城県",
			"9" => "栃木県",
			"10" => "群馬県",
			"11" => "埼玉県",
			"12" => "千葉県",
			"13" => "東京都",
			"14" => "神奈川県",
			"15" => "新潟県",
			"16" => "富山県",
			"17" => "石川県",
			"18" => "福井県",
			"19" => "山梨県",
			"20" => "長野県",
			"21" => "岐阜県",
			"22" => "静岡県",
			"23" => "愛知県",
			"24" => "三重県",
			"25" => "滋賀県",
			"26" => "京都府",
			"27" => "大阪府",
			"28" => "兵庫県",
			"29" => "奈良県",
			"30" => "和歌山県",
			"31" => "鳥取県",
			"32" => "島根県",
			"33" => "岡山県",
			"34" => "広島県",
			"35" => "山口県",
			"36" => "徳島県",
			"37" => "香川県",
			"38" => "愛媛県",
			"39" => "高知県",
			"40" => "福岡県",
			"41" => "佐賀県",
			"42" => "長崎県",
			"43" => "熊本県",
			"44" => "大分県",
			"45" => "宮崎県",
			"46" => "鹿児島県",
			"47" => "沖縄県",
			"48" => "その他・海外",
	);

    function insert($connectFrom) {
    	$user = $this->user;

    	switch ($connectFrom) {
    		case SOYShopConnector::SOYSHOP_AREA:
				$value = (!is_null($user->getArea())) ? $this->prefecture[$user->getArea()] : null;
    			break;
    		case SOYShopConnector::SOYSHOP_JOB_AREA:
				$value = (!is_null($user->getJobArea())) ? $this->prefecture[$user->getJobArea()] : null;
    			break;
    		case SOYShopConnector::SOYSHOP_ADDRESS:
    			if(strpos($user->getZipcode(), "-")){
    				$zipArray = explode("-", $user->getZipcode());
    				$value["zip1"] = $zipArray[0];
    				$value["zip2"] = $zipArray[1];
    			}elseif(strlen($user->getZipcode()) === 7){
    				$value["zip1"] = substr($user->getZipcode(), 0, 3);
    				$value["zip2"] = substr($user->getZipcode(), 3);
    			}else{
    				$value["zip1"] = null;
    				$value["zip2"] = null;
    			}
    			$value["prefecture"] = (!is_null($user->getArea())) ? $this->prefecture[$user->getArea()] : null;
    			$value["address1"] = $user->getAddress1();
    			$value["address2"] = $user->getAddress2();
    			$value["address3"] = null;
    			break;
   			case SOYShopConnector::SOYSHOP_JOBADDRESS:
   				if(strpos($user->getZipcode(), "-")){
    				$zipArray = explode("-", $user->getJobZipcode());
    				$value["zip1"] = $zipArray[0];
    				$value["zip2"] = $zipArray[1];
    			}elseif(strlen($user->getJobZipcode()) === 7){
    				$value["zip1"] = substr($user->getJobZipcode(), 0, 3);
    				$value["zip2"] = substr($user->getJobZipcode(), 3);
    			}else{
    				$value["zip1"] = null;
    				$value["zip2"] = null;
    			}
    			$value["prefecture"] = (!is_null($user->getJobArea())) ? $this->prefecture[$user->getJobArea()] : null;
    			$value["address1"] = $user->getJobAddress1();
    			$value["address2"] = $user->getJobAddress2();
    			$value["address3"] = null;
    			break;
			default:
				$value = null;
				break;
    	}

    	return $value;
    }
}

class DateConnector extends SOYShopConnector{
	function insert($connectFrom){
		$user = $this->user;

		if(is_null($user->getBirthday())) return null;

		switch($connectFrom){
			case SOYShopConnector::SOYSHOP_BIRTHDAY:
				$birthday = explode("-", $user->getBirthday());
				$value = array("year" => $birthday[0], "month" => $birthday[1], "day" => $birthday[2]);
				break;
			default:
				$value = null;
				break;
		}

		return $value;
	}
}
