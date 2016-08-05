<?php
class DetailPage extends MainMyPagePageBase{

    function __construct($args) {    	
    	
    	//ダウンロードページの廃止
		$this->jumpToTop();
    }
}
?>