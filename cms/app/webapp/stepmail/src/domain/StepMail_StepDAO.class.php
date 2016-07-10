<?php

/**
 * @entity StepMail_Step
 */
abstract class StepMail_StepDAO extends SOY2DAO{
	
	/**
	 * @return id
	 * @trigger onInsert
	 */
	abstract function insert(StepMail_Step $bean);
	
	/**
	 * @trigger onUpdate
	 */
	abstract function update(StepMail_Step $bean);
	
	/**
	 * @return object
	 */
	abstract function getById($id);
	
	/**
	 * @return list
	 * @query mail_id = :mailId AND is_disabled != 1
	 * @order id ASC
	 */
	abstract function getByMailId($mailId);
	
	/**
	 * @return column_count_step
	 * @columns count(id) as count_step
	 * @query is_disabled != 1 AND mail_id = :mailId
	 */
	abstract function countStepByMailId($mailId);
	
	/**
	 * @return object
	 * @query mail_id = :mailId AND id > :stepId AND is_disabled != 1
	 * @order id ASC
	 * @limit 1
	 */
	abstract function getNextStep($mailId, $stepId);
	
	function countNextTh($mailId, $stepId){
		$sql = "SELECT COUNT(id) AS th ".
				"FROM stepmail_step ".
				"WHERE mail_id = :mail_id ".
				"AND id <= :step_id ".
				"AND is_disabled != 1";
				
		$binds = array(":mail_id" => $mailId, ":step_id" => $stepId);
		
		try{
			$res = $this->executeQuery($sql, $binds);
		}catch(Exception $e){
			return 0;
		}
		
		return (isset($res[0]["th"])) ? (int)$res[0]["th"] : 0;
	}
	
	function getSumSendDate($mailId, $stepId){
		$sql = "SELECT SUM(days_after) AS days ".
				"FROM stepmail_step ".
				"WHERE mail_id = :mail_id ".
				"AND id <= :step_id ".
				"AND is_disabled != 1";
				
		$binds = array(":mail_id" => $mailId, ":step_id" => $stepId);
		
		try{
			$res = $this->executeQuery($sql, $binds);
		}catch(Exception $e){
			return 0;
		}
		
		return (isset($res[0]["days"])) ? (int)$res[0]["days"] : 0;
	}
	
	/**
	 * @return object
	 * @query mail_id = :mailId AND is_disabled != 1
	 * @order id ASC
	 * @limit 1
	 */
	abstract function getFirstStepMailByMailId($mailId);
	
	/**
	 * @final
	 */
	function onInsert($query, $binds){
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