<?php

class UserLogic extends SOY2LogicBase {

	function __construct(){

	}

	// @return array(id => SOYShop_User...) SOYShop_User::attributesにcount_postを格納する
	function getUsers(){
		$dao = SOY2DAOFactory::create("user.SOYShop_UserDAO");
		$users = $dao->getIsPublishUsers();
		if(!count($users)) return array();

		$userIds = array();
		foreach($users as $user){
			$userIds[] = (int)$user->getId();
		}

		$sql = "SELECT user_id, count(id) AS count_post FROM soyboard_post ".
				"WHERE user_id IN (" . implode(",", $userIds) . ") AND is_open = 1 ".
				"GROUP BY user_id";
		try{
			$res = $dao->executeQuery($sql);
		}catch(Exception $e){
			$res = array();
		}

		$posts = array();
		if(count($res)){
			foreach($res as $v){
				$posts[(int)$v["user_id"]] = (int)$v["count_post"];
			}
		}

		foreach($userIds as $userId){
			if(!isset($posts[$userId])) $posts[$userId] = 0;
		}

		arsort($posts);
		$list = array();
		foreach($posts as $userId => $postCount){
			if(!isset($users[$userId])) continue;
			$users[$userId]->setAttribute("post_count", $postCount);
			$list[$userId] = $users[$userId];
		}
		unset($users);
		unset($posts);

		return $list;
	}
}
