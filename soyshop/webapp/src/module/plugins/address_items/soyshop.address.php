<?php

class AddressItemsAddress extends SOYShopAddressbase{

	function getAddressItems(){
		SOY2::import("module.plugins.address_items.util.AddressItemsUtil");
		$cnfs = AddressItemsUtil::getConfig();
		//整形
		for($i = 0; $i < count($cnfs); $i++){
			$cnf = $cnfs[$i];
			$cnf["required"] = (isset($cnf["required"]) && $cnf["required"] == 1);
			$cnfs[$i] = $cnf;
		}
		return $cnfs;
	}
}
SOYShopPlugin::extension("soyshop.address", "address_items", "AddressItemsAddress");
