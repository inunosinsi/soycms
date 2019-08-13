<?php

class SearchAdministratorLogic extends SOY2LogicBase {

	private $limit;
	private $offset;
	private $order;
	private $where = array();
	private $binds = array();

	function __construct(){

	}

	function setSearchCondition($search){
		if(!is_array($search) || !count($search)) return;

		foreach($search as $key => $cnd){
			$this->where[$key] = $key . " LIKE :" . $key;
			$this->binds[":" . $key] = "%" . $cnd . "%";
		}
	}

	function get(){
		$dao = SOY2DAOFactory::create("admin.AdministratorDAO");
		$sql = "SELECT * FROM Administrator ";

		if(count($this->where)){
			$sql .= " WHERE " . implode(" AND ", $this->where);
		}

		$sql .= " LIMIT " . $this->limit;
		$sql .= " OFFSET " . $this->offset;

		try{
			$res = $dao->executeQuery($sql, $this->binds);
		}catch(Exception $e){
			$res = array();
		}

		if(!count($res)) return array();

		$admins = array();
		foreach($res as $v){
			$admin = $dao->getObject($v);
			$admin->sites = array();	//サイトに関しては空の配列を入れておく
			$admins[] = $admin;
		}

		return $admins;
	}

	function total(){
		$dao = SOY2DAOFactory::create("admin.AdministratorDAO");
		$sql = "SELECT COUNT(id) AS COUNT FROM Administrator ";

		if(count($this->where)){
			$sql .= " WHERE " . implode(" AND ", $this->where);
		}

		try{
			$res = $dao->executeQuery($sql, $this->binds);
		}catch(Exception $e){
			$res = array();
		}

		return (isset($res[0]["COUNT"])) ? (int)$res[0]["COUNT"] : 0;
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
}
