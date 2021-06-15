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

		$sql = "select DISTINCT ".SOYShop_Order::getTableName() . ".*"." from " . $table . " ";
		if(count($this->where) > 0){
			$sql .= " where ".implode(" and ", $this->where);
		}
		if(strlen($this->order)) $sql .= $this->order;
		return $sql;
	}

	/**
	 * 検索条件からSQLを構築
	 */
	function setSearchCondition($cnds){
		$table = $this->getTable();
		$where = array();
		$binds = array();

		if(isset($cnds["userId"]) && strlen($cnds["userId"]) > 0){
			$where[] = "user_id = :user_id";
			$binds[":user_id"] = (int)$cnds["userId"];
		}

		if(isset($cnds["userArea"]) && strlen($cnds["userArea"]) > 0){
			if(!class_exists("SOYShop_User"))SOY2::import("domain.SOYShop_User");
			$where[] = "user_id in (select id from ". SOYShop_User::getTableName() ." where area LIKE :user_area)";
			$binds[":user_area"] = (int)$cnds["userArea"];
		}

		if(isset($cnds["noDelivery"]) && $cnds["noDelivery"] == 1){
			$where[] = "order_status in (".SOYShop_Order::ORDER_STATUS_REGISTERED.",".SOYShop_Order::ORDER_STATUS_RECEIVED.")";
		}else if(isset($cnds["orderStatus"])){
			//注文状態を配列で渡す場合(チェックボックス式)
			if(is_array($cnds["orderStatus"]) && count($cnds["orderStatus"])){
				$where[] = "order_status IN (" . implode(",", $cnds["orderStatus"]) . ")";

			//注文状態を文字列で渡す場合(セレクトボックス式)
			}else if(is_string($cnds["orderStatus"]) && strlen($cnds["orderStatus"]) > 0){
				$where[] = "order_status = :order_status";
				$binds[":order_status"] = (int)$cnds["orderStatus"];
			}
		}else{
			//注文一覧でキャンセルを含むか？
			SOY2::import("domain.config.SOYShop_ShopConfig");
			if(SOYShop_ShopConfig::load()->getDisplayCancelOrder()){
				$where[] = "order_status not in (".SOYShop_Order::ORDER_STATUS_INTERIM.",".SOYShop_Order::ORDER_STATUS_INVALID.")";
			}else{
				$where[] = "order_status not in (".SOYShop_Order::ORDER_STATUS_INTERIM.",".SOYShop_Order::ORDER_STATUS_INVALID.",".SOYShop_Order::ORDER_STATUS_CANCELED.")";
			}
		}

		if(isset($cnds["noPayment"]) && $cnds["noPayment"] == 1){
			$where[] = "payment_status in (".SOYShop_Order::PAYMENT_STATUS_WAIT.",".SOYShop_Order::PAYMENT_STATUS_ERROR.",".SOYShop_Order::PAYMENT_STATUS_DIRECT.")";
		}else if(isset($cnds["paymentStatus"])){
			//支払い状況を配列で渡す場合(チェックボックス式)
			if(is_array($cnds["paymentStatus"]) && count($cnds["paymentStatus"])){
				$where[] = "payment_status IN (" . implode(",", $cnds["paymentStatus"]) . ")";

			//支払い状況を文字列で渡す場合(セレクトボックス式)
			}else if(is_string($cnds["paymentStatus"]) && strlen($cnds["paymentStatus"]) > 0){
				$where[] = "payment_status = :payment_status";
				$binds[":payment_status"] = (int)$cnds["paymentStatus"];
			}
		}

		//合計金額
		if(isset($cnds["totalPriceMin"]) && (int)$cnds["totalPriceMin"] > 0){
			$where[] = "price >= :total_price_min";
			$binds[":total_price_min"] = (int)$cnds["totalPriceMin"];
		}

		if(isset($cnds["totalPriceMax"]) && (int)$cnds["totalPriceMax"] > 0){
			$where[] = "price <= :total_price_max";
			$binds[":total_price_max"] = (int)$cnds["totalPriceMax"];
		}

		if(isset($cnds["orderDateStart"]) && strlen($cnds["orderDateStart"]) > 0 && strtotime($cnds["orderDateStart"])){
			$where[] = "order_date >= :order_date_start";
			$binds[":order_date_start"] = strtotime($cnds["orderDateStart"]);
		}

		if(isset($cnds["orderDateEnd"]) && strlen($cnds["orderDateEnd"]) > 0 && strtotime($cnds["orderDateEnd"])){
			$where[] = "order_date <= :order_date_end";
			$order_date_end_time = strtotime($cnds["orderDateEnd"]);
			if(date("H:i:s", $order_date_end_time) === "00:00:00"){
				$order_date_end_time = strtotime($cnds["orderDateEnd"]." 23:59:59");
			}
			$binds[":order_date_end"] = $order_date_end_time;
		}

		//備考　SQLだけでは取得できないのでデータを取得直後に更に加工を行う
		if(isset($cnds["orderMemo"]) && strlen($cnds["orderMemo"])){
			//検索ワードをスペースやカンマで配列にする
			$words = self::getOrderMemoWords($cnds["orderMemo"]);
			if(count($words)){
				$memoWhere = array();
				for($i = 0; $i < count($words); $i++){
					$word = $words[$i];
					$memoWhere[] = "attributes LIKE :order_memo_" . $i;
					$binds[":order_memo_" . $i] = "%" . $word . "%";
				}

				if(count($memoWhere)){
					$cnd = ((int)$cnds["orderMemoAndOr"] === 1) ? " OR " : " AND ";
					$where[] = "(" . implode($cnd, $memoWhere) . ")";
				}
			}
		}

		//コメント
		if(isset($cnds["orderComment"]) && strlen($cnds["orderComment"])){
			//検索ワードをスペースやカンマで配列にする
			$words = self::getOrderMemoWords($cnds["orderComment"]);
			if(count($words)){
				$commentWhere = array();
				for($i = 0; $i < count($words); $i++){
					$word = $words[$i];
					$commentWhere[] = "content LIKE :comment_" . $i;
					$binds[":comment_" . $i] = "%" . $word . "%";
				}

				if(count($commentWhere)){
					$cnd = ((int)$cnds["orderCommentAndOr"] === 1) ? " OR " : " AND ";
					$where[] = "id IN (SELECT order_id FROM soyshop_order_state_history WHERE ". implode($cnd, $commentWhere) . ")";
				}
			}
		}

		//更新日
		if(isset($cnds["updateDateStart"]) && strlen($cnds["updateDateStart"]) > 0 && strtotime($cnds["updateDateStart"])){
			$where[] = "id IN (SELECT order_id FROM soyshop_order_state_history WHERE order_date >= :update_date_start)";
			$binds[":update_date_start"] = strtotime($cnds["updateDateStart"]);
		}

		if(isset($cnds["updateDateEnd"]) && strlen($cnds["updateDateEnd"]) > 0 && strtotime($cnds["updateDateEnd"])){
			$where[] = "id IN (SELECT order_id FROM soyshop_order_state_history WHERE order_date <= :update_date_end)";
			$update_date_end_time = strtotime($cnds["updateDateEnd"]);
			if(date("H:i:s", $update_date_end_time) === "00:00:00"){
				$update_date_end_time = strtotime($cnds["updateDateEnd"]." 23:59:59");
			}
			$binds[":update_date_end"] = $update_date_end_time;
		}

		if(isset($cnds["trackingNumber"]) && strlen(trim($cnds["trackingNumber"])) > 0){
			$where[] = "tracking_number LIKE :tracking_number";
			$binds[":tracking_number"] = "%" . trim($cnds["trackingNumber"]) . "%";
		}
		if(isset($cnds["orderId"]) && strlen($cnds["orderId"]) > 0){
			$where[] = "id LIKE :order_id";
			$binds[":order_id"] = "%" . $cnds["orderId"] . "%";
		}

		if(isset($cnds["orderIdStart"]) && strlen($cnds["orderIdStart"]) > 0){
			$where[] = "id >= :order_id_start";
			$binds[":order_id_start"] = $cnds["orderIdStart"];
		}

		if(isset($cnds["orderIdEnd"]) && strlen($cnds["orderIdEnd"]) > 0){
			$where[] = "id <= :order_id_end";
			$binds[":order_id_end"] = $cnds["orderIdEnd"];
		}

		if(isset($cnds["userName"]) && strlen($cnds["userName"]) > 0){
			if(!class_exists("SOYShop_User")) SOY2::import("domain.SOYShop_User");
			$nameCnds = explode(" ", str_replace("　", " ", $cnds["userName"]));
			if(count($nameCnds)){
				$subWhere = array();
				$i = 0;
				foreach($nameCnds as $nameCnd){
					$nameCnd = trim($nameCnd);
					if(!strlen($nameCnd)) continue;
					$subWhere[] = "name LIKE :user_name_" . $i;
					$binds[":user_name_" . $i] = "%" . $nameCnd . "%";
					$i++;
				}

				if(count($subWhere)) $where[] = "user_id in (select id from ". SOYShop_User::getTableName() ." where " . implode(" AND ", $subWhere) . ")";
				unset($subWhere);
			}
		}

		if(isset($cnds["userReading"])){
			$reading = trim($cnds["userReading"]);
			if(strlen($reading) > 0){
				if(!class_exists("SOYShop_User")) SOY2::import("domain.SOYShop_User");
				$readCnds = explode(" ", str_replace("　", " ", $reading));
				if(count($readCnds)){
					$subWhere = array();
					$i = 0;
					foreach($readCnds as $readCnd){
						//全角カナであろうデータ
						$katakana = mb_convert_kana($readCnd,"c");
						$hiragana = mb_convert_kana($readCnd,"C");

						//SQLiteで文字列の全角半角を無視して検索する関数が見つからないので、一つ一つ丁寧にSQL構文を発行する
						$subWhere[] = "(reading LIKE :user_reading_c_" . $i . " OR " .
						"reading LIKE :user_reading_C_" . $i . " OR " .
						"reading LIKE :user_reading_k_" . $i . ")";

						$binds[":user_reading_c_" . $i] = "%" . $katakana . "%";
						$binds[":user_reading_C_" . $i] = "%" . $hiragana . "%";
						$binds[":user_reading_k_" . $i] = "%" . mb_convert_kana($hiragana,"k") . "%";

						$i++;
					}

					if(count($subWhere)) $where[] = "user_id in (select id from ". SOYShop_User::getTableName().	" WHERE " . implode(" AND ", $subWhere) . ")";
					unset($subWhere);
				}
			}
		}

		if(isset($cnds["userMailAddress"]) && strlen($cnds["userMailAddress"]) > 0){
			if(!class_exists("SOYShop_User"))SOY2::import("domain.SOYShop_User");
			$where[] = "user_id in (select id from ". SOYShop_User::getTableName() ." where mail_address LIKE :mail_address)";
			$binds[":mail_address"] = "%" . $cnds["userMailAddress"] . "%";
		}

		if(isset($cnds["userGender"]) && count($cnds["userGender"])){
			if(!class_exists("SOYShop_User"))SOY2::import("domain.SOYShop_User");
			$where[] = "user_id in (select id from ". SOYShop_User::getTableName() ." where gender IN (" . implode(",", $cnds["userGender"]) . "))";
		}

		if(isset($cnds["userBirthday"]) && count($cnds["userBirthday"])){
			$birthArray = $cnds["userBirthday"];
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

		//電話番号 Faxと携帯も同時検索　出来れば全角数字にも対応したい
		if(isset($cnds["userTelephoneNumber"]) && count($cnds["userTelephoneNumber"])){
			$tellArray = $cnds["userTelephoneNumber"];
			$tell_where = array();
			foreach(array("telephone", "fax", "cellphone") as $tellType){
				$w = array();	//半角版
				$W = array();	//全角版
				for($i = 0; $i < 3; $i++){
					if(isset($tellArray[$i]) && strlen(trim($tellArray[$i]))){
						$num = trim($tellArray[$i]);
						$w[] = " " . $tellType . "_number LIKE :" . $tellType . $i;
						$binds[":" . $tellType . $i] = "%" . $num . "%";
						$W[] = " " . $tellType . "_number LIKE :" . $tellType . $i . "_N";
						$binds[":" . $tellType . $i . "_N"] = "%" . mb_convert_kana($num, "N") . "%";
					}
				}

				if(count($w)){
					$tell_where[] = "((" . implode(" AND ", $w) . ") OR (" . implode(" AND ", $W) . "))";
				}
			}

			if(count($tell_where)){
				$where[] = "user_id in (select id from ". SOYShop_User::getTableName() ." where " . implode(" OR ", $tell_where) . ")";
			}
		}

		if(
			(isset($cnds["itemName"]) && strlen($cnds["itemName"])) > 0 ||
			(isset($cnds["itemCode"]) && strlen($cnds["itemCode"]) > 0)
		){
			SOY2DAOFactory::importEntity("shop.SOYShop_Item");

			$table .= " inner join " . SOYShop_ItemOrder::getTableName();
			$table .= " on (".SOYShop_Order::getTableName() . ".id = ".SOYShop_ItemOrder::getTableName() . ".order_id) ";
			$table .= " inner join " . SOYShop_Item::getTableName();
			$table .= " on (".SOYShop_Item::getTableName() . ".id = ".SOYShop_ItemOrder::getTableName() . ".item_id)";


			if(isset($cnds["itemName"]) && strlen($cnds["itemName"])){
				$where[] = SOYShop_Item::getTableName() . ".item_name like :item_name";
				$binds[":item_name"] = "%" . $cnds["itemName"] . "%";
			}

			if(isset($cnds["itemCode"]) && strlen($cnds["itemCode"])){
				$where[] = SOYShop_Item::getTableName() . ".item_code like :item_code";
				$binds[":item_code"] = "%" . $cnds["itemCode"] . "%";
			}
		}

		//支払方法
		if(isset($cnds["paymentMethod"]) && count($cnds["paymentMethod"])){
			$attr_where = array();
			foreach($cnds["paymentMethod"] as $p){
				$attr_where[] = "attributes LIKE '%" . $p . "%'";
			}
			$where[] = "(" . implode(" OR ", $attr_where) . ")";
		}

		//拡張ポイントから出力したフォーム用
		SOYShopPlugin::load("soyshop.order.search");
		$queries = SOYShopPlugin::invoke("soyshop.order.search", array(
			"mode" => "search",
			"params" => (isset($cnds["customs"])) ? $cnds["customs"] : array()
		))->getQueries();

		if(is_array($queries) && count($queries)){
			foreach($queries as $moduleId => $values){
				if(!isset($values["queries"])) continue;
				if(!is_array($values["queries"]) || !count($values["queries"])) continue;
				$where = array_merge($where, $values["queries"]);
				if(isset($values["binds"])) $binds = array_merge($binds, $values["binds"]);
			}
		}

		$this->where = $where;
		$this->binds = $binds;
		$this->table = $table;
	}

	/**
	 * マイページの購入履歴一覧で使う
	 */
	public function setSearchConditionForMyPage($userId, $cnds = array()){
		$this->setSearchCondition($cnds);

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
