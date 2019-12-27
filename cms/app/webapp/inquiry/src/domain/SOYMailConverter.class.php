<?php

class SOYMailConverter {
	const SOYMAIL_NONE 		=	"none";
	const SOYMAIL_NAME		= 	"name";
	const SOYMAIL_READING	=	"reading";
	const SOYMAIL_TEL		=	"telephone_number";
	const SOYMAIL_FAX		=	"fax_number";
	const SOYMAIL_CELLPHONE	=	"cellphone_number";
	const SOYMAIL_JOB_TEL	=	"job_telephone_number";
	const SOYMAIL_JOB_FAX	=	"job_fax_number";
	const SOYMAIL_JOB_NAME	=	"job_name";
	const SOYMAIL_ATTR1		=	"attribute1";
	const SOYMAIL_ATTR2		=	"attribute2";
	const SOYMAIL_ATTR3		=	"attribute3";
	const SOYMAIL_MEMO		=	"memo";
	const SOYMAIL_ADDRESS 	=	"address";
	const SOYMAIL_JOBADDRESS=	"job_address";
	const SOYMAIL_BIRTHDAY 	=	"birthday";
	const SOYMAIL_GENDER	=	"gender";
	const SOYMAIL_MAIL		=	"mail_address";
	const SOYMAIL_AREA		=	"area";
	const SOYMAIL_MAIL_SEND	=	"mail_send";


    function __construct() {}

    /**
     * 与えられた$valueを指定のフォーマットに変換して返す
     * @return array( "SOYMailの連携先カラム名" => value)
     */
    function convert($value, $linkto=SOYMailConverter::SOYMAIL_NONE) {

    	if(!strlen($linkto) || $linkto == SOYMailConverter::SOYMAIL_NONE){
    		return null;
    	}

    	if(is_array($value)){
    		$value = implode(", ",$value);
    	}

    	switch ($linkto) {
    		case SOYMailConverter::SOYMAIL_MEMO:
    			return array( SOYMailConverter::SOYMAIL_MEMO => (string)$value);
    			break;
    		default:
    			return array( $linkto => $value );
    			break;
    	}
    }
}

class AddressConverter extends SOYMailConverter {

	private $prefecture = array(
			"北海道" => "1",
			"青森県" => "2",
			"岩手県" => "3",
			"宮城県" => "4",
			"秋田県" => "5",
			"山形県" => "6",
			"福島県" => "7",
			"茨城県" => "8",
			"栃木県" => "9",
			"群馬県" => "10",
			"埼玉県" => "11",
			"千葉県" => "12",
			"東京都" => "13",
			"神奈川県" => "14",
			"新潟県" => "15",
			"富山県" => "16",
			"石川県" => "17",
			"福井県" => "18",
			"山梨県" => "19",
			"長野県" => "20",
			"岐阜県" => "21",
			"静岡県" => "22",
			"愛知県" => "23",
			"三重県" => "24",
			"滋賀県" => "25",
			"京都府" => "26",
			"大阪府" => "27",
			"兵庫県" => "28",
			"奈良県" => "29",
			"和歌山県" => "30",
			"鳥取県" => "31",
			"島根県" => "32",
			"岡山県" => "33",
			"広島県" => "34",
			"山口県" => "35",
			"徳島県" => "36",
			"香川県" => "37",
			"愛媛県" => "38",
			"高知県" => "39",
			"福岡県" => "40",
			"佐賀県" => "41",
			"長崎県" => "42",
			"熊本県" => "43",
			"大分県" => "44",
			"宮崎県" => "45",
			"鹿児島県" => "46",
			"沖縄県" => "47",
			"その他・海外" => "48",
	);

    function convert($value, $linkto=SOYMailConverter::SOYMAIL_NONE) {
    	switch ($linkto) {
    		case SOYMailConverter::SOYMAIL_ADDRESS:

				$ret = array(
					"zip_code" => self::_zipcode($value),
					"area" => $this->prefecture[$value["prefecture"]],
					"address1" => $value["address1"],
					"address2" => $value["address2"]
				);
				if(strlen($value["address3"])) $ret["address2"] .= $value["address3"];
				return $ret;
		   		break;
    		case SOYMailConverter::SOYMAIL_JOBADDRESS:
				$ret = array(
					"job_zip_code" => self::_zipcode($value),
					"job_area" => $this->prefecture[$value["prefecture"]],
					"job_address1" => $value["address1"],
					"job_address2" => $value["address2"]
				);
				if(strlen($value["address3"])) $ret["job_address2"] .= $value["address3"];
				return $ret;
		   		break;
    		case SOYMailConverter::SOYMAIL_MEMO:
    			$val  = self::_zipcode($value) ." ";
    			$val .= $value["prefecture"] . $value["address1"] . $value["address2"] . $value["address3"];
    			return array(
    				SOYMailConverter::SOYMAIL_MEMO => $val
    			);
    			break;
    		default:
    			// 見つからないときは親クラスのconvertを使用
    			return SOYMailConverter::convert($value, $linkto);
    			break;
    	}
    }

	private function _zipcode($value){
		if(isset($value["zip"])){
			$zip = trim($value["zip"]);
			$zip = str_replace(array("-", "ー", " ", "　"), "", $zip);
			$zip1 = substr($zip, 0, 3);
			$zip2 = substr($zip, 3);
			return $zip1 . "-" . $zip2;
		}else{
			return trim($value["zip1"]) . "-" . trim($value["zip2"]);
		}
	}
}


class DateConverter extends SOYMailConverter {
    function convert($value, $linkto=SOYMailConverter::SOYMAIL_NONE) {
	    switch ($linkto) {
    		case SOYMailConverter::SOYMAIL_BIRTHDAY:
    			return array(
					SOYMailConverter::SOYMAIL_BIRTHDAY => mktime( 0, 0, 0, (int)$value["month"], (int)$value["day"], (int)$value["year"])
				);
    		default:
				if(!is_array($value)) $value = array();
    			return SOYMailConverter::convert(implode("/", $value), $linkto);
    	}
    }
}

class RadioConverter extends SOYMailConverter {
    function convert($value, $linkto=SOYMailConverter::SOYMAIL_NONE) {
	    switch ($linkto) {
				// TODO 決め打ちなので良くない
    		case SOYMailConverter::SOYMAIL_GENDER:
    			return array(
					SOYMailConverter::SOYMAIL_GENDER => (strpos("a".$value,"男")) ? 0 : 1
				);
    		default:
    			// 見つからないときは親クラスのconvertを使用
    			return SOYMailConverter::convert($value, $linkto);
    	}
    }
}

class CheckConverter extends SOYMailConverter {
    function convert($value, $linkto=SOYMailConverter::SOYMAIL_NONE) {
	    switch ($linkto) {
    		default:
    			if (isset($value)) {
	    			return SOYMailConverter::convert(implode(",",$value), $linkto);
    			} else {
					return null;
				}
    	}
    }
}
