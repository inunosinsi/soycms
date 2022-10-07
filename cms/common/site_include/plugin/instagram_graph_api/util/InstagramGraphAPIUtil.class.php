<?php

class InstagramGraphAPIUtil {

	public static function getConfig(){
		SOY2::import("domain.cms.DataSets");
		return DataSets::get("instagram_graph_api.config", array(
			"ver" => "12.0",
			"limit" => 15,
			"bizId" => "",
			"token" => ""
		));
	}

	public static function saveConfig(array $values){
		SOY2::import("domain.cms.DataSets");
		DataSets::put("instagram_graph_api.config", $values);
	}
}