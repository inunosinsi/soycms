<?php
/**
 * @param string
 * @param HTMLPage
 * @return string
 */
function soycms_osm_link(string $html, HTMLPage $htmlObj){
	$pageObj = $htmlObj->create("osm_link", "HTMLTemplatePage", array(
		"arguments" => array("osm_link", $html)
	));

	if(!defined("OSM_PLUGIN_DIR")) define("OSM_PLUGIN_DIR", SOY2::RootDir()."site_include/plugin/openstreetmap/");

	//
	$entryId = 0;
	if(property_exists($htmlObj, "entry") && $htmlObj->entry instanceOf LabeledEntry){
		$entryId = $htmlObj->entry->getId();
	}

	$osm = SOY2Logic::createInstance("site_include.plugin.openstreetmap.logic.OSMLogic")->getByEntryId($entryId);

	$lat = $osm->getLatitude();
	$lng = $osm->getLongitude();

	$pageObj->addModel("osm_is_latlng", array(
		"soy2prefix" => "cms",
		"visible" => (strlen((string)$lat) && strlen((string)$lng))
	));

	/**
	 * 土壌インベントリー
	 */
	$endpoint = "https://soil-inventory.rad.naro.go.jp/figure.html";
	$pageObj->addLink("soil_map_link", array(
		"soy2prefix" => "cms",
		"link" => $endpoint."?lat=".$lat."&lng=".$lng."&zoom=15"
	));

	/**
	 * シームレス地質図
	 */
	$endpoint = "https://gbank.gsj.jp/seamless/v2/viewer/?center=";
	$pageObj->addLink("geo_map_link", array(
		"soy2prefix" => "cms",
		"link" => $endpoint.$lat."%2C".$lng."&z=13"
	));

	/**
	 * OpenStreetMap
	 */
	$endpoint = "https://www.openstreetmap.org/search";
	$pageObj->addLink("openstreetmap_link", array(
		"soy2prefix" => "cms",	
		"link" => $endpoint."?lat=".$lat."&lon=".$lng."&zoom=19#map=19/".$lat."/".$lng
	));

	/**
	 * OpenStreetMap iframe
	 */
	$endpoint = "https://www.openstreetmap.org/export/embed.html";
	$pageObj->addModel("openstreetmap_iframe", array(
		"soy2prefix" => "cms",	
		"attr:src" => $endpoint."?bbox=".$lng."%2C".$lat."%2C".$lng."%2C".$lat."&layer=mapnik&marker=".$lat."%2C".$lng
	));

	/**
	 * Google Maps
	 */
	$endpoint = "https://www.google.co.jp/maps/@";
	$pageObj->addLink("google_maps_link", array(
		"soy2prefix" => "cms",	
		"link" => $endpoint.$lat.",".$lng
	));

	$pageObj->display();
}
