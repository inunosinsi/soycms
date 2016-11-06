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

		if(strlen(@$search["userArea"]) > 0){
			if(!class_exists("SOYShop_User"))SOY2::import("domain.SOYShop_User");
			$where[] = "user_id in (select id from ". SOYShop_User::getTableName() ." where area LIKE :user_area)";
			$binds[":user_area"] = @(int)$search["userArea"];
		}

		if(isset($search["noDelivery"]) && $search["noDelivery"] == 1){
			$where[] = "order_status in (".SOYShop_Order::ORDER_STATUS_REGISTERED.",".SOYShop_Order::ORDER_STATUS_RECEIVED.",".SOYShop_Order::ORDER_STATUS_STOCK_CONFIRM.")";
		}else if(strlen(@$search["orderStatus"]) > 0){
			$where[] = "order_status = :order_status";
			$binds[":order_status"] = @$search["orderStatus"];
		}else{
			$where[] = "order_status not in (".SOYShop_Order::ORDER_STATUS_INTERIM.",".SOYShop_Order::ORDER_STATUS_CANCELED.")";
		}

		if(isset($search["noPayment"]) && $search["noPayment"] == 1){
			$where[] = "payment_status in (".SOYShop_Order::PAYMENT_STATUS_WAIT.",".SOYShop_Order::PAYMENT_STATUS_ERROR.",".SOYShop_Order::PAYMENT_STATUS_DIRECT.")";
		}else if(strlen(@$search["paymentStatus"]) > 0){
			$where[] = "payment_status = :payment_status";
			$binds[":payment_status"] = @$search["paymentStatus"];
		}

		if(strlen(@$search["orderDateStart"])>0 && strtotime($search["orderDateStart"])){
			$where[] = "order_date >= :order_date_start";
			$binds[":order_date_start"] = strtotime($search["orderDateStart"]);
		}

		if(strlen(@$search["orderDateEnd"])>0 && strtotime($search["orderDateEnd"])){
			$where[] = "order_date <= :order_date_end";
			$order_date_end_time = strtotime($search["orderDateEnd"]);
			if(date("H:i:s", $order_date_end_time) === "00:00:00"){
				$order_date_end_time = strtotime($search["orderDateEnd"]." 23:59:59");
			}
			$binds[":order_date_end"] = $order_date_end_time;
		}
		
		//更新日
		if(strlen(@$search["updateDateStart"])>0 && strtotime($search["updateDateStart"])){
			$where[] = "id IN (SELECT order_id FROM soyshop_order_state_history WHERE order_date >= :update_date_start)";
			$binds[":update_date_start"] = strtotime($search["updateDateStart"]);
		}

		if(strlen(@$search["updateDateEnd"])>0 && strtotime($search["updateDateEnd"])){
			$where[] = "id IN (SELECT order_id FROM soyshop_order_state_history WHERE order_date <= :update_date_end)";
			$update_date_end_time = strtotime($search["updateDateEnd"]);
			if(date("H:i:s", $update_date_end_time) === "00:00:00"){
				$update_date_end_time = strtotime($search["updateDateEnd"]." 23:59:59");
			}
			$binds[":update_date_end"] = $update_date_end_time;
		}

		if(strlen(@$search["trackingNumber"]) > 0){
			$where[] = "tracking_number LIKE :tracking_number";
			$binds[":tracking_number"] = "%" . @$search["trackingNumber"] . "%";
		}
		if(strlen(@$search["orderId"]) > 0){
			$where[] = "id LIKE :order_id";
			$binds[":order_id"] = "%" . @$search["orderId"] . "%";
		}
		
		if(strlen(@$search["orderIdStart"]) > 0){
			$where[] = "id >= :order_id_start";
			$binds[":order_id_start"] = $search["orderIdStart"];
		}
		
		if(strlen(@$search["orderIdEnd"]) > 0){
			$where[] = "id >= :order_id_end";
			$binds[":order_id_end"] = $search["orderIdEnd"];
		}

		if(strlen(@$search["userName"]) > 0){
			if(!class_exists("SOYShop_User"))SOY2::import("domain.SOYShop_User");
			$where[] = "user_id in (select id from ". SOYShop_User::getTableName() ." where name LIKE :user_name)";
			$binds[":user_name"] = "%" . @$search["userName"] . "%";
		}

		if(strlen(@$search["userReading"]) > 0){
			if(!class_exists("SOYShop_User"))SOY2::import("domain.SOYShop_User");

			//全角カナであろうデータ
			$katakana = mb_convert_kana(@$search["userReading"],"c");
			$hiragana = mb_convert_kana(@$search["userReading"],"C");

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

		if(strlen(@$search["itemCode"]) > 0){
			SOY2DAOFactory::importEntity("shop.SOYShop_Item");

			$table .= " inner join " . SOYShop_ItemOrder::getTableName();
			$table .= " on (".SOYShop_Order::getTableName() . ".id = ".SOYShop_ItemOrder::getTableName() . ".order_id)";
			$table .= " inner join " . SOYShop_Item::getTableName();
			$table .= " on (".SOYShop_Item::getTableName() . ".id = ".SOYShop_ItemOrder::getTableName() . ".item_id)";

			$binds[":item_id"] = "%" . $search["itemCode"] . "%";
			$where[] = SOYShop_Item::getTableName() . ".item_code like :item_id";
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
		$itemOrderDAO = SOY2DAOFactory::create("order.SOYShop_ItemOrderDAO");
		$orderDAO = SOY2DAOFactory::create("order.SOYShop_OrderDAO");

		try{
			$result  = array();

			$orderDAO->setLimit($this->getLimit());
			$orderDAO->setOffset($this->getOffset());

			$res = $orderDAO->executeQuery($this->getSearchSQL(),$this->getBinds());

			foreach($res as $row){
				$obj = @$orderDAO->getObject($row);
				$obj->setItems($itemOrderDAO->getByOrderId($obj->getId()));
				$result[] = $obj;
			}

			return $result;
		}catch(Exception $e){
			var_dump($e);
			return array();
		}

	}

	function getSorts(){
		return $this->sorts;
	}

	/**
	 * 合計件数を取得
	 */
	function getTotalCount(){
		$countSql = $this->getCountSQL();
		try{
			$dao = new SOY2DAO();
			$countResult = $dao->executeQuery($countSql, $this->binds);
		}catch(Exception $e){
			return 0;
		}
		return $countResult[0]["count"];
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
?>