<?php

class UserGoogleMapAddress extends SOYShopUserAddressBase{

	const PLUGIN_ID = "user_google_map";

	function getForm(int $userId){
		SOY2::import("module.plugins.user_google_map.util.UserGoogleMapUtil");
		$config = UserGoogleMapUtil::getConfig();
		$key = (isset($config["google_maps_api_key"])) ? $config["google_maps_api_key"] : "";

		$html = array();
		$html[] = "<a href=\"javascript:void(0)\" id=\"search_by_address\">住所から地図検索</a>";
		$html[] = "<div id=\"map\"></div>";
		$html[] = "<input type=\"hidden\" id=\"lat\" name=\"user_google_map[lat]\" value=\"" . self::_getGeoInfo($userId, "lat") . "\">";
		$html[] = "<input type=\"hidden\" id=\"lng\" name=\"user_google_map[lng]\" value=\"" . self::_getGeoInfo($userId, "lng") . "\">";

		//顧客グループプラグインからスクリプトを取得する
		$html[] = "<script>\n" . file_get_contents(dirname(dirname(__FILE__)) . "/user_group/js/map.js") . "</script>";
		$html[] = "<script src=\"https://maps.googleapis.com/maps/api/js?key=" . $key . "&callback=initMap\"></script>";
		$html[] = "<style>";
		$html[] = "#map{";
		$html[] = "width:400px;";
		$html[] = "height:300px;";
		$html[] = "}";
		$html[] = "</style>";

		return implode("\n", $html);
	}

	private function _getGeoInfo($userId, string $mode = "lat"){
		return soyshop_get_user_attribute_value($userId, self::PLUGIN_ID . "_" . $mode, "string");
	}
}

SOYShopPlugin::extension("soyshop.user.address", "user_google_map", "UserGoogleMapAddress");
