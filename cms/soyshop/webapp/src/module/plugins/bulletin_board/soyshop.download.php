<?php

class BulletinBoardDownload extends SOYShopDownload{

	const LIMIT = 5;

	//掲示板毎に新着のJSONを出力する
	function execute(){
		if(!isset($_GET["forum_id"]) || !is_numeric($_GET["forum_id"])) self::_empty();

		$groupLogic = SOY2Logic::createInstance("module.plugins.bulletin_board.logic.GroupLogic");
		$group = $groupLogic->getById($_GET["forum_id"]);
		if(!is_numeric($group->getId())) self::_empty();

		$topics = SOY2Logic::createInstance("module.plugins.bulletin_board.logic.TopicLogic")->getByGroupId($group->getId(), true, true);
		if(!count($topics)) self::_empty();

		$postLogic = SOY2Logic::createInstance("module.plugins.bulletin_board.logic.PostLogic");
		$arr = array();
		foreach($topics as $topic){
			if(count($arr) > self::LIMIT) break;
			$cnt = $postLogic->countPostByTopicId($topic->getId());
			if($cnt === 0) continue;

			$label = $topic->getLabel();

			//最初と最後の投稿を取得する
			list($firstPost, $lastPost) = $postLogic->getFirstAndLastPost($topic->getId());
			unset($firstPost);

			$arr[] = array(
				"label" => $label . "(" . $cnt . ")",
				"url" => soyshop_get_mypage_url() . "/board/topic/detail/" . $topic->getId(),
				"post_date" => $lastPost->getCreateDate()
			);
		}

		echo json_encode($arr);
		exit;
	}

	private function _empty(){
		echo json_encode(array());
		exit;
	}
}
SOYShopPlugin::extension("soyshop.download", "bulletin_board", "BulletinBoardDownload");
