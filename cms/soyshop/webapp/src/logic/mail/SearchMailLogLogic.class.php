<?php

class SearchMailLogLogic extends SOY2LogicBase{

	public static function getInstance($className,$args){
		//import
		SOY2DAOFactory::create("logging.SOYShop_MailLogDAO");
		
		return parent::getInstance($className,$args);
	}

	private $table;
	private $limit;
	private $offset;
	private $order;
	private $where = array();
	private $binds = array();

	private $sorts = array(
		"send_date" =>  "send_date",
		"send_date_desc" =>  "send_date desc",
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

		$sql = "select ".SOYShop_MailLog::getTableName() . ".*"." from " . $table . " ";
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
		$this->where[] = SOYShop_MailLog::getTableName() . ".user_id = :mypage_user_id";
		$this->binds[":mypage_user_id"] = $userId;
		
		//送信に成功したもののみ表示
		$this->where[] = SOYShop_MailLog::getTableName() . ".is_success = 1";
	}

	/**
	 * 検索する
	 */
	function getLogs(){

		SOY2DAOConfig::setOption("limit_query", true);
		$mailLogDAO = SOY2DAOFactory::create("order.SOYShop_MailLogDAO");

		try{
			$result  = array();

			$mailLogDAO->setLimit($this->getLimit());
			$mailLogDAO->setOffset($this->getOffset());

			$res = $mailLogDAO->executeQuery($this->getSearchSQL(),$this->getBinds());
			
			foreach($res as $row){
				$obj = @$mailLogDAO->getObject($row);
				$result[] = $obj;
			}

			return $result;
		}catch(Exception $e){
			//var_dump($e);
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
		if(strlen($this->table) < 1)$this->table = SOYShop_MailLog::getTableName();
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