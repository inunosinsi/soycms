<?php

class OrderCSV extends SOYShopOrderExportBase{

	/**
	 * 検索結果一覧に表示するメニューの表示文言
	 */
	function getMenuTitle(){
		return "注文CSV出力";
	}

	/**
	 * 検索結果一覧に表示するメニューの説明
	 */
	function getMenuDescription(){
		return '注文のCSVを出力します。&nbsp;&nbsp;(<strong>文字コード=</strong>
			<input id="charset_shit_jis" type="radio" name="charset" value="Shift-JIS" />
			<label for="charset_shit_jis">Shift-JIS</label>
			<input id="charset_utf_8" type="radio" name="charset" value="UTF-8" />
			<label for="charset_utf_8">UTF-8</label>
		)';
	}

	/**
	 * export エクスポート実行
	 */
	function export($orders){

		set_time_limit(0);
		$lines = array();

		foreach($orders as $order){
			$user = soyshop_get_user_object($order->getId());

			$line = array();
			$line[] = $order->getTrackingNumber();						//注文番号
			$line[] = date("Y-m-d H:i:s", $order->getOrderDate());		//注文時刻

			$claimedCustomer = $order->getClaimedAddressArray();
			$line[] = $claimedCustomer["name"];							//顧客名
			$line[] = $user->getMailAddress();							//メールアドレス

			$tel = trim($claimedCustomer["telephoneNumber"]);
			$line[] = (strlen($tel)) ? "=\"" . $tel . "\"" : "";		//電話番号
			$line[] = "\"" . number_format($order->getPrice()) . "\"";	//合計金額

			//ここからはひたすら注文を出力する
			$cnt = 0;
			foreach($order->getItems() as $itemOrder){
				$item = self::getItemById($itemOrder->getItemId());
				$line[] = $itemOrder->getItemName();
				$line[] = $item->getCode();
				$line[] = $itemOrder->getItemCount();
				$line[] = $itemOrder->getTotalPrice();
				$cnt++;
			}

			$lines[] = implode(",", $line);
		}

		$charset = (isset($_REQUEST["charset"])) ? $_REQUEST["charset"] : "Shift-JIS";

		header("Cache-Control: public");
		header("Pragma: public");
    	header("Content-Disposition: attachment; filename=order_" . date("YmdHis"). ".csv");
		header("Content-Type: text/csv; charset=" . htmlspecialchars($charset) . ";");

		ob_start();
		echo self::getLabels($cnt);
		echo "\r\n";
		echo implode("\r\n",$lines);
		$csv = ob_get_contents();
		ob_end_clean();

		echo mb_convert_encoding($csv,$charset,"UTF-8");

		exit;	//csv output
	}

	private function getItemById($itemId){
		static $items;
		if(is_null($items)) $items = array();

		if(!isset($items[$itemId])){
			//メモリの節約のため、取得するカラムを制限する
			$sql = "SELECT item_code FROM soyshop_item WHERE id = :itemId";
			try{
				$res = self::itemDao()->executeQuery($sql, array(":itemId" => $itemId));
			}catch(Exception $e){
				$res = array();
			}

			$item = (isset($res[0])) ? self::itemDao()->getObject($res[0]) : new SOYShop_Item();
			$items[$itemId] = $item;
		}

		return $items[$itemId];
	}

	private function getLabels($cnt){
		$labels = array();
		$labels[] = "注文番号";
		$labels[] = "注文日時";
		$labels[] = "顧客名";
		$labels[] = "メールアドレス";
		$labels[] = "電話番号";
		$labels[] = "合計金額";

		//最後は注文内容の羅列
		for($i = 0; $i < $cnt; $i++){
			$labels[] = "商品名";
			$labels[] = "商品コード";
			$labels[] = "数量";
			$labels[] = "金額";
		}


		return implode(",", $labels);
	}

	private function itemDao(){
		static $dao;
		if(is_null($dao)) $dao = SOY2DAOFactory::create("shop.SOYShop_ItemDAO");
		return $dao;
	}
}

SOYShopPlugin::extension("soyshop.order.export","order_csv","OrderCSV");
