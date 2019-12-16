<?php
class IndexPage extends MainMyPagePageBase{

	function __construct(){

		//ダウンロードページの廃止
		$this->jumpToTop();
	}
}
