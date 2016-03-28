<?php

/**
 * @entity admin.LoginErrorLog
 */
abstract class LoginErrorLogDAO extends SOY2DAO{
	
	/**
	 * @return id
	 * @trigger onInsert
	 */
	abstract function insert(LoginErrorLog $bean);
	
	/**
	 * @trigger onUpdate
	 */
	abstract function update(LoginErrorLog $bean);
	
	/**
	 * @return object
	 */
	abstract function getById($id);
	
	/**
	 * @return object
	 */
	abstract function getByIp($ip);
	
	abstract function deleteByIp($ip);
	
	function getCandidates($i){
		$sql = "SELECT * FROM LoginErrorLog ".
				"WHERE count >= :cnt";
				
		$binds[":cnt"] = (int)$i;
		
		try{
			$res = $this->executeQuery($sql, $binds);
		}catch(Exception $e){
			return array();
		}
		
		if(!count($res)) return array();
		
		$list = array();
		foreach($res as $values){
			$list[] = $this->getObject($values);
		}
		return $list;
	}
	
	function hasErrorLogin($cnt = 10){
		$sql = "SELECT * FROM LoginErrorLog ".
				"WHERE count >= ". $cnt . " ".
				"LIMIT 1";
				
		try{
			$res = $this->executeQuery($sql, array());
		}catch(Exception $e){
			return false;
		}
		
		if(!count($res)) return false;
		
		/**
		 * @ToDo　どれくらいの期間でログインを試みたかも見たい
		 */
		return true;
	}
	
	//ログの削除:参照速度を上げるため
	function clean($cnt){
		//期限のハードコーディング
		$sql = "DELETE FROM LoginErrorLog ".
				"WHERE count <  ". $cnt . " ".
				"AND successed = 0 ".
				"AND update_date < " . strtotime("-1 month");
				
		try{
			$res = $this->executeUpdateQuery($sql, array());
		}catch(Exception $e){
			//
		}
	}
	
	/**
	 * @final
	 */
	function onInsert($query, $binds){
		$binds[":startDate"] = time();
		$binds[":updateDate"] = time();
		
		return array($query, $binds);
	}
	
	/**
	 * @final
	 */
	function onUpdate($query, $binds){
		$binds[":updateDate"] = time();
		
		return array($query, $binds);
	}
}
?>