<?php
class B2OutputCSV{

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
	private $deliveryTime = array(
		//指定なしの時は午前中にする
		"希望なし" => "0812",
		"指定なし" => "0812",
		"午前中" => "0812",
		"12時～14時" => "1214",
		"14時～16時" => "1416",
		"16時～18時" => "1618",
		"18時～20時" => "1820",
		"20時～21時" => "2021"
	);

	function __construct(){
		SOY2::import("module.plugins.b2_order_csv.util.B2OrderCsvUtil");
	}

	function getCSVLine($orderId, $slipNumber = null){

		$order = soyshop_get_order_object($orderId);
		if(is_null($order->getId())) return;

		$methodDelivery = $order->getAttributeList();

		$idx = B2OrderCsvUtil::getSelectedDeliveryMethod($order->getId());
		$deliveryValue = (strlen($idx) && isset($methodDelivery[$idx.".time"]["value"])) ? $methodDelivery[$idx.".time"]["value"] : "";
		$deliveryCode = (isset($this->deliveryTime[$deliveryValue])) ? "\"" . $this->deliveryTime[$deliveryValue]."\"" : "";

		//送付先を取得する
		$address = $order->getAddressArray();

		//ショップ情報を取得
		$company = B2OrderCsvUtil::getCompanyInfomation();

		//使ってない？
		// $itemOrders = B2OrderCsvUtil::getOrderItemsByOrderId($orderId);
		// $itemOrder = array_shift($itemOrders);

		//代引きか？
		$isDaibiki = B2OrderCsvUtil::isDaibiki($order->getId());

		$config = B2OrderCsvUtil::getConfig();

		$customerAddress = (isset($address["area"]) && strlen($address["area"])) ? $this->prefecture[$address["area"]].$address["address1"].$address["address2"].$address["address3"] : "";
		$customerReading = (isset($address["reading"])) ? mb_convert_kana($address["reading"],"k") : "";

		$csv = array();
		if(strlen($config["number"]) && $config["number"] == "##TRACKING_NUMBER##"){
			$customerNumber = $order->getTrackingNumber();
		}else{
			$customerNumber = $config["number"];
		}
		$csv[] = $customerNumber;												//お客様管理番号

		$csv[] = B2OrderCsvUtil::getInvoiceType($order->getId());								//送り状種類
		$csv[] = "";															//クール区分
		$csv[] = $slipNumber;													//伝票番号

		//出荷予定日
		$csv[] = (isset($config["auto_insert_shipping_date"]) && $config["auto_insert_shipping_date"] == 1) ? date("Y/m/d") : "";
		$csv[] = "";															//お届け予定日
		$csv[] = $deliveryCode;													//配達時間帯
		$csv[] = "";															//お届け先コード
		$csv[] = B2OrderCsvUtil::mbConvertKana($address["telephoneNumber"]);	//お届け先電話番号
		$csv[] = "";															//お届け先電話番号枝番

		$csv[] = B2OrderCsvUtil::removeHyphen($address["zipCode"]);				//お届け先郵便番号
		$csv[] = B2OrderCsvUtil::convertSpace($customerAddress);				//お届け先住所
		$csv[] = "";															//お届け先建物名
		$csv[] = "";															//お届け先会社・部門１
		$csv[] = "";															//お届け先会社・部門２

		$csv[] = B2OrderCsvUtil::convertSpace($address["name"]);				//お届け先名
		$csv[] = B2OrderCsvUtil::convertSpace($customerReading);				//お届け先名略称カナ
		$csv[] = "";															//敬称
		$csv[] = "";															//ご依頼主コード
		$csv[] = $company["telephone"];											//ご依頼主電話番号

		$csv[] = "";															//ご依頼主電話番号枝番
		$csv[] = $company["address1"];											//ご依頼主郵便番号
		$csv[] = $company["address2"];											//ご依頼主住所
		$csv[] = $company["building"];											//ご依頼主建物名
		$csv[] = $company["shop_name"];											//ご依頼主名

		$csv[] = "";															//ご依頼主名略称カナ
		$csv[] = "";															//品名コード１
		$csv[] = $config["name"];												//品名１
		$csv[] = "";															//品名コード２
		$csv[] = "";															//品名２

		$csv[] = "";															//荷扱い１
		$csv[] = "";															//荷扱い２
		$csv[] = "";															//記事
		$csv[] = ($isDaibiki) ? $order->getPrice() : "";						//コレクト代金引換額
		$csv[] = ($isDaibiki) ? 0 : "";											//コレクト内消費税

		$csv[] = "";															//営業所止置き
		$csv[] = "";															//営業所コード
		$csv[] = "";															//発行枚数
		$csv[] = 2;																//個数口枠の印字
		$csv[] = "";															//ご請求先顧客コード
		$csv[] = (isset($config["customer_code"])) ? "\"" . trim($config["customer_code"]) ."\"" : "";
		$csv[] = "";															//運賃管理番号

		//隠し機能　運賃管理番号よりも後に値を追加したい場合　詳しくは extends/readme.txtに記載
		$n = B2OrderCsvUtil::getExtInsertNumber();
		$csvLineCnt = count($csv);
		if($n > $csvLineCnt){
			for($i = $csvLineCnt + 1; $i <= $n; $i++){
				if(file_exists(B2OrderCsvUtil::getExtendDir() . $i . ".php")){
					include(B2OrderCsvUtil::getExtendDir() . $i . ".php");
				}else{
					$csv[] = "";
				}
			}
		}

		$line = implode(",",$csv);

		return $line;
	}

	function getLabels(){
		$label = array();
		$label[] = "お客様管理番号";
		$label[] = "送り状種類";
		$label[] = ""; //クール区分
		$label[] = ""; //伝票番号
		$label[] = "出荷予定日"; // YYYY/MM/DD

		$label[] = "お届け予定日"; // YYYY/MM/DD
		$label[] = "配達時間帯";
		$label[] = "お届け先コード"; //ここは空欄になってる
		$label[] = "お届け先電話番号"; //ハイフンを含む
		$label[] = "お届け先電話番号枝番";

		$label[] = "お届け先郵便番号"; //ハイフンなし可
		$label[] = "お届け先住所";
		$label[] = "お届け先建物名";
		$label[] = "お届け先会社・部門１";
		$label[] = "お届け先会社・部門２";

		$label[] = "お届け先名";
		$label[] = "お届け先名略称カナ";
		$label[] = ""; //敬称
		$label[] = "ご依頼主コード";
		$label[] = "ご依頼主電話番号";

		$label[] = "ご依頼主電話番号枝番";
		$label[] = "ご依頼主郵便番号";
		$label[] = "ご依頼主住所";
		$label[] = "ご依頼主建物名";
		$label[] = "ご依頼主名";

		$label[] = "ご依頼主名略称カナ";
		$label[] = "品名コード１";
		$label[] = "品名１";
		$label[] = "品名コード２";
		$label[] = "品名２";

		$label[] = "荷扱い１";
		$label[] = "荷扱い２";
		$label[] = "記事";
		$label[] = "コレクト代金引換額";
		$label[] = "コレクト内消費税";

		$label[] = "営業所止置き";
		$label[] = "営業所コード";
		$label[] = "発行枚数";
		$label[] = "個数口枠の印字";
		$label[] = "ご請求先顧客コード";
		$label[] = "ご請求先分類コード";

		$label[] = "運賃管理番号";

		//隠し機能　運賃管理番号よりも後に値を追加したい場合　詳しくは extends/readme.txtに記載
		$n = B2OrderCsvUtil::getExtInsertNumber();
		$labelLineCnt = (count($label));
		if($n > $labelLineCnt){
			for($i = $labelLineCnt + 1; $i <= $n; $i++){
				if(file_exists(B2OrderCsvUtil::getExtendDir() . $i . "_label.php")){
					include(B2OrderCsvUtil::getExtendDir() . $i . "_label.php");
				}else{
					$label[] = "";
				}
			}
		}

		return $label;
	}
}
