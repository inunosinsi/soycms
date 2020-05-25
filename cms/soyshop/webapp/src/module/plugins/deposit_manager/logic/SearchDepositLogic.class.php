<?php

class SearchDepositLogic extends SOY2LogicBase {

	private $where = array();
    private $binds = array();
	private $limit = 15;
	private $offset = 0;

	function __construct(){
		SOY2::import("module.plugins.deposit_manager.domain.SOYShop_DepositManagerDepositDAO");
		SOY2::import("module.plugins.deposit_manager.util.DepositManagerUtil");
	}

	function search(){
		$dao = self::_dao();

		$sql = "SELECT * FROM soyshop_deposit_manager_deposit ";
		$sql .= self::_buildWhere() . " ";
		$sql .= "ORDER BY deposit_date DESC ";
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
		$dao = self::_dao();

		$sql = "SELECT COUNT(id) AS CNT FROM soyshop_deposit_manager_deposit ";
		try{
			$res = $dao->executeQuery($sql);
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
		$cnds = DepositManagerUtil::getParameter("Search");

		if(is_array($cnds) && count($cnds)){
			foreach($cnds as $key => $cnd){
				if(is_string($cnd) && strlen($cnd)) $cnd = trim($cnd);
				if(is_string($cnd) && !strlen($cnd)) continue;
				switch($key){
					case "user_name":
						if($key == "user_name") $key = "name";
						$this->where[$key] = "user_id IN (SELECT id FROM soyshop_user WHERE " . $key . " LIKE :" . $key . ")";
						$this->binds[":" . $key] = "%" . $cnd . "%";
						break;
					case "subject_id":
						if(is_array($cnd) && count($cnd)){
							$this->where[$key] = $key . " IN (" . implode(",", $cnd) . ")";
						}
						break;
					case "deposit_date":
						if(is_array($cnd)){
							if(isset($cnd["start"]) && strlen($cnd["start"])){
								$this->where[$key . "_start"] = $key . " >= :" . $key . "_start";
								$this->binds[":" . $key . "_start"] = soyshop_convert_timestamp($cnd["start"]);
							}

							if(isset($cnd["end"]) && strlen($cnd["end"])){
								$this->where[$key . "_end"] = $key . " <= :" . $key . "_end";
								$this->binds[":" . $key . "_end"] = soyshop_convert_timestamp($cnd["end"], "end");
							}
						}
				}
			}
		}
	}

	function setLimit($limit){
		$this->limit = $limit;
	}
	function setOffset($offset){
		$this->offset = $offset;
	}

	private function _dao(){
		static $dao;
		if(is_null($dao)) $dao = SOY2DAOFactory::create("SOYShop_DepositManagerDepositDAO");
		return $dao;
	}
}
