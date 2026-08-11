<?php

class SearchReviewAdvanceLogic extends SOY2LogicBase {

	private $where = array();
    private $binds = array();
	private $itemId;
	private $limit = 15;
	private $offset = 0;

	function __construct(){

	}

	function search(){
		if(!is_numeric($this->itemId)) return array();

		$dao = self::_dao();

		$sql = "SELECT * FROM soyshop_item_review ";
		$sql .= self::_buildWhere();
		$sql .= " ORDER BY create_date DESC ";
		$sql .= "LIMIT " . $this->limit . " ";
		$sql .= "OFFSET " . $this->offset;
		try{
			$res = $dao->executeQuery($sql, $this->binds);
		}catch(Exception $e){
			return array();
		}

		if(!count($res)) return array();

		$list = array();
		foreach($res as $v){
			$list[] = $dao->getObject($v);
		}
		return $list;
	}

	function getTotal(){
		if(!is_numeric($this->itemId)) return 0;

		$dao = self::_dao();

		$sql = "SELECT COUNT(id) AS CNT FROM soyshop_item_review ".
				"WHERE item_id = :itemId ".
				"AND is_approved = " . SOYShop_ItemReview::REVIEW_IS_APPROVED;
		try{
			$res = $dao->executeQuery($sql, array(":itemId" => $this->itemId));
		}catch(Exception $e){
			return 0;
		}

		return (isset($res[0]["CNT"])) ? (int)$res[0]["CNT"] : 0;
	}

	private function _buildWhere(){
		self::_setSearchCondition();

		if(count($this->where)){
			$sql = "WHERE " . implode(" AND ", $this->where);
			return $sql;
		}

		return "";
	}

	private function _setSearchCondition(){
		$this->where["item_id"] = "item_id = :itemId";
		$this->binds[":itemId"] = $this->itemId;

		$this->where["is_approved"] = "is_approved = " . SOYShop_ItemReview::REVIEW_IS_APPROVED;
	}

	function setItemId($itemId){
		$this->itemId = $itemId;
	}

	function setLimit($limit){
		$this->limit = $limit;
	}
	function setOffset($offset){
		$this->offset = $offset;
	}

	private function _dao(){
		static $dao;
		if(is_null($dao)) {
			SOY2::import("module.plugins.item_review.domain.SOYShop_ItemReviewDAO");
			$dao = SOY2DAOFactory::create("SOYShop_ItemReviewDAO");
		}
		return $dao;
	}
}
