<?php
/**
 * @param string
 * @return array(width, height)
 */
function soycms_osm_extract_width_and_height(string $html){
	$w = 600;
	$h = 450;
	// 1. iframe の箇所のみを抽出
	$iframeTag = "";
	if (preg_match('/<iframe\b[^>]*>/is', $html, $matches)) {
		if(isset($matches[0])) $iframeTag = $matches[0];
	}

	if(!strlen($iframeTag)) return array($w, $h);

	// width の値を抽出
	preg_match('/width=["\']?([^"\'\s>]+)["\']?/i', $html, $matches);
	if(isset($matches[1]) && is_numeric($matches[1])) $w = (int)$matches[1];
	
	// height の値を抽出
	preg_match('/height=["\']?([^"\'\s>]+)["\']?/i', $html, $matches);
	if(isset($matches[1]) && is_numeric($matches[1])) $h = (int)$matches[1];

	return array($w, $h);
}
/**
 * 緯度・経度とズームレベルから OpenStreetMap 用の bbox を計算する
 */
function soycms_osm_calculate_bbox(float $lat, float $lng, int $zoom, int $widthPx, int $heightPx): string {
    $degPerPxLng = 360.0 / (256.0 * pow(2, $zoom));
    $degPerPxLat = $degPerPxLng * cos(deg2rad($lat));

    $deltaLng = ($widthPx / 2.0) * $degPerPxLng;
    $deltaLat = ($heightPx / 2.0) * $degPerPxLat;

    return sprintf('%.6f,%.6f,%.6f,%.6f', $lng - $deltaLng, $lat - $deltaLat, $lng + $deltaLng, $lat + $deltaLat);
}
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
	$zoom = (int)$osm->getZoom();	// 任意のズームレベルを設定 (15〜17 あたりが街区表示に最適)
	if($zoom <= 0) $zoom = 17;

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
	$lat = (float)$lat;
	$lng = (float)$lng;

	//iframeからwidthとheightの抽出
	[$iframeWidth, $iframeHeight] = soycms_osm_extract_width_and_height($html);
	
	$pageObj->addModel("openstreetmap_iframe", array(
		"soy2prefix" => "cms",	
		"attr:src" => $endpoint . "?bbox=" . rawurlencode(soycms_osm_calculate_bbox($lat, $lng, $zoom, $iframeWidth, $iframeHeight)) . "&layer=mapnik&marker=" . rawurlencode($lat . "," . $lng)
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
