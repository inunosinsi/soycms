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
		
		$yupackConfig = $this->getConfig();
		
		//送付先を取得する
		$address = $order->getAddressArray();
		$orderAddress = $this->prefecture[$address["area"]]." " . $address["address1"].$address["address2"];
		
		if(!$this->config)$this->config = SOYShop_ShopConfig::load();
		$config = $this->config;
		
		if(!$this->shopInfo)$this->shopInfo = $config->getCompanyInformation();
		$shopInfo = $this->shopInfo;

		//配送方法を取得する
		$modules = $order->getModuleList();		
		$index = "";
		foreach($modules as $key => $value){
			if(preg_match('/delivery.*/',$key)){
				$index = $key;
				break;
			}
		}
		
		//支払い方法を取得する
		$attributes = $order->getAttributeList();
		if(isset($attributes[$index.".time"]["value"])){
			$deliveryTime = $this->convertDeliveryTime($attributes[$index.".time"]["value"]);
		}else{
			$deliveryTime = 99;
		}
		

		//支払い方法を取得する
		$attributes = $order->getAttributeList();
		
		$csv = array();
		$csv[] = "";															//送り状種別
		$csv[] = "";															//出荷予定日
		$csv[] = "\"" . $this->mbConvertKana($address["zipCode"])."\"";			//お届け先 郵便番号
		$csv[] = $orderAddress;													//お届け先 住所
		$csv[] = $address["name"];												//お届け先 おなまえ
		$csv[] = $address["reading"];											//お届け先 フリガナ
		$csv[] = 1;																//お届け先 敬称
		$csv[] = "\"" . $this->mbConvertKana($address["telephoneNumber"])."\"";	//お届け先 電話番号
		$csv[] = "\"" . $this->mbConvertKana($shopInfo["address1"])."\"";			//ご依頼主 郵便番号
		$csv[] = $shopInfo["address2"];											//ご依頼主 住所
		$csv[] = $config->getShopName();										//ご依頼主 おなまえ
		$csv[] = 9;																//ご依頼主 敬称
		$csv[] = $shopInfo["telephone"];										//ご依頼主 電話番号
		$csv[] = $shopInfo["mailaddress"];										//ご依頼主 メールアドレス
		$csv[] = $yupackConfig["name"];											//品名(商品名)
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
		$label[] = "お届け先おなまえ";
		$label[] = "お届け先フリガナ";
		$label[] = "お届け先敬称";
		$label[] = "お届け先 電話番号";
		$label[] = "ご依頼主 郵便番号";
		$label[] = "ご依頼主 住所";
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
	
	function mbConvertKana($str){
		$str = mb_convert_kana($str,"r");
		$str = mb_convert_kana($str,"n");
		$str = mb_convert_kana($str,"h");
		$str = mb_convert_kana($str,"k");
		
		if(strpos($str,"ー"))$str = str_replace("ー","-",$str);
		if(strpos($str,"－"))$str = str_replace("－","-",$str);
		if(strpos($str,"ｰ"))$str = str_replace("ｰ","-",$str);
		
		//郵便番号対策
		if(strlen($str) == 7)$str = substr($str,0,3)."-".substr($str,3,4);
		
		return $str;
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
				$flag = 65;
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
	
	function getConfig(){
		return SOYShop_DataSets::get("yupack_order_csv", array(
				"name" => ""
		));
	}
}
?>