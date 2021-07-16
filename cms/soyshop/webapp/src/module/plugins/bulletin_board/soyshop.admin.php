<?php
class BulletinBoardAdmin extends SOYShopAdminBase{

	function execute(){
		//トピックのポストが0のまま一週間が経過したものを自動で削除
		$topicLogic = SOY2Logic::createInstance("module.plugins.bulletin_board.logic.TopicLogic");
		$list = $topicLogic->getNoPostTopicList();

		$topics = (count($list)) ? $topicLogic->getByIds($list) : array();
		if(count($topics)){
			$oneWeekAfter = strtotime("-1 week");
			foreach($topics as $topic){
				if($topic->getCreateDate() > $oneWeekAfter) continue;

				// @ToDo トピックの削除
			}
		}
	}
}
SOYShopPlugin::extension("soyshop.admin", "bulletin_board", "BulletinBoardAdmin");
