<?php

class CommonAdditionCommon{

	public static function getConfig(){
		return SOYShop_DataSets::get("addition_option", array(
			"name" => "",
			"price" => 0,
			"text" => ""
    	));
	}
}
