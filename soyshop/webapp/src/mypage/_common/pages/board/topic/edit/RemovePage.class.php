<?php

class RemovePage extends MainMyPagePageBase{

	private $id;

	function __construct($args){
		if(!soy2_check_token()) $this->jumpToTop();

		// 掲示板アプリプラグインを有効にしていない場合は表示しない
		SOY2::import("util.SOYShopPluginUtil");
		if(!SOYShopPluginUtil::checkIsActive("bulletin_board")) $this->jumpToTop();

		if(!isset($args[0]) && !is_numeric($args[0])) $this->jumpToTop();
		$this->id = (int)$args[0];

		$logic = SOY2Logic::createInstance("module.plugins.bulletin_board.logic.PostLogic");
		$post = $logic->getById($this->id, $this->getUserId());
		$post->setIsOpen(SOYBoard_Post::NO_OPEN);

		if(is_numeric($logic->update($post))){
			$this->jump("board/topic/detail/" . $post->getTopicId() . "?removed");
		}else{
			$this->jump("board/topic/detail/" . $post->getTopicId() . "?failed");
		}
	}
}
