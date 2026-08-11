<?php

class ClickPostUtil {

	public static function getConfig(){
		return SOYShop_DataSets::get("delivery_clickpost.config", array(
			"fee" => 185,
			"shippingFree" => "",
			"feePerPack" => array(),	// array("pack":0, "fee":0),
			"name" => "郵送",
			"description" => "クリックポストで郵送する"
		));
	}

	public static function saveConfig(array $cfgs){
		foreach(array("name", "description") as $idx){
			$cfgs[$idx] = trim($cfgs[$idx]);
		}
		foreach(array("fee") as $idx){
			if(!isset($cfgs[$idx]) || !is_numeric($cfgs[$idx])) $cfgs[$idx] = 0;
		}
		if((int)$cfgs["shippingFree"] === 0) $cfgs["shippingFree"] = "";
		if(isset($cfgs["fee_per_pack"])){
			$lines = (isset($cfgs["fee_per_pack"]["fee"]) && is_array($cfgs["fee_per_pack"]["fee"])) ? count($cfgs["fee_per_pack"]["fee"]) : 0;
			$cfgs["feePerPack"] = array();
			if($lines > 0){
				for($i = 0; $i < $lines; $i++){
					if(isset($cfgs["fee_per_pack"]["fee"][$i]) && (int)$cfgs["fee_per_pack"]["fee"][$i] > 0){
						if(isset($cfgs["fee_per_pack"]["pack"][$i]) && (int)$cfgs["fee_per_pack"]["pack"][$i] > 0){
							$cfgs["feePerPack"][] = array("pack" => $cfgs["fee_per_pack"]["pack"][$i], "fee" => $cfgs["fee_per_pack"]["fee"][$i]);
						}
					}
				}
			}
			unset($cfgs["fee_per_pack"]);

			// 並べ替え
			if(count($cfgs["feePerPack"])){
				usort($cfgs["feePerPack"], function ($a, $b) {
				    return $a['pack'] <=> $b['pack'];
				});
			}
		}
		SOYShop_DataSets::put("delivery_clickpost.config", $cfgs);
	}
}
