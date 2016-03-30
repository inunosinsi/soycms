<?php

class IndexPage extends WebPage{

	var $id;

	function IndexPage($args) {
		SOY2PageController::jump("User");
		exit;
	}

}

