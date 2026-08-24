<?php
/**
 * @param string
 * @param HTMLPage
 * @return string
 */
function soycms_osm_init(string $html, HTMLPage $htmlObj){
	if(!defined("OSM_PLUGIN_DIR")) define("OSM_PLUGIN_DIR", SOY2::RootDir()."site_include/plugin/openstreetmap/");

	$osmPlugin = CMSPlugin::loadPluginConfig("openstreetmap");
	$home = ($osmPlugin instanceof OSMPlugin) ? $osmPlugin->getHomePosition() : array();
	
	$html = array();
	$html[] = "<script>";
	$html[] = "let lat = ".$home["lat"].";";
	$html[] = "let lng = ".$home["lng"].";";
	$html[] = "let zoom = ".$home["zoom"].";";
	
	$html[] = trim(file_get_contents(OSM_PLUGIN_DIR."js/osm.js"));
	$html[] = "</script>";
	return implode("\n", $html);
}
