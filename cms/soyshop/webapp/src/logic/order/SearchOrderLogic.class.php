<?php

class SearchOrderLogic extends SOY2LogicBase{

	public static function getInstance($className,$args){
		//import
		SOY2DAOFactory::create("order.SOYShop_OrderDAO");
		SOY2DAOFactory::create("order.SOYShop_ItemOrderDAO");

		return parent::getInstance($className,$args);
	}

	private $table;
	private $limit;
	private $offset;
	private $order;
	private $where = array();
	private $binds = array();

	private $sorts = array(
		"order_date" =>  "order_date",
		"order_date_desc" =>  "order_date desc",
	);

	/**
	 * 合計件数取得用のSQL生成
	 */
	protected function getCountSQL(){
		$table = $this->getTable();

		$countSql = "select count(*) as count from " . $table . " ";
		if(count($this->where) > 0){
			$countSql .= " where ".implode(" and ", $this->where);
		}
		return $countSql;
	}

	/**
	 * 検索用のSQL生成
	 */
	protected function getSearchSQL(){
		$table = $this->getTable();

		$sql = "select ".SOYShop_Order::getTableName() . ".*"." from " . $table . " ";
		if(count($this->where) > 0){
			$sql .= " where ".implode(" and ", $this->where);
		}
		if(strlen($this->order)) $sql .= $this->order;
		return $sql;
	}

	/**
	 * 検索条件からSQLを構築
	 */
	function setSearchCondition($search){
		$table = $this->getTable();
		$where = array();
		$binds = array();

		if(isset($search["userId"]) && strlen($search["userId"]) > 0){
			$where[] = "user_id = :user_id";
			$binds[":user_id"] = (int)$search["userId"];
		}

		if(isset($search["userArea"]) && strlen($search["userArea"]) > 0){
			if(!class_exists("SOYShop_User"))SOY2::import("domain.SOYShop_User");
			$where[] = "user_id in (select id from ". SOYShop_User::getTableName() ." where area LIKE :user_area)";
			$binds[":user_area"] = (int)$search["userArea"];
		}

		if(isset($search["noDelivery"]) && $search["noDelivery"] == 1){
			$where[] = "order_status in (".SOYShop_Order::ORDER_STATUS_REGISTERED.",".SOYShop_Order::ORDER_STATUS_RECEIVED.")";
		}else if(isset($search["orderStatus"])){
			//注文状態を配列で渡す場合(チェックボックス式)
			if(is_array($search["orderStatus"]) && count($search["orderStatus"])){
				$where[] = "order_status IN (" . implode(",", $search["orderStatus"]) . ")";

			//注文状態を文字列で渡す場合(セレクトボックス式)
			}else if(is_string($search["orderStatus"]) && strlen($search["orderStatus"]) > 0){
				$where[] = "order_status = :order_status";
				$binds[":order_status"] = (int)$search["orderStatus"];
			}
		}else{
			//注文一覧でキャンセルを含むか？
			SOY2::import("domain.config.SOYShop_ShopConfig");
			if(SOYShop_ShopConfig::load()->getDisplayCancelOrder()){
				$where[] = "order_status not in (".SOYShop_Order::ORDER_STATUS_INTERIM.")";
			}else{
				$where[] = "order_status not in (".SOYShop_Order::ORDER_STATUS_INTERIM.",".SOYShop_Order::ORDER_STATUS_CANCELED.")";
			}
		}

		if(isset($search["noPayment"]) && $search["noPayment"] == 1){
			$where[] = "payment_status in (".SOYShop_Order::PAYMENT_STATUS_WAIT.",".SOYShop_Order::PAYMENT_STATUS_ERROR.",".SOYShop_Order::PAYMENT_STATUS_DIRECT.")";
		}else if(isset($search["paymentStatus"])){
			//支払い状況を配列で渡す場合(チェックボックス式)
			if(is_array($search["paymentStatus"]) && count($search["paymentStatus"])){
				$where[] = "payment_status IN (" . implode(",", $search["paymentStatus"]) . ")";

			//支払い状況を文字列で渡す場合(セレクトボックス式)
			}else if(is_string($search["paymentStatus"]) && strlen($search["paymentStatus"]) > 0){
				$where[] = "payment_status = :payment_status";
				$binds[":payment_status"] = (int)$search["paymentStatus"];
			}
		}

		//合計金額
		if(isset($search["totalPriceMin"]) && (int)$search["totalPriceMin"] > 0){
			$where[] = "price >= :total_price_min";
			$binds[":total_price_min"] = (int)$search["totalPriceMin"];
		}

		if(isset($search["totalPriceMax"]) && (int)$search["totalPriceMax"] > 0){
			$where[] = "price <= :total_price_max";
			$binds[":total_price_max"] = (int)$search["totalPriceMax"];
		}

		if(isset($search["orderDateStart"]) && strlen($search["orderDateStart"]) > 0 && strtotime($search["orderDateStart"])){
			$where[] = "order_date >= :order_date_start";
			$binds[":order_date_start"] = strtotime($search["orderDateStart"]);
		}

		if(isset($search["orderDateEnd"]) && strlen($search["orderDateEnd"]) > 0 && strtotime($search["orderDateEnd"])){
			$where[] = "order_date <= :order_date_end";
			$order_date_end_time = strtotime($search["orderDateEnd"]);
			if(date("H:i:s", $order_date_end_time) === "00:00:00"){
				$order_date_end_time = strtotime($search["orderDateEnd"]." 23:59:59");
			}
			$binds[":order_date_end"] = $order_date_end_time;
		}

		//備考　SQLだけでは取得できないのでデータを取得直後に更に加工を行う
		if(isset($search["orderMemo"]) && strlen($search["orderMemo"])){
			//検索ワードをスペースやカンマで配列にする
			$words = self::getOrderMemoWords($search["orderMemo"]);
			if(count($words)){
				$memoWhere = array();
				for($i = 0; $i < count($words); $i++){
					$word = $words[$i];
					$memoWhere[] = "attributes LIKE :order_memo_" . $i;
					$binds[":order_memo_" . $i] = "%" . $word . "%";
				}

				if(count($memoWhere)){
					$cnd = ((int)$search["orderMemoAndOr"] === 1) ? " OR " : " AND ";
					$where[] = "(" . implode($cnd, $memoWhere) . ")";
				}
			}
		}

		//コメント
		if(isset($search["orderComment"]) && strlen($search["orderComment"])){
			//検索ワードをスペースやカンマで配列にする
			$words = self::getOrderMemoWords($search["orderComment"]);
			if(count($words)){
				$commentWhere = array();
				for($i = 0; $i < count($words); $i++){
					$word = $words[$i];
					$commentWhere[] = "content LIKE :comment_" . $i;
					$binds[":comment_" . $i] = "%" . $word . "%";
				}

				if(count($commentWhere)){
					$cnd = ((int)$search["orderCommentAndOr"] === 1) ? " OR " : " AND ";
					$where[] = "id IN (SELECT order_id FROM soyshop_order_state_history WHERE ". implode($cnd, $commentWhere) . ")";
				}
			}
		}

		//更新日
		if(isset($search["updateDateStart"]) && strlen($search["updateDateStart"]) > 0 && strtotime($search["updateDateStart"])){
			$where[] = "id IN (SELECT order_id FROM soyshop_order_state_history WHERE order_date >= :update_date_start)";
			$binds[":update_date_start"] = strtotime($search["updateDateStart"]);
		}

		if(isset($search["updateDateEnd"]) && strlen($search["updateDateEnd"]) > 0 && strtotime($search["updateDateEnd"])){
			$where[] = "id IN (SELECT order_id FROM soyshop_order_state_history WHERE order_date <= :update_date_end)";
			$update_date_end_time = strtotime($search["updateDateEnd"]);
			if(date("H:i:s", $update_date_end_time) === "00:00:00"){
				$update_date_end_time = strtotime($search["updateDateEnd"]." 23:59:59");
			}
			$binds[":update_date_end"] = $update_date_end_time;
		}

		if(isset($search["trackingNumber"]) && strlen($search["trackingNumber"]) > 0){
			$where[] = "tracking_number LIKE :tracking_number";
			$binds[":tracking_number"] = "%" . @$search["trackingNumber"] . "%";
		}
		if(isset($search["orderId"]) && strlen($search["orderId"]) > 0){
			$where[] = "id LIKE :order_id";
			$binds[":order_id"] = "%" . $search["orderId"] . "%";
		}

		if(isset($search["orderIdStart"]) && strlen($search["orderIdStart"]) > 0){
			$where[] = "id >= :order_id_start";
			$binds[":order_id_start"] = $search["orderIdStart"];
		}

		if(isset($search["orderIdEnd"]) && strlen($search["orderIdEnd"]) > 0){
			$where[] = "id <= :order_id_end";
			$binds[":order_id_end"] = $search["orderIdEnd"];
		}

		if(isset($search["userName"]) && strlen($search["userName"]) > 0){
			if(!class_exists("SOYShop_User")) SOY2::import("domain.SOYShop_User");
			$where[] = "user_id in (select id from ". SOYShop_User::getTableName() ." where name LIKE :user_name)";
			$binds[":user_name"] = "%" . $search["userName"] . "%";
		}

		if(isset($search["userReading"]) && strlen($search["userReading"]) > 0){
			if(!class_exists("SOYShop_User"))SOY2::import("domain.SOYShop_User");

			//全角カナであろうデータ
			$katakana = mb_convert_kana($search["userReading"],"c");
			$hiragana = mb_convert_kana($search["userReading"],"C");

			//SQLiteで文字列の全角半角を無視して検索する関数が見つからないので、一つ一つ丁寧にSQL構文を発行する
			$where[] = "user_id in (select id from ". SOYShop_User::getTableName().	" where " .
					"reading LIKE :user_reading_c OR " .
					"reading LIKE :user_reading_C OR " .
					"reading LIKE :user_reading_k" .
					")";
			$binds[":user_reading_c"] = "%" . $katakana . "%";
			$binds[":user_reading_C"] = "%" . $hiragana . "%";
			$binds[":user_reading_k"] = "%" . mb_convert_kana($hiragana,"k") . "%";
		}

		if(isset($search["userMailAddress"]) && strlen($search["userMailAddress"]) > 0){
			if(!class_exists("SOYShop_User"))SOY2::import("domain.SOYShop_User");
			$where[] = "user_id in (select id from ". SOYShop_User::getTableName() ." where mail_address LIKE :mail_address)";
			$binds[":mail_address"] = "%" . $search["userMailAddress"] . "%";
		}

		if(isset($search["userGender"]) && count($search["userGender"])){
			if(!class_exists("SOYShop_User"))SOY2::import("domain.SOYShop_User");
			$where[] = "user_id in (select id from ". SOYShop_User::getTableName() ." where gender IN (" . implode(",", $search["userGender"]) . "))";
		}

		if(isset($search["userBirthday"]) && count($search["userBirthday"])){
			$birthArray = $search["userBirthday"];
			$birth_where = array();
			//年
			if(isset($birthArray[0]) && strlen($birthArray[0])){
				$birth_where[] = " birthday LIKE :birthday_year ";
				$binds[":birthday_year"] = (int)trim($birthArray[0]) . "-%";
			}
			if(isset($birthArray[1]) && strlen($birthArray[1])){
				$m = trim($birthArray[1]);
				if($m[0] == "0") $m = (int)substr($m, 1);
				//1〜9までの場合
				if(strlen($m) === 1){
					$birth_where[] = " (birthday LIKE :birthday_month OR birthday LIKE :birthday_month1 )";
					$binds[":birthday_month1"] = "%-0" . $m . "-%";
				//10〜12の場合
				}else{
					$birth_where[] = " birthday LIKE :birthday_month ";
				}
				$binds[":birthday_month"] = "%-" . $m . "-%";
			}
			if(isset($birthArray[2]) && strlen($birthArray[2])){
				$d = trim($birthArray[2]);
				if($d[0] == "0") $d = (int)substr($d, 1);
				//1〜9までの場合
				if(strlen($d) === 1){
					$birth_where[] = " (birthday LIKE :birthday_day OR birthday LIKE :birthday_day1 )";
					$binds[":birthday_day1"] = "%-0" . $d;
				//10〜31の場合
				}else{
					$birth_where[] = " birthday LIKE :birthday_day ";
				}
				$binds[":birthday_day"] = "%-" . $d;
			}

			if(count($birth_where)){
				$where[] = "user_id in (select id from ". SOYShop_User::getTableName() ." where " . implode(" AND ", $birth_where) . ")";
			}
		}

		if(
			(isset($search["itemName"]) && strlen($search["itemName"])) > 0 ||
			(isset($search["itemCode"]) && strlen($search["itemCode"]) > 0)
		){
			SOY2DAOFactory::importEntity("shop.SOYShop_Item");

			$table .= " inner join " . SOYShop_ItemOrder::getTableName();
			$table .= " on (".SOYShop_Order::getTableName() . ".id = ".SOYShop_ItemOrder::getTableName() . ".order_id) ";
			$table .= " inner join " . SOYShop_Item::getTableName();
			$table .= " on (".SOYShop_Item::getTableName() . ".id = ".SOYShop_ItemOrder::getTableName() . ".item_id)";


			if(isset($search["itemName"]) && strlen($search["itemName"])){
				$where[] = SOYShop_Item::getTableName() . ".item_name like :item_name";
				$binds[":item_name"] = "%" . $search["itemName"] . "%";
			}

			if(isset($search["itemCode"]) && strlen($search["itemCode"])){
				$where[] = SOYShop_Item::getTableName() . ".item_code like :item_code";
				$binds[":item_code"] = "%" . $search["itemCode"] . "%";
			}
		}

		//支払方法
		if(isset($search["paymentMethod"]) && count($search["paymentMethod"])){
			$attr_where = array();
			foreach($search["paymentMethod"] as $p){
				$attr_where[] = "attributes LIKE '%" . $p . "%'";
			}
			$where[] = implode(" OR ", $attr_where);
		}

		//拡張ポイントから出力したフォーム用
		SOYShopPlugin::load("soyshop.order.search");
		$queries = SOYShopPlugin::invoke("soyshop.order.search", array(
			"mode" => "search",
			"params" => (isset($search["customs"])) ? $search["customs"] : array()
		))->getQueries();

		foreach($queries as $moduleId => $values){
			if(is_null($values["queries"]) || !count($values["queries"])) continue;
			$where = array_merge($where, $values["queries"]);
			if(isset($values["binds"])) $binds = array_merge($binds, $values["binds"]);
		}

		$this->where = $where;
		$this->binds = $binds;
		$this->table = $table;
	}

	/**
	 * マイページの購入履歴一覧で使う
	 */
	public function setSearchConditionForMyPage($userId, $search = array()){
		$this->setSearchCondition($search);

		//現在のユーザーの注文のみ
		$this->where[] = SOYShop_Order::getTableName() . ".user_id = :mypage_user_id";
		$this->binds[":mypage_user_id"] = $userId;
	}

	/**
	 * 検索する
	 */
	function getOrders(){

		SOY2DAOConfig::setOption("limit_query", true);
		$orderDAO = SOY2DAOFactory::create("order.SOYShop_OrderDAO");
		$orderDAO->setLimit($this->getLimit());
		$orderDAO->setOffset($this->getOffset());

		try{
			$res = $orderDAO->executeQuery($this->getSearchSQL(),$this->getBinds());
		}catch(Exception $e){
			return array();
		}

		$orders = array();
		$itemOrderDAO = SOY2DAOFactory::create("order.SOYShop_ItemOrderDAO");
		foreach($res as $row){
			if(!isset($row["id"])) continue;
			$obj = $orderDAO->getObject($row);

			//備考の検索を行う
			if(isset($_GET["search"]["orderMemo"]) && strlen($_GET["search"]["orderMemo"])){
				$words = self::getOrderMemoWords($_GET["search"]["orderMemo"]);
				if(count($words)){
					$attrs = $obj->getAttributeList();
					if(!isset($attrs["memo"]) || !strlen($attrs["memo"]["value"])) continue;
					$memo = $attrs["memo"];

					$hit = false;
					foreach($words as $word){
						if(strpos($memo["value"], $word) !== false) $hit = true;
					}
					if(!$hit) continue;
				}
			}

			$obj->setItems($itemOrderDAO->getByOrderId($obj->getId()));
			$orders[] = $obj;
		}

		return $orders;
	}

	function getSorts(){
		return $this->sorts;
	}

	/**
	 * 合計件数を取得
	 */
	function getTotalCount(){
		$countSql = $this->getCountSQL();
		$dao = new SOY2DAO();
		try{
			$countResult = $dao->executeQuery($countSql, $this->binds);
		}catch(Exception $e){
			return 0;
		}
		return (isset($countResult[0]["count"])) ? (int)$countResult[0]["count"] : 0;
	}

	/**
	 * 備考用の検索ワードの配列を取得
	 */
	function getOrderMemoWords($str){
		static $array;
		if(isset($array)) return $array;
		$array = array();

		$str = str_replace(array(" ", "　"), ",", trim($str));
		$strs = explode(",", $str);

		if(!count($strs)) return $array;

		for($i = 0; $i < count($strs); $i++) {
			$str = trim($strs[$i]);
			if(!isset($str) || !strlen($str)) continue;
			$array[] = $str;
		}

		return $array;
	}

	/* getter setter */

	function getTable() {
		if(strlen($this->table) < 1)$this->table = SOYShop_Order::getTableName();
		return $this->table;
	}
	function setTable($table) {
		$this->table = $table;
	}
	function getLimit() {
		return $this->limit;
	}
	function setLimit($limit) {
		$this->limit = $limit;
	}
	function getOffset() {
		return $this->offset;
	}
	function setOffset($offset) {
		$this->offset = $offset;
	}
	function getOrder() {
		return $this->order;
	}
	function setOrder($order){
		if(isset($this->sorts[$order])){
			$order = $this->sorts[$order];
			$order = str_replace("_desc"," desc",$order);
		}else{
			$order = "order_date desc";
		}
		$this->order = " order by " . $order;

	}
	function getWhere() {
		return $this->where;
	}
	function setWhere($where) {
		$this->where = $where;
	}
	function getBinds() {
		return $this->binds;
	}
	function setBinds($binds) {
		$this->binds = $binds;
	}
}
