<?php

class SearchSupplierLogic extends SOY2LogicBase {

	private $where = array();
    private $binds = array();
	private $limit = 15;
	private $offset = 0;

	function __construct(){
		SOY2::import("module.plugins.supplier_manager.util.SupplierManagerUtil");
		SOY2::import("module.plugins.supplier_manager.domain.SOYShop_SupplierDAO");
	}

	function search(){
		$dao = self::_dao();

		$sql = "SELECT * FROM soyshop_supplier ";
		$sql .= self::_buildWhere() . " ";
		$sql .= "ORDER BY id DESC ";
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

		$sql = "SELECT COUNT(id) AS CNT FROM soyshop_supplier ";
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
		$cnds = SupplierManagerUtil::getParameter("Search");

		if(is_array($cnds) && count($cnds)){
			foreach($cnds as $key => $cnd){
				if(is_string($cnd) && strlen($cnd)) $cnd = trim($cnd);
				if(is_string($cnd) && !strlen($cnd)) continue;
				switch($key){
					case "area":
						$this->where[$key] = $key . " = :" . $key;
						$this->binds[":" . $key] = $cnd;
						break;
					case "name":
					default:
						$this->where[$key] = $key . " LIKE :" . $key;
						$this->binds[":" . $key] = "%" . $cnd . "%";
						break;
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
		if(is_null($dao)) $dao = SOY2DAOFactory::create("SOYShop_SupplierDAO");
		return $dao;
	}
}
