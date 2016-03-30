<?php
/**
 * @entity SOYShop_Point
 */
abstract class SOYShop_PointDAO extends SOY2DAO{
   	/**
	 * @return id
	 */
   	abstract function insert(SOYShop_Point $bean);
   	
	abstract function update(SOYShop_Point $bean);
	
	/**
	 * @return object
	 */
	abstract function getByUserId($userId);
	
	abstract function deleteByUserId($userId);
	
	function getUsersByNoticeDate($start, $end){
		$sql = "SELECT user.* FROM SOYShop_User user ".
				"INNER JOIN soyshop_point point ".
				"ON user.id = point.user_id ".
				"WHERE point.time_limit > :start ".
				"AND point.time_limit < :end ".
				"AND user.is_disabled != 1";
		$binds = array(":start" => $start, ":end" => $end);
				
		try{
			$results = $this->executeQuery($sql, $binds);
		}catch(Exception $e){
			var_dump($e);
			return array();
		}
		
		if(count($results) === 0) return array();
		
		$users = array();
		$userDao = SOY2DAOFactory::create("user.SOYShop_UserDAO");
		foreach($results as $result){
			if(!isset($result["id"])) continue;
			$users[] = $userDao->getObject($result);
		}
		
		return $users;
	}
}
?>