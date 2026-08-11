<?php

class CategoryFooterMenuPage extends HTMLPage{

	function __construct(){
		parent::__construct();
		
		DisplayPlugin::toggle("custom_plugin", SOYShopPluginUtil::checkIsActive("common_category_customfield"));
	}
}
