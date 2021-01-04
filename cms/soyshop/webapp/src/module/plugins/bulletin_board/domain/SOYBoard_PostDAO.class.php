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
