<?php

class TopicLogic extends SOY2LogicBase {

	function __construct(){
		SOY2::import("module.plugins.bulletin_board.domain.SOYBoard_TopicDAO");
	}

	function get(bool $notDisabledGroup=false){
		if($notDisabledGroup){
			return self::_dao()->getWithNotDisabledGroup();
		}else{
			try{
				return self::_dao()->get();
			}catch(Exception $e){
				return array();
			}
		}
	}

	function getById(int $topicId, bool $notDisabledGroup=false){
		if($notDisabledGroup){
			return self::_dao()->getByIdWithNotDisabledGroup($topicId);
		}else{
			return self::_getById($topicId);
		}
	}

	function getByIds(array $topicIds){
		return self::_dao()->getByIds($topicIds);
	}

	function getByGroupId($groupId, $notDisabledGroup=false, $sortMode=false){
		if($sortMode){
			SOY2::import("module.plugins.bulletin_board.domain.SOYBoard_PostDAO");
			$res = SOY2DAOFactory::create("SOYBoard_PostDAO")->getCreateDateListByGroupId($groupId);

			//富豪プログラミングになるが、一回ずつ取得	→　@ToDo いずれページャに置き換える
			$topics = array();
			foreach($res as $topicId => $createDate){
				$topic = self::_dao()->getByIdWithNotDisabledGroup($topicId);
				if(is_null($topic->getId())) continue;
				$d = $createDate;
				for(;;){
					if(!isset($topics[$d])) break;
					$d++;
				}
				$topics[$d] = $topic;
			}

			return $topics;
		}else{
			if($notDisabledGroup){
				return self::_dao()->getByGroupIdWithNotDisabledGroup($groupId);
			}else{
				try{
					return self::_dao()->getByGroupId($groupId);
				}catch(Exception $e){
					return array();
				}
			}
		}
	}

	function countByGroupId($groupId){
		if(!is_numeric($groupId)) return 0;
		try{
			return self::_dao()->countByGroupId($groupId);
		}catch(Exception $e){
			return 0;
		}
	}

	function insert($values){
		$values["label"] = trim($values["label"]);
		$dao = self::_dao();
		$topic = SOY2::cast("SOYBoard_Topic", $values);
		if(!strlen($topic->getLabel())) return null;
		try{
			return $dao->insert($topic);
		}catch(Exception $e){
			return null;
		}
	}

	//トピックに紐付いたポスト数のリスト
	function getNoPostTopicList(){
		SOY2::import("module.plugins.bulletin_board.domain.SOYBoard_PostDAO");
		$list = SOY2DAOFactory::create("SOYBoard_PostDAO")->countPostEachTopicId();
		if(!count($list)) return array();

		//ポスト数が0のトピックIDをまとめる
		$topicIds = array();
		foreach($list as $topicId => $cnt){
			if($cnt > 0) continue;
			$topicIds[] = $topicId;
		}
		return $topicIds;
	}

	function deleteById($topicId){
		try{
			self::_dao()->deleteById($topicId);
		}catch(Exception $e){
			//
		}
	}

	private function _getById($topicId){
		try{
			return self::_dao()->getById($topicId);
		}catch(Exception $e){
			return new SOYBoard_Topic();
		}
	}

	private function _dao(){
		static $dao;
		if(is_null($dao)) $dao = SOY2DAOFactory::create("SOYBoard_TopicDAO");
		return $dao;
	}
}
