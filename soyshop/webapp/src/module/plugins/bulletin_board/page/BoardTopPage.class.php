<?php

class BoardTopPage extends WebPage {

	const POST_LIMIT = 5;

	function __construct(){
		SOY2::import("module.plugins.bulletin_board.domain.SOYBoard_PostDAO");
		SOY2::import("module.plugins.bulletin_board._component.PostListComponent");
	}

	function execute(){
		parent::__construct();

		$dao = SOY2DAOFactory::create("SOYBoard_PostDAO");
		$dao->setLimit(self::POST_LIMIT + 1);
		$dao->setOrder("create_date DESC");
		$posts = $dao->get();

		$cnt = count($posts);

		DisplayPlugin::toggle("no_post", $cnt === 0);
		DisplayPlugin::toggle("is_post", $cnt > 0);
		DisplayPlugin::toggle("more_post", $cnt > self::POST_LIMIT);

		if($cnt > self::POST_LIMIT) $posts = array_slice($posts, 0, self::POST_LIMIT);

		$this->createAdd("post_list", "PostListComponent", array(
			"list" => $posts
		));
	}
}
