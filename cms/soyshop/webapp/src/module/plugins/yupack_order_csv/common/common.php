<?php
/*
 * Created on 2010/03/29
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */

SOY2::import("domain.config.SOYShop_ShopConfig");
class YupackOutputCSV{

	private $orderDAO;
	private $config;
	private $shopInfo;

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

	function getCSVLine($orderId){
		if(!$this->orderDAO)$this->orderDAO = SOY2DAOFactory::create("order.SOYShop_OrderDAO");
		try{
			$dao = $this->orderDAO;
			$order = $dao->getById($orderId);
		}catch(Exception $e){
			return;
		}


		//送付先を取得する
		$address = $order->getAddressArray();
		$addressPref = isset($this->prefecture[$address["area"]]) ? $this->prefecture[$address["area"]] : "" ;

		//配達希望時間帯を取得する
		$attributes = $order->getAttributeList();
		if(isset($attributes["delivery_normal.time"]["value"])){
			$deliveryTime = $this->convertDeliveryTime($attributes["delivery_normal.time"]["value"]);
			//"希望なし", "午前中", "12時～14時", "14時～16時", "16時～18時", "18時～21時"
		}else{

			//delivery_dummy_adminへの対応

			//配送方法を取得する
			$modules = $order->getModuleList();
			$index = "";
			foreach($modules as $key => $value){
				if(preg_match('/delivery.*/',$key)){
					$index = $key;
					break;
				}
			}
			if(isset($attributes[$index.".time"]["value"])){
				$deliveryTime = $this->convertDeliveryTime($attributes[$index.".time"]["value"]);
			}else{
				$deliveryTime = 99;
			}
		}

		//配達希望日

		//送付元情報
		$yupackConfig = $this->getConfig();


		//支払い方法を取得する
		$attributes = $order->getAttributeList();

		$csv = array();
		$csv[] = "";															//送り状種別
		$csv[] = "";															//出荷予定日
		$csv[] = "\"" . $this->convertZipcode($address["zipCode"])."\"";			//お届け先 郵便番号
		$csv[] = $addressPref;													//お届け先 住所
		$csv[] = $address["address1"];											//お届け先 住所
		$csv[] = $address["address2"].$address["address3"];						//お届け先 住所
		$csv[] = $address["name"];												//お届け先 おなまえ
		$csv[] = $address["reading"];											//お届け先 フリガナ
		$csv[] = 1;																//お届け先 敬称
		$csv[] = "\"" . $this->getAsciiNumber($address["telephoneNumber"])."\"";	//お届け先 電話番号

		$csv[] = "\"" . $this->convertZipcode($yupackConfig["zipcode"])."\"";			//ご依頼主 郵便番号
		$csv[] = $yupackConfig["address_pref"];											//ご依頼主 住所
		$csv[] = $yupackConfig["address1"];											//ご依頼主 住所
		$csv[] = $yupackConfig["address2"];											//ご依頼主 住所
		$csv[] = $yupackConfig["shop_name"];										//ご依頼主 おなまえ
		$csv[] = 9;																//ご依頼主 敬称
		$csv[] = $yupackConfig["telephone"];										//ご依頼主 電話番号
		$csv[] = $yupackConfig["mailaddress"];										//ご依頼主 メールアドレス
		$csv[] = $yupackConfig["object_name"];											//品名(商品名)
		$csv[] = "";															//配送希望日
		$csv[] = $deliveryTime;													//配送希望時間
		$csv[] = 0;																//運賃等
		$csv[] = "";															//その他
		$csv[] = $order->getPrice();											//合計

		$line = implode(",",$csv);


		return $line;
	}

	function getLabels(){
		$label = array();

		$label[] = "送り状種別";
		$label[] = "出荷予定日";
		$label[] = "お届け先 郵便番号";
		$label[] = "お届け先 住所";
		$label[] = "お届け先 住所２";
		$label[] = "お届け先 住所３";
		$label[] = "お届け先おなまえ";
		$label[] = "お届け先フリガナ";
		$label[] = "お届け先敬称";
		$label[] = "お届け先 電話番号";
		$label[] = "ご依頼主 郵便番号";
		$label[] = "ご依頼主 住所";
		$label[] = "ご依頼主 住所２";
		$label[] = "ご依頼主 住所３";
		$label[] = "ご依頼主 おなまえ";
		$label[] = "ご依頼主 敬称";
		$label[] = "ご依頼主 電話番号";
		$label[] = "ご依頼主 メールアドレス";
		$label[] = "品名(商品名)";
		$label[] = "配送希望日";
		$label[] = "配送希望時間";
		$label[] = "運賃等";
		$label[] = "その他";
		$label[] = "合計";

		return $label;
	}

	function getAsciiNumber($str){
		$str = mb_convert_kana($str,"rnhk");

		if(strpos($str,"ー"))$str = str_replace("ー","-",$str);
		if(strpos($str,"－"))$str = str_replace("－","-",$str);
		if(strpos($str,"ｰ"))$str = str_replace("ｰ","-",$str);

		return $str;
	}

	function convertZipcode($str){
		$str = $this->getAsciiNumber($str);
		if(strlen($str) == 7)$str = substr($str,0,3)."-".substr($str,3,4);
		return $str;
	}

	function splitAddress($address){

		preg_match("/^(北海道|東京都|大阪府|京都府|.+?県)(.*)$/u", $address, $matches);

		if(count($matches) <3){
			$pref = "";
			$address1 = $address;
			$address2 = "";
		}else{
			$pref     = $matches[1];
			$address1 = $matches[2];
			$address2 = "";
		}

		if(strpos($address1, " ") !== false){
			list($address1, $address2) = explode(" ", $address1);
		}elseif(strpos($address1, "　") !== false){
			list($address1, $address2) = explode("　", $address1);
		}

		if($address2 == ""){
			while(strlen($address1) > 50){
				$tmp = mb_substr($address1,0,mb_strlen($address1)-1);
				$address2 = mb_substr($address1,mb_strlen($address1)-1) . $address2;
				$address1 = $tmp;
			}
		}

		return array($pref, $address1, $address2);
	}

	function convertDeliveryTime($value){
		if(strpos($value, "～") !== false){
			$value = str_replace("～", "-", $value);
		}

		switch($value){
			case "希望なし":
				$flag = 99;
				break;
			case "午前中":
				$flag = 60;
				break;
			case "12-14時":
				$flag = 62;
				break;
			case "14-16時":
				$flag = 63;
				break;
			case "16-18時":
				$flag = 64;
				break;
			case "18-20時":
			case "18-21時":
				$flag = 65;//18-20
				break;
			case "20-21時":
				$flag = 66;
				break;
			default:
				$flag = 67;
				break;
		}

		return $flag;
	}

	public function saveConfig($config){
		$config = $this->alignValue($config);
		SOYShop_DataSets::put("yupack_order_csv", $config);
	}

	private function alignValue($config){
		if(isset($config["zipcode"]))   $config["zipcode"] = $this->convertZipcode($config["zipcode"]);
		if(isset($config["telephone"])) $config["telephone"] = $this->getAsciiNumber($config["telephone"]);
		if(isset($config["fax"]))       $config["fax"] = $this->getAsciiNumber($config["fax"]);
		return $config;
	}

	public function getConfig(){
		//初期値
		$config = SOYShop_ShopConfig::load();
		$shopName = $config->getShopName();
		$companyInfo = $config->getCompanyInformation();
		list($pref,$address1,$address2) = $this->splitAddress($companyInfo["address2"]);
		$default = array(
				"object_name" => "",
				"shop_name" => $shopName,
				"zipcode" => $companyInfo["address1"],
				"address_pref" => $pref,
				"address1" => $address1,
				"address2" => $address2,
				"telephone" => $companyInfo["telephone"],
				"fax" => $companyInfo["fax"],
				"mailaddress" => $companyInfo["mailaddress"],
		);

		//保存値
		$value = SOYShop_DataSets::get("yupack_order_csv", $default);

		//キーが増えた場合の対処
		if(count($default) != count($value)){
			$value = array_merge($default, $value);
		}

		$value = $this->alignValue($value);

		return $value;
	}
}
