<?php
SOY2::import("module.plugins.bulletin_board.domain.SOYBoard_Post");
/**
 * @entity SOYBoard_Post
 */
abstract class SOYBoard_PostDAO extends SOY2DAO {

	/**
	 * @return id
	 * @trigger onInsert
	 */
	abstract function insert(SOYBoard_Post $bean);

	/**
	 * @trigger onUpdate
	 */
	abstract function update(SOYBoard_Post $bean);

	abstract function get();

	/**
	 * @return object
	 */
	abstract function getById($id);

	abstract function getByTopicId($topicId);

	/**
	 * @query topic_id = :topicId AND is_open = 1
	 */
	abstract function getByTopicIdAndIsOpen($topicId);

	/**
	 * @return column_count_post
	 * @columns count(id) as count_post
	 * @query topic_id = :topicId AND is_open = 1
	 */
	abstract function countByTopicIdAndIsOpen($topicId);

	/**
	 * @return column_count_post
	 * @columns count(id) as count_post
 	 * @query user_id = :userId AND is_open = 1
	 */
	abstract function countByUserIdAndIsOpen($userId);

	/**
	 * @final
	 */
	function getFirstAndLastPostByTopicId($topicId){
		$sql = "SELECT id FROM soyboard_post ".
				"WHERE topic_id = :topicId ".
				"AND is_open = " . SOYBoard_Post::IS_OPEN . " ".
				"ORDER BY create_date ASC";

		try{
			$res = $this->executeQuery($sql, array(":topicId" => $topicId));
		}catch(Exception $e){
			$res = array();
		}

		if(!count($res)) return array(0, 0);

		if(count($res) === 1){
			return array($res[0]["id"], $res[0]["id"]);
		}

		$first = $res[0];
		$last = end($res);

		return array($first["id"], $last["id"]);
	}

	/**
	 * @final
	 */
	function getCreateDateListByGroupId($groupId){
		if(!is_numeric($groupId) || $groupId == 0) return array();

		$topicIds = self::_getTopicIdsByGroupId($groupId);
		if(!count($topicIds)) return array();

		$sql = "SELECT topic_id, MAX(create_date) AS cdate FROM soyboard_post ".
				"WHERE topic_id IN (" . implode(",", $topicIds) . ") ".
				"AND is_open = " . SOYBoard_Post::IS_OPEN . " ".
				"GROUP BY topic_id ";
		try{
			$res = $this->executeQuery($sql);
		}catch(Exception $e){
			$res = array();
		}
		if(!count($res)) return array();

		$list = array();
		foreach($res as $v){
			$list[$v["topic_id"]] = (int)$v["cdate"];
		}

		foreach($topicIds as $topicId){
			if(!isset($list[$topicId])) $list[$topicId] = 0;
		}

		return $list;
	}

	private function _getTopicIdsByGroupId($groupId){
		$sql = "SELECT id FROM soyboard_topic ".
				"WHERE group_id = :groupId";
		try{
			$res = $this->executeQuery($sql, array(":groupId" => $groupId));
		}catch(Exception $e){
			$res = array();
		}
		if(!count($res)) return array();

		$ids = array();
		foreach($res as $v){
			$ids[] = $v["id"];
		}

		return $ids;
	}

	/**
	 * @final
	 */
	function onInsert($query, $binds){
		if(!isset($binds[":isOpen"]) || !is_numeric($binds[":isOpen"])) $binds[":isOpen"] = SOYBoard_Post::NO_OPEN;
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
