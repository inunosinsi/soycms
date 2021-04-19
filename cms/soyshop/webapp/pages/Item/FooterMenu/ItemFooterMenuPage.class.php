<?php

class ItemFooterMenuPage extends HTMLPage{

	function __construct(){
		parent::__construct();

		DisplayPlugin::toggle("app_limit_function", (AUTH_CONFIG || AUTH_CSV));

		DisplayPlugin::toggle("app_config_limit_function", AUTH_CONFIG);
		DisplayPlugin::toggle("app_csv_limit_function", AUTH_CSV);
	}
}
