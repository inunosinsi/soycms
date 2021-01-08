<?php
SOY2::import("module.plugins.bulletin_board.domain.SOYBoard_Topic");
/**
 * @entity SOYBoard_Topic
 */
abstract class SOYBoard_TopicDAO extends SOY2DAO {

	/**
	 * @return id
	 * @trigger onInsert
	 */
	abstract function insert(SOYBoard_Topic $bean);

	abstract function get();

	/**
	 * @return object
	 */
	abstract function getById($id);

	/**
	 * @order create_date ASC
	 */
	abstract function getByGroupId($groupId);

	/**
	 * @return column_count_topic
	 * @columns count(id) as count_topic
	 * @query group_id = :groupId
	 */
	abstract function countByGroupId($groupId);

	/**
	 * @final
	 */
	function getWithNotDisabledGroup(){
		try{
			$res = $this->executeQuery(self::_buildGetSqlStmt(null, null));
		}catch(Exception $e){
			$res = array();
		}
		if(!count($res)) return array();

		$topics = array();
		foreach($res as $v){
			$topics[] = $this->getObject($v);
		}
		return $topics;
	}

	/**
	 * @final
	 */
	function getByIdWithNotDisabledGroup($topicId){
		try{
			$res = $this->executeQuery(self::_buildGetSqlStmt($topicId, null), array(":topicId" => $topicId));
		}catch(Exception $e){
			$res = array();
		}
		return (isset($res[0])) ? $this->getObject($res[0]) : new SOYBoard_Topic();
	}

	/**
	 * @final
	 */
	function getByGroupIdWithNotDisabledGroup($groupId){
		try{
			$res = $this->executeQuery(self::_buildGetSqlStmt(null, $groupId), array(":groupId" => $groupId));
		}catch(Exception $e){
			$res = array();
		}
		if(!count($res)) return array();

		$topics = array();
		foreach($res as $v){
			$topics[] = $this->getObject($v);
		}

		return $topics;
	}

	//グループの削除の状況を加味したSQL構文を発行する
	private function _buildGetSqlStmt($topicId, $groupId){
		SOY2::import("module.plugins.bulletin_board.domain.SOYBoard_Group");
		$sql = "SELECT t.* FROM soyboard_topic t ".
				"INNER JOIN soyboard_group g ".
				"ON t.group_id = g.id ";
				"WHERE g.is_disabled != " . SOYBoard_Group::IS_DISABLED . " ";
		if(is_numeric($topicId)){
			$sql .= "AND t.id = :topicId ";
		}
		if(is_numeric($groupId)){
			$sql .= "AND t.group_id = :groupId ";
		}
		$sql .= "ORDER BY t.create_date DESC";
		return $sql;
	}

	/**
	 * @final
	 */
	function onInsert($query, $binds){
		$binds[":createDate"] = time();
		return array($query, $binds);
	}
}
