<?php

abstract class SOYShop_NoticeArrivalDAO extends SOY2DAO{
	
	/**
	 * @return id
	 * @trigger onInsert
	 */
	abstract function insert(SOYShop_NoticeArrival $bean);
	
	/**
	 * @trigger onUpdate
	 */
	abstract function update(SOYShop_NoticeArrival $bean);
	
	//条件に応じてオブジェクトを取得できる
	function getByItemIdAndUserId($itemId, $userId, $sended = null, $checked = null){
		$binds = array("itemId" => $itemId, "userId" => $userId);
		
		$sql = "SELECT * FROM soyshop_notice_arrival ".
				"WHERE item_id = :itemId ".
				"AND user_id = :userId ";
				
		if(!is_null($sended) && is_numeric($sended)){
			$sql .=	"AND sended = :sended ";
			$binds["sended"] = $sended;
		}
		
		if(!is_null($checked) && is_numeric($checked)){
			$sql .= "AND checked = :checked ";
			$binds["checked"] = $checked;
		}
		
		$sql .= "LIMIT 1";
		
		try{
			$results = $this->executeQuery($sql, $binds);
		}catch(Exception $e){
			$results = array();
		}
		
		if(!isset($results[0])) return new SOYShop_NoticeArrival();
		
		return $this->getObject($results[0]);
		
	}
	
	//全商品の入庫通知希望の顧客のアドレスリストを取得する
	function getUsers($sended = null, $checked = null){
		$binds = array();
		
		$sql = "SELECT user.* FROM soyshop_user user ".
				"INNER JOIN soyshop_notice_arrival notice ".
				"ON user.id = notice.user_id ".
				"WHERE user.is_disabled = 0 ";
		
		if(!is_null($sended) && is_numeric($sended)){
			$sql .=	"AND notice.sended = :sended ";
			$binds["sended"] = $sended;
		}
		
		if(!is_null($checked) && is_numeric($checked)){
			$sql .= "AND notice.checked = :checked ";
			$binds["checked"] = $checked;
		}		
		
		try{
			$results = $this->executeQuery($sql, $binds);
		}catch(Exception $e){
			return array();
			
		}
		
		$userDao = SOY2DAOFactory::create("user.SOYShop_UserDAO");
		
		$users = array();
		foreach($results as $res){
			if(!isset($res["mail_address"])) continue;
			$users[] = $userDao->getObject($res);
		}
		return $users;
	}
	
	//商品ごとの入庫通知希望の顧客のアドレスリストを取得する
	function getUsersByItemId($itemId, $sended = null, $checked = null){
		$binds = array("itemId" => $itemId);
		
		$sql = "SELECT user.* FROM soyshop_user user ".
				"INNER JOIN soyshop_notice_arrival notice ".
				"ON user.id = notice.user_id ".
				"WHERE notice.item_id = :itemId ";
		
		if(!is_null($sended) && is_numeric($sended)){
			$sql .=	"AND notice.sended = :sended ";
			$binds["sended"] = $sended;
		}
		
		if(!is_null($checked) && is_numeric($checked)){
			$sql .= "AND notice.checked = :checked ";
			$binds["checked"] = $checked;
		}
		
		$sql .=	"AND user.is_disabled = 0";
		
		
		try{
			$results = $this->executeQuery($sql, $binds);
		}catch(Exception $e){
			return array();
			
		}
		
		$userDao = SOY2DAOFactory::create("user.SOYShop_UserDAO");
		
		$users = array();
		foreach($results as $res){
			if(!isset($res["mail_address"])) continue;
			$users[] = $userDao->getObject($res);
		}
		return $users;
	}
	
	function getItems($userId, $sended = null, $checked = null){
		
		$now = time();
		$binds = array("userId" => $userId);
		
		$sql = "SELECT DISTINCT item.* FROM soyshop_item item ".
				"INNER JOIN soyshop_notice_arrival notice ".
				"ON item.id = notice.item_id ".
				"WHERE notice.user_id = :userId ";
		
		if(!is_null($sended) && is_numeric($sended)){
			$sql .=	"AND notice.sended = :sended ";
			$binds["sended"] = $sended;
		}
		
		if(!is_null($checked) && is_numeric($checked)){
			$sql .= "AND notice.checked = :checked ";
			$binds["checked"] = $checked;
		}
		
		$sql .=	"AND item.item_is_open = 1 ".
				"AND item.is_disabled = 0 ".
				"AND item.open_period_start <= " . $now . " ".
				"AND item.open_period_end >= " . $now;
						
		try{
			$results = $this->executeQuery($sql, $binds);
		}catch(Exception $e){
			return array();
		}
			
		if(count($results) === 0) return array();
		
		$itemDao = SOY2DAOFactory::create("shop.SOYShop_ItemDAO");
		
		$items = array();
		foreach($results as $res){
			$items[] = $itemDao->getObject($res);
		}
		return $items;
	}
	
	//新着情報用、登録日と商品名も表示したい時に利用
	function getUsersForNewsPage($sended = null, $checked = null){
		$binds = array();
		
		$sql = "SELECT user.id, user.mail_address, user.name, notice.create_date, notice.item_id, item.item_name " .
				"FROM soyshop_user user ".
				"INNER JOIN soyshop_notice_arrival notice ".
				"ON user.id = notice.user_id ".
				"INNER JOIN soyshop_item item ".
				"on item.id = notice.item_id ".
				"WHERE user.is_disabled = 0 ";
				"AND item.item_is_open = 1";
		
		if(!is_null($sended) && is_numeric($sended)){
			$sql .=	"AND notice.sended = :sended ";
			$binds["sended"] = $sended;
		}
		
		if(!is_null($checked) && is_numeric($checked)){
			$sql .= "AND notice.checked = :checked ";
			$binds["checked"] = $checked;
		}
		
		$sql .= "ORDER BY notice.create_date DESC ".
				"LIMIT 15";
		
		try{
			$results = $this->executeQuery($sql, $binds);
		}catch(Exception $e){
			var_dump($e);
			return array();
		}
		
		return $results;
	}
	
	/**
	 * @final
	 */
	function onInsert($query, $binds){
		$binds[":sended"] = 0;
		$binds[":checked"] = 0;
		$binds[":createDate"] = time();
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