<?php

class PostLogic extends SOY2LogicBase {

	function __construct(){
		SOY2::import("module.plugins.bulletin_board.util.BulletinBoardUtil");
		SOY2::import("module.plugins.bulletin_board.domain.SOYBoard_PostDAO");
	}

	function save($userId=null, $topicId=null, $postId=null, $content=""){
		$dao = self::_dao();

		if(is_numeric($topicId)){	//新規作成
			$post = new SOYBoard_Post();
			$post->setTopicId($topicId);
			$post->setUserId($userId);
			$post->setContent(trim(BulletinBoardUtil::shapeHTML($content)));

			//とりあえずは常に公開にしておく
			$post->setIsOpen(SOYBoard_Post::IS_OPEN);

			try{
				return $dao->insert($post);
			}catch(Exception $e){
				return null;
			}
		} else if(is_numeric($postId)) {	//更新
			$new = SOY2::cast(self::_getById($postId), array("content" => $content));
			try{
				$dao->update($new);
				return $new->getId();
			}catch(Exception $e){
				return null;
			}
		}

		return null;
	}

	function update(SOYBoard_Post $post){
		try{
			self::_dao()->update($post);
			return $post->getId();
		}catch(Exception $e){
			return null;
		}
	}

	function getById($postId, $currentLoggedInUserId=null){
		$post = self::_getById($postId);

		//後者の条件で現在ログインしているユーザと一致したポストか調べる
		if(is_null($currentLoggedInUserId) || (int)$post->getUserId() === (int)$currentLoggedInUserId) return $post;

		return new SOYBoard_Post();
	}


	function getByTopicId($topicId, $isAll=false){
		// @ToDo 公開の有無のパラメータ
		$dao = self::_dao();
		$dao->setOrder("create_date ASC");
		try{
			return ($isAll) ? $dao->getByTopicId($topicId) : $dao->getByTopicIdAndIsOpen($topicId);
		}catch(Exception $e){
			return array();
		}
	}

	function getFirstAndLastPost($topicId){
		if($topicId === 0) return array(new SOYBoard_Post(), new SOYBoard_Post());
		list($firstId, $lastId) = self::_dao()->getFirstAndLastPostByTopicId($topicId);

		return array(self::_getById($firstId), self::_getById($lastId));
	}

	function countPostByTopicId($topicId){
		try{
			return self::_dao()->countByTopicIdAndIsOpen($topicId);
		}catch(Exception $e){
			return 0;
		}
	}

	//新着情報 とりあえず1週間以内の投稿
	function getNewPosts(){
		return self::_dao()->getNewPosts();
	}

	function getLatestPostTopicIdByGroupId($groupId){
		if(!is_numeric($groupId)) return null;
		return self::_dao()->getLatestPostByGroupId($groupId)->getTopicId();
	}

	//投稿後に投稿したトピック内で何人のアカウントが投稿したか？投稿したアカウントのIDを返す
	function getUserIdsWithinSameTopicByPostId($postId){
		return self::_dao()->getUserIdsWithinSameTopicByPostId($postId);
	}

	private function _getById($postId){
		$dao = self::_dao();
		if($postId === 0) return new SOYBoard_Post();
		try{
			return self::_dao()->getById($postId);
		}catch(Exception $e){
			return new SOYBoard_Post();
		}
	}

	private function _dao(){
		static $dao;
		if(is_null($dao)) $dao = SOY2DAOFactory::create("SOYBoard_PostDAO");
		return $dao;
	}
}
