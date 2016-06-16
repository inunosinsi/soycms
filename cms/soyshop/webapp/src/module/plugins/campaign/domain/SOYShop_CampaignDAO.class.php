<?php
/**
 * @entity SOYShop_Campaign
 */
abstract class SOYShop_CampaignDAO extends SOY2DAO {
	
	/**
	 * @return id
	 * @trigger onInsert
	 */
	abstract function insert(SOYShop_Campaign $bean);
	
	/**
	 * @trigger onUpdate
	 */
	abstract function update(SOYShop_Campaign $bean);
	
	/**
	 * @return list
	 * @query is_disabled != 1
	 */
	abstract function get();
	
	/**
	 * @return object
	 */
	abstract function getById($id);
	
	function getWithinPostPeriodEnd($lim = 10){
		$sql = "SELECT * FROM soyshop_campaign ".
				"WHERE is_open = 1 ".
				"AND is_disabled != 1 ".
				"AND post_period_start < :now ".
				"AND post_period_end > :now ".
				"ORDER BY post_period_start ASC ".
				"LIMIT " . $lim;
				
		try{
			$res = $this->executeQuery($sql, array(":now" => time()));
		}catch(Exception $e){
			return array();
		}
		
		if(!count($res)) return array();
		
		$list = array();
		foreach($res as $v){
			$list[] = $this->getObject($v);
		}
		
		return $list;
	}
		
	function getBeforePostPeriodEnd($lim = 10){
		$sql = "SELECT * FROM soyshop_campaign ".
				"WHERE is_disabled != 1 ".
				"AND post_period_end > :now ".
				"ORDER BY post_period_start ASC ".
				"LIMIT " . $lim;
				
		try{
			$res = $this->executeQuery($sql, array(":now" => time()));
		}catch(Exception $e){
			return array();
		}
		
		if(!count($res)) return array();
		
		$list = array();
		foreach($res as $v){
			$list[] = $this->getObject($v);
		}
		
		return $list;
	}
	
	/**
	 * @final
	 */
	function onInsert($query, $binds){
		if(strlen($binds[":postPeriodStart"])) $binds[":postPeriodStart"] = 0;
		if(!strlen($binds[":postPeriodEnd"])) $binds[":postPeriodEnd"] = 2147483647;
		
		$binds[":createDate"] = time();
		$binds[":updateDate"] = time();
		
		return array($query, $binds);
	}
	
	/**
	 * @final
	 */
	function onUpdate($query, $binds){
		if(!strlen($binds[":postPeriodStart"])) $binds[":postPeriodStart"] = 0;
		if(!strlen($binds[":postPeriodEnd"])) $binds[":postPeriodEnd"] = 2147483647;
		
		$binds[":updateDate"] = time();
		
		return array($query, $binds);
	}
}
?>