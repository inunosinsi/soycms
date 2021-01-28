<?php

class ItemFooterMenuPage extends HTMLPage{

	function __construct(){
		parent::__construct();

		DisplayPlugin::toggle("app_limit_function", AUTH_CSV);
	}
}
