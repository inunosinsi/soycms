<?php

class LoadPage extends WebPage {

	function __construct(){
		parent::__construct();

		//不安定なので廃止
		echo json_encode(array("content" => SOY2DAOFactory::create("memo.SOYShop_MemoDAO")->getLatestMemo()->getContent()));
		exit;
	}
}
