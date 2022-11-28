<?php

class SearchUsersLogic extends SOY2LogicBase{
	
	private $query;
	private $limit;
	private $offset;
	private $order;
	private $where;
	private $binds;
	private $table;
	
	const SORT_ID				= "id";
	const SORT_ID_DESC		   = "id_desc";
	const SORT_NAME			  = "name";
	const SORT_NAME_DESC		 = "name_desc";
	const SORT_READING		   = "reading";
	const SORT_READING_DESC	  = "reading_desc";
	const SORT_MAIL_ADDRESS	  = "mail_address";
	const SORT_MAIL_ADDRESS_DESC = "mail_address_desc";
	
	function __construct(){
		SOY2DAOConfig::setOption("limit_query",true);
		$extendLogic = SOY2Logic::createInstance("logic.user.ExtendUserDAO");
		$this->query = $extendLogic->getDAO();
		$this->table = ($extendLogic->checkSOYShopConnect()===true) ? "soyshop_user" : "soymail_user";
	}
	
	function setLimit($value){
		$this->limit = $value;
	}
	function setOffset($value){
		$this->offset = $value;
	}
	
	function setSortOrder($sort){
		switch($sort){
			case self::SORT_ID :
				$order = " order by id ";
				break;
			case self::SORT_ID_DESC :
				$order = " order by id desc ";
				break;
			case self::SORT_NAME :
				$order = " order by name ";
				break;
			case self::SORT_NAME_DESC :
				$order = " order by name desc ";
				break;
			case self::SORT_READING :
				$order = " order by reading ";
				break;
			case self::SORT_READING_DESC :
				$order = " order by reading desc ";
				break;
			case self::SORT_MAIL_ADDRESS :
				$order = " order by mail_address ";
				break;
			case self::SORT_MAIL_ADDRESS_DESC :
				$order = " order by mail_address desc ";
				break;
			default:
				$order = "";
		}
		$this->order = $order;
		
	}
	function setSearchCondition($search){
		$where = array();
		$binds = array();
		foreach($search as $key => $value){
			if( is_string($value) && strlen($value) ){
				switch($key){
					case "area" :
					case "job_area" :
						if(!is_numeric($value)) break;
					case "id":
					case "name":
					case "reading":
					case "mail_address":
					case "zip_code" :
					case "address1" :
					case "address2" :
					case "telephone_number" :
					case "faxNumber" :
					case "cellphone_number" :
					case "job_name" :
					case "job_zip_code" :
					case "job_address1" :
					case "job_address2" :
					case "job_telephone_number" :
					case "job_fax_number" :
					case "memo" :
					case "attribute1":
					case "attribute2":
					case "attribute3":
					case "mail_error_count" :
						$where[] = " $key like :$key ";
						$binds[":$key"] = "%".$value."%";
						break;
					case "not_send":
					case "is_disabled" :
						$where[] = ($value == 1) ? " $key = 1 " : " $key != 1 " ;
						break;
				}
			}
			if( is_array($value) && count($value) ){
				switch($key){
					case "gender" :
						$where_gender = array();
						foreach($value as $key_2 => $value_2){
							if( is_string($value_2) && strlen($value_2) ){
								switch($key_2){
									case "male" :
										$where_gender[] = " gender = 0 ";
										break;
									case "female" :
										$where_gender[] = " gender = 1 ";
										break;
									case "other" :
										$where_gender[] = " gender != 0 AND gender != 1 ";
										break;
								}
							}
						}
						if(count($where_gender)) $where[] = " ( ".implode(" OR ", $where_gender). " ) ";
						break;
					case "birthday" : 
					case "register_date" :
					case "update_date" :
						if(strlen(@$value["start"]["month"]) && strlen(@$value["start"]["day"]) && strlen(@$value["start"]["year"])){
							$value_start = @mktime(0,0,0,$value["start"]["month"],$value["start"]["day"],$value["start"]["year"]);
							$key_start = $key . "_start";
							$where[] = " $key >= :$key_start ";
							$binds[":$key_start"] = $value_start;
						}
						if(strlen(@$value["end"]["month"]) && strlen(@$value["end"]["day"]) && strlen(@$value["end"]["year"])){
							$value_end = @mktime(0,0,0,$value["end"]["month"],$value["end"]["day"],$value["end"]["year"]);
							$key_end = $key . "_end";
							$where[] = " $key <= :$key_end ";
							$binds[":$key_end"] = $value_end;
						}
						break;
				}
			}
		}

		$this->where = $where;
		$this->binds = $binds;
	}

	protected function getCountSQL(){
		$countSql = "select count(*) as count from " . $this->table . " ";
		$countSql .= " where ";
		if(count($this->where)>0){
			$countSql .= implode(" and ", $this->where) . " and ";
		}
		$countSql .= "is_disabled != 1";
		return $countSql;
	}
	
	protected function getUsersSQL(){
		$sql = "select * from " . $this->table . " ";
		$sql .= " where ";
		if(count($this->where)>0){
			$sql .= implode(" and ", $this->where) . " and ";
		}
		$sql .= "is_disabled != 1";
		
		if(strlen($this->order)) $sql .= $this->order;
		return $sql;
	}
	
	//合計件数取得
	function getTotalCount(){
		if($this->table==="soyshop_user")$old = SOYMailUtil::switchSOYShopConfig();
		
		$countSql = $this->getCountSQL();
		
		try{
			$countResult = $this->query->executeQuery($countSql, $this->binds);
		}catch(Exception $e){
			return 0;
		}
		
		if($this->table==="soyshop_user")$old = SOYMailUtil::resetConfig($old);
		return $countResult[0]["count"];
	}
	
	//ユーザー取得
	function getUsers(){
		if($this->table==="soyshop_user")$old = SOYMailUtil::switchSOYShopConfig();
		
		$this->query->setLimit($this->limit);
		$this->query->setOffset($this->offset);
		$sql = $this->getUsersSQL();
		
		try{
			$result = $this->query->executeQuery($sql, $this->binds);
		}catch(Exception $e){
			$result = array();
		}
		
		$users = array();
		foreach($result as $raw){
			$users[] = $this->query->getObject($raw);
		}
		
		if($this->table==="soyshop_user")$old = SOYMailUtil::resetConfig($old);
		
		return $users;
	}
}

?>