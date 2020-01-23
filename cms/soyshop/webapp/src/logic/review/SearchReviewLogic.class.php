<?php
SOY2::imports("module.plugins.item_review.domain.*");
class SearchReviewLogic extends SOY2LogicBase{

	private $query;
	private $limit;
	private $offset;
	private $order;
	private $group;
	private $having;
	private $where = array();
	private $binds = array();

	private $sorts = array(

		"nickname" => "nickname",
		"nickname_desc" => "nickname desc",

		"evaluation" =>  "evaluation",
		"evaluation_desc" =>  "evaluation desc",

		"update_date" => "update_date",
		"update_date_desc" => "update_date desc",
	);

	const TABLE_NAME = "soyshop_item_review";

	function getQuery(){
		if(is_null($this->query)){
			SOY2DAOConfig::setOption("limit_query", true);
			$this->query = SOY2DAOFactory::create("shop.SOYShop_ItemReviewDAO");
		}

		return $this->query;
	}

	function setLimit($value){
		$this->limit = $value;
	}
	function setOffset($value){
		$this->offset = $value;
	}

	function setOrder($order){
		if(isset($this->sorts[$order])){
			$order = $this->sorts[$order];
			$order = str_replace("_desc"," desc",$order);
		}else{
			$order = "update_date desc";
		}
		$this->order = "order by " . $order;

	}

	function getSorts(){
		return $this->sorts;
	}

	function setSearchCondition($search){
		$where = array();
		$binds = array();
		foreach($search as $key => $value){

			switch($key){
				case "evaluation":
					$where[] = "evaluation LIKE :evaluation";
					$binds[":evaluation"] = "%" . $value . "%";
					break;
				case "nickname":
				default:
					$where[] = "nickname LIKE :nickname";
					$binds[":nickname"] = "%" . $value . "%";
					break;
			}
		}

		$this->where = $where;
		$this->binds = $binds;
	}

	/**
	 * マイページの購入履歴一覧で使う
	 */
	public function setSearchConditionForMyPage($userId, $search = array()){
		$this->setSearchCondition($search);

		//現在のユーザーの注文のみ
		$this->where[] = SOYShop_ItemReview::getTableName() . ".user_id = :mypage_user_id";
		$this->binds[":mypage_user_id"] = $userId;
	}

	protected function getCountSQL(){
		$countSql = "select count(*) as count from " . self::TABLE_NAME . " ";
		if(count($this->where) > 0){
			$countSql .= " where ".implode(" and ", $this->where);
		}
		return $countSql;
	}

	protected function getReviewsSQL(){
		$sql = "select * from " . self::TABLE_NAME . " ";
		if(count($this->where) > 0){
			$sql .= " where ".implode(" and ", $this->where);
		}
		if(strlen($this->order)) $sql .= " " . $this->order;
		return $sql;
	}

	//合計件数取得
	function getTotalCount(){
		$countSql = $this->getCountSQL();
		try{
			$countResult = $this->getQuery()->executeQuery($countSql, $this->binds);
		}catch(Exception $e){
			return 0;
		}
		return $countResult[0]["count"];
	}

	//ユーザー取得
	function getReviews(){
		$this->getQuery()->setLimit($this->limit);
		$this->getQuery()->setOffset($this->offset);
		$sql = $this->getReviewsSQL();

		try{
			$result = $this->getQuery()->executeQuery($sql, $this->binds);
		}catch(Exception $e){
			$result = array();
		}

		$users = array();
		foreach($result as $raw){
			$users[] = $this->getQuery()->getObject($raw);
		}
		return $users;
	}
}
