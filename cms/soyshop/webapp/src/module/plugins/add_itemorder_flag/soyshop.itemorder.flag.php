<?php

class AddItemOrderFlag extends SOYShopItemOrderFlag{

	function FlagItem(){
		SOY2::import("module.plugins.add_itemorder_flag.util.AddItemOrderFlagUtil");
		$config = AddItemOrderFlagUtil::getConfig();
		if(count($config)){
			$list = array();
			foreach($config as $key => $label){
				$list[$key] = $label;
			}
			return $list;
		}
	}
}
SOYShopPlugin::extension("soyshop.itemorder.flag", "add_itemorder_flag", "AddItemOrderFlag");
