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
	function export(array $orders){
		set_time_limit(0);
		$lines = array();

		SOYShopPlugin::load("soyshop.user.customfield");
    	$fieldIdList = SOYShopPlugin::invoke("soyshop.user.customfield", array("mode" => "order_csv"))->getList();
		if(!is_array($fieldIdList)) $fieldIdList = array();
    		
		$itemCnt = 0;
		foreach($orders as $order){
			$user = soyshop_get_user_object($order->getId());

			$line = array();
			$line[] = $order->getTrackingNumber();						//注文番号
			$line[] = date("Y-m-d H:i:s", $order->getOrderDate());		//注文時刻

			$claimedCustomer = $order->getClaimedAddressArray();
			$line[] = $claimedCustomer["name"];							//顧客名
			$line[] = $user->getMailAddress();							//メールアドレス

			$tel = (isset($claimedCustomer["telephoneNumber"])) ? trim($claimedCustomer["telephoneNumber"]) : "";
			$line[] = (strlen($tel)) ? "=\"" . $tel . "\"" : "";		//電話番号

			// ユーザーカスタムフィールド
			if(count($fieldIdList)){
				$fieldValues = self::_getUserAttributeValues($fieldIdList, (int)$order->getUserId());
				foreach($fieldValues as $fieldValue){
					$line[] = $fieldValue;
				}
			}
			
			$line[] = "\"" . number_format($order->getPrice()) . "\"";	//合計金額

			$_cnt = count($order->getItems());
			if($itemCnt < $_cnt) $itemCnt = $_cnt;
		
			//ここからはひたすら注文を出力する
			foreach($order->getItems() as $itemOrder){
				$item = self::getItemById($itemOrder->getItemId());
				$line[] = $itemOrder->getItemName();
				$line[] = $item->getCode();
				$line[] = $itemOrder->getItemCount();
				$line[] = $itemOrder->getTotalPrice();
			}

			$lines[] = implode(",", $line);
		}

		$charset = (isset($_REQUEST["charset"])) ? $_REQUEST["charset"] : "Shift-JIS";

		header("Cache-Control: public");
		header("Pragma: public");
    	header("Content-Disposition: attachment; filename=order_" . date("YmdHis"). ".csv");
		header("Content-Type: text/csv; charset=" . htmlspecialchars($charset) . ";");

		ob_start();
		echo self::_labels($fieldIdList, $itemCnt);
		echo "\r\n";
		echo implode("\r\n",$lines);
		$csv = ob_get_contents();
		ob_end_clean();

		echo mb_convert_encoding($csv,$charset,"UTF-8");
		exit;	//csv output
	}

	/**
	 * @param array
	 * @param int
	 * @return array
	 */
	private function _getUserAttributeValues(array $fieldIdList, int $userId){
		//メモリの節約
		static $_arr;
		if(!is_array($_arr)) $_arr = array();
		if(isset($_arr[$userId])) return $_arr[$userId];
	
		$fieldIds = array_keys($fieldIdList);
		
		try{
			$res = soyshop_get_hash_table_dao("user_attribute")->executeQuery(
				"SELECT user_field_id, user_value FROM soyshop_user_attribute WHERE user_id = :userId AND user_field_id IN (\"".implode("\",\"", $fieldIds)."\")",
				array(":userId" => $userId)
			);
		}catch(Exception $e){
			$res = array();
		}

		foreach($fieldIds as $fieldId){
			$_arr[$userId][$fieldId] = "";
			if(!count($res)) continue;

			foreach($res as $v){
				if($fieldId == $v["user_field_id"]){
					$_arr[$userId][$fieldId] = $v["user_value"];
				}
			}
		}
		return $_arr[$userId];
	}

	/**
	 * @param int
	 * @return SOYShop_Item
	 */
	private function getItemById(int $itemId){
		static $items;
		if(is_null($items)) $items = array();
		if(isset($items[$itemId])) return $items[$itemId];

		$dao = soyshop_get_hash_table_dao("item");
	
		//メモリの節約のため、取得するカラムを制限する
		try{
			$res = $dao->executeQuery(
				"SELECT item_code FROM soyshop_item WHERE id = :itemId",
				array(":itemId" => $itemId)
			);
		}catch(Exception $e){
			$res = array();
		}

		$items[$itemId] = (isset($res[0])) ? $dao->getObject($res[0]) : new SOYShop_Item();
		return $items[$itemId];
	}

	/**
	 * @param array
	 * @param int
	 * @return string
	 */
	private function _labels(array $fieldIdList, int $cnt){
		$labels = array();
		$labels[] = "注文番号";
		$labels[] = "注文日時";
		$labels[] = "顧客名";
		$labels[] = "メールアドレス";
		$labels[] = "電話番号";

		if(count($fieldIdList)){
			foreach($fieldIdList as $label){
				$labels[] = $label;
			}
		}
		
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
}

SOYShopPlugin::extension("soyshop.order.export","order_csv","OrderCSV");
