<?php

class IndexPage extends WebPage{

	var $id;

	function __construct($args) {
		SOY2PageController::jump("User");
		exit;
	}

}

