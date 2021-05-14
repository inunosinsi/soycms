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

		if(count($cnds)){
			foreach($cnds as $key => $value){
				if(is_string($value)) $value = trim($value);
				if(is_string($value) && !strlen($value)) continue;
				if(is_array($value) && !count($value)) continue;
				switch($key){
					case "name_or_code":
						$this->where[$key] = "(name LIKE :name OR coupon_code LIKE :code)";
						$this->binds[":name"] = "%" . $value . "%";
						$this->binds[":code"] = "%" . $value . "%";
						break;
					case "coupon_type":
						$w = array();
						foreach($value as $v){
							$w[] = "coupon_type = " . $v;
						}
						if(count($w)) $this->where[$key] = "(" . implode(" OR ", $w) . ")";
						break;
					case "expired":	//期限切れクーポンを表示する
						$this->where["time_limit_end"] = "time_limit_end > 0";
						break;
					default:
						if(is_string($value)){	//文字列として検索
							$this->where[$key] = $key . " = :" . $key;
							$this->binds[":" . $key] = "%" . $value . "%";
						}else if(is_array($value)){	//配列として検索
							$this->where[$key] = $key . " IN (\"" . implode("\"", $value) . "\")";
						}
				}
			}
		}

		//期限の設定	@ToDoいずれは検索できるようにしたい
		if(!isset($this->where["time_limit_end"])) $this->where["time_limit_end"] = "time_limit_end > " . time();

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
