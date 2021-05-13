<?php

class SearchCouponLogic extends SOY2LogicBase {

	private $where = array();
	private $binds = array();

	function __construct(){
		SOY2::import("module.plugins.discount_free_coupon.domain.SOYShop_CouponDAO");
	}

	function search(){
		$sql = "SELECT * FROM soyshop_coupon ";
		$sql .= self::_buildWhere();
		$sql .= " ORDER BY id DESC";	//仮

		$dao = self::_dao();

		try{
			$res = $dao->executeQuery($sql, $this->binds);
		}catch(Exception $e){
			$res = array();
		}

		if(!count($res)) return array();

		$list = array();
		foreach($res as $v){
			$list[] = $dao->getObject($v);
		}
		return $list;
	}

	function getTotal(){
		$sql = "SELECT COUNT(id) AS CNT FROM soyshop_coupon ";
		$sql .= self::_buildWhere();

		$dao = self::_dao();

		try{
			$res = $dao->executeQuery($sql, $this->binds);
		}catch(Exception $e){
			$res = array();
		}

		return (isset($res[0]["CNT"])) ? (int)$res[0]["CNT"] : 0;
	}

	function setCondition($cnds){
		if(!is_array($cnds)) $cnds = array();

		//期限の設定	@ToDoいずれは検索できるようにしたい
		$this->where["time_limit_end"] = "time_limit_end > " . time();

		//削除フラグ
		$this->where["is_delete"] = "is_delete != " . SOYShop_Coupon::DELETED;
	}

	private function _buildWhere(){
		if(count($this->where)){
			$sql = "WHERE " . implode(" AND ", $this->where);
			return $sql;
		}

		return "";
	}

	private function _dao(){
		static $dao;
		if(is_null($dao)) $dao = SOY2DAOFactory::create("SOYShop_CouponDAO");
		return $dao;
	}
}
