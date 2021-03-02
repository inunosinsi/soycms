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

		$list = array();
		if(count($res)){
			foreach($res as $v){
				$list[$v["topic_id"]] = (int)$v["cdate"];
			}
		}

		foreach($topicIds as $topicId){
			if(!isset($list[$topicId])) $list[$topicId] = 0;
		}

		//ソート
		arsort($list);

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
	function getNewPosts($periodStart=null){
		if(is_null($periodStart)) $periodStart = strtotime("-7 day");

		$sql = "SELECT id, topic_id, create_date FROM soyboard_post ".
				"WHERE is_open = " . SOYBoard_Post::IS_OPEN . " ".
				"AND create_date > :start ".
				"ORDER BY create_date DESC";
		try{
			$res = $this->executeQuery($sql, array(":start" => $periodStart));
		}catch(Exception $e){
			$res = array();
		}
		if(!count($res)) return array();

		//各トピックの最新を取得する
		$posts = array();
		$list = array();	//トピックIDの重複を避ける為に、同一トピックの投稿を避ける
		foreach($res as $v){
			if(count($list) && is_numeric(array_search($v["topic_id"], $list))) continue;
			$list[] = $v["topic_id"];
			$posts[] = $this->getObject($v);
		}

		return $posts;
	}

	/**
	 * @final
	 */
	function getLastPostDate(){
		try{
			$res = $this->executeQuery("SELECT create_date FROM soyboard_post WHERE is_open = " . SOYBoard_Post::IS_OPEN . " ". "ORDER BY create_date DESC LIMIT 1");
		}catch(Exception $e){
			$res = array();
		}
		return (isset($res[0]["create_date"])) ? (int)$res[0]["create_date"] : null;
	}

	/**
	 * @final
	 */
	function getLatestPostByGroupId($groupId){
		if(!is_numeric($groupId)) return new SOYBoard_Post();

		$sql = "SELECT p.* FROM soyboard_post p ".
				"INNER JOIN soyboard_topic t ".
				"ON p.topic_id = t.id ".
				"WHERE t.group_id = :groupId ".
				"AND p.is_open = " . SOYBoard_Post::IS_OPEN . " ".
				"ORDER BY p.create_date DESC ".
				"LIMIT 1";
		try{
			$res = $this->executeQuery($sql, array(":groupId" => $groupId));
		}catch(Exception $e){
			$res = array();
		}

		return (isset($res[0])) ? $this->getObject($res[0]) : new SOYBoard_Post();
	}

	/**
	 * @final
	 */
	function getLatestPostByTopicId($topicId){
		if(!is_numeric($topicId)) return new SOYBoard_Post();

		$sql = "SELECT * FROM soyboard_post ".
				"WHERE topic_id = :topicId ".
				"AND is_open = " . SOYBoard_Post::IS_OPEN . " ".
				"ORDER BY create_date DESC ".
				"LIMIT 1";
		try{
			$res = $this->executeQuery($sql, array(":topicId" => $topicId));
		}catch(Exception $e){
			$res = array();
		}

		return (isset($res[0])) ? $this->getObject($res[0]) : new SOYBoard_Post();
	}

	/**
	 * @final
	 */
	function getUserIdsWithinSameTopicByPostId($postId){
		$sql = "SELECT DISTINCT user_id FROM soyboard_post ".
				"WHERE topic_id = (".
					"SELECT topic_id FROM soyboard_post WHERE id = :postId".
				") ".
				"AND is_open = 1";
		try{
			$res = $this->executeQuery($sql, array(":postId" => $postId));
		}catch(Exception $e){
			$res = array();
		}
		if(!count($res)) return array();

		$ids = array();
		foreach($res as $v){
			$ids[] = (int)$v["user_id"];
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
