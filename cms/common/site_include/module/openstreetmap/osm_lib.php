<?php
/**
 * @param string
 * @param HTMLPage
 * @return string
 */
function soycms_osm_lib(string $html, HTMLPage $htmlObj){
	$html = array();
	$html[] = "<link rel=\"stylesheet\" href=\"https://unpkg.com/leaflet@1.9.4/dist/leaflet.css\" integrity=\"sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=\" crossorigin=\"\" />";
	$html[] = "<script src=\"https://unpkg.com/leaflet@1.9.4/dist/leaflet.js\" integrity=\"sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=\" crossorigin=\"\"></script>";
	return implode("\n", $html);
}
