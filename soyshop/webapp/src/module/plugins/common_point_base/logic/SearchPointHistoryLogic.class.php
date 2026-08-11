<?php
SOY2::imports("module.plugins.common_point_base.domain.*");
class SearchPointHistoryLogic extends SOY2LogicBase{

	public static function getInstance($className, $args){
		//import
		SOY2DAOFactory::create("SOYShop_PointHistoryDAO");

		return parent::getInstance($className, $args);
	}

	private $table;
	private $limit;
	private $offset;
	private $order;
	private $where = array();
	private $binds = array();

	private $sorts = array(
		"create_date" =>  "create_date",
		"create_date_desc" =>  "create_date desc",
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

		$sql = "select ".SOYShop_PointHistory::getTableName() . ".*"." from " . $table . " ";
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
		$this->where[] = SOYShop_PointHistory::getTableName() . ".user_id = :mypage_user_id";
		$this->binds[":mypage_user_id"] = $userId;
	}

	/**
	 * 検索する
	 */
	function getHistories(){
		SOY2DAOConfig::setOption("limit_query", true);
		$pointHistoryDAO = SOY2DAOFactory::create("SOYShop_PointHistoryDAO");
		$result  = array();

		$pointHistoryDAO->setLimit($this->getLimit());
		$pointHistoryDAO->setOffset($this->getOffset());
		try{
			$res = $pointHistoryDAO->executeQuery($this->getSearchSQL(), $this->getBinds());
		}catch(Exception $e){
			return array();
		}

		foreach($res as $row){
			if(!isset($row)) continue;
			$obj = $pointHistoryDAO->getObject($row);
			$result[] = $obj;
		}

		return $result;
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
		if(strlen($this->table) < 1) $this->table = SOYShop_PointHistory::getTableName();
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
			$order = str_replace("_desc"," desc", $order);
		}else{
			$order = "create_date desc";
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