<?php

class FbGraphAPIUtil {

	public static function getConfig(){
		SOY2::import("domain.cms.DataSets");
		return DataSets::get("fb_graph_api.config", array(
			"ver" => "15.0",
			"limit" => 15,
			"bizId" => "",
			"token" => ""
		));
	}

	public static function saveConfig(array $values){
		SOY2::import("domain.cms.DataSets");
		DataSets::put("fb_graph_api.config", $values);
	}
}