<?php
/**
 * @param string
 * @param array
 * @return string
 */
function soycms_osm_module_build_custom_icon(string $hash, array $_cfg){
	$iconPath = OSMUtil::iconDir().$_cfg["filename"];
	$iconUrl = str_replace($_SERVER["DOCUMENT_ROOT"], "", $iconPath);

	$info = getimagesize($iconPath);

	$s = array();
	$s[] = "const ico_".$hash." = L.icon({";
	$s[] = "iconUrl: '".$iconUrl."',";
	$s[] = "iconRetinaUrl: '".$iconUrl."',";
	$s[] = "iconSize: [".$info[0].", ".$info[1]."],";
	//$s[] = "iconAnchor: [25, 50],";
	//$s[] = "popupAnchor: [0, -50]";
	$s[] = "});";
	/**$s[] = "L.marker([lat, lng], {icon: ico".$hash."}).addTo(map);";**/
	return implode("", $s);
}

function soycms_osm_module_build_feature(array $_arr, string $url){
	$label = (isset($_arr["label"])) ? trim((string)$_arr["label"]) : "";
	if(!strlen($label)) $label = trim($_arr["title"]);
	

	$s = array();
	$s[] = "{";
	$s[] = 	"\"type\":\"Feature\",";
	$s[] = 	"\"properties\":{";
	$s[] = 		"\"name\":\"".$label."\",";
	$s[] = 		"\"url\":\"".$url.$_arr["entry_id"]."\",";
	if(isset($_arr["icon"]) && strlen($_arr["icon"])){
		$s[] = 		"\"icon\":ico_".$_arr["icon"].",";
	}
	$s[] = 	"},";
	$s[] = 	"\"geometry\": {";
	$s[] = 		"\"type\": \"Point\",";
	$s[] = 		"\"coordinates\":[".$_arr["longitude"].", ".$_arr["latitude"]."]";
	$s[] = 	"}";
	$s[] = "}";
	return implode("", $s);
}
/**
 * @param string
 * @param HTMLPage
 * @return string
 */
function soycms_osm_script(string $html, HTMLPage $htmlObj){
	if(!defined("OSM_PLUGIN_DIR")) define("OSM_PLUGIN_DIR", SOY2::RootDir()."site_include/plugin/openstreetmap/");
	preg_match('/=(.*)?/', $html, $tmp);
	$blogPageUri = (isset($tmp[1]) && strlen(trim($tmp[1]))) ? trim($tmp[1]) : "";
	if($blogPageUri === "/") $blogPageUri = "";

	
	$list = SOY2DAOFactory::create("cms.BlogPageDAO")->getBlogPageUriList();
	if(!count($list)) return "";

	$blogPageId = array_search($blogPageUri, $list);
	if(!is_numeric($blogPageId) && isset($_SERVER["SOYCMS_PAGE_ID"]) && isset($list[$_SERVER["SOYCMS_PAGE_ID"]])) $blogPageId = $_SERVER["SOYCMS_PAGE_ID"];
	if(!is_numeric($blogPageId)) return "";
	

	$blog = soycms_get_blog_page_object($blogPageId);
	$labelId = (int)$blog->getBlogLabelId();
	$blogUrl = soycms_get_site_url_by_frontcontroller().$blog->getUri()."/".$blog->getEntryPageUri()."/";
	if(is_numeric(strpos($blogUrl, "//"))) $blogUrl = str_replace("//", "/", $blogUrl);

	$dao = soycms_get_hash_table_dao("entry");

	try{
		$res = $dao->executeQuery(
			"SELECT osm.*, ent.title FROM OpenStreetMap osm ".
			"INNER JOIN Entry ent ".
			"ON osm.entry_id = ent.id ".
			"WHERE osm.entry_id IN (".
				"SELECT entry_id FROM EntryLabel ".
				"WHERE label_id = :labelId".
			") ".
			"AND osm.latitude IS NOT NULL ".
			"AND osm.longitude IS NOT NULL ".
			"AND ent.isPublished = ".Entry::ENTRY_ACTIVE. " ".
			"AND ent.openPeriodStart < :now ".
			"AND ent.openPeriodEnd > :now",
			array(":labelId" => $labelId, ":now" => time())
		);
	}catch(Exception $e){
		$res = array();
	}

	if(!count($res)) return "";
	
	$html = array();
	$html[] = "<script>";

	//カスタムアイコン
	$osmPlugin = CMSPlugin::loadPluginConfig("openstreetmap");
	$iconConfig = ($osmPlugin instanceof OSMPlugin) ? $osmPlugin->getIconConfig() : array();

	SOY2::import("site_include.plugin.openstreetmap.util.OSMUtil");
	$icoCfg = (is_array($iconConfig)) ? OSMUtil::organizeIconConfig($iconConfig) : array();

	if(count($icoCfg)){
		foreach($icoCfg as $hash => $cfg){
			$html[] = soycms_osm_module_build_custom_icon($hash, $cfg);
		}
	}
	
	$html[] = "let features = [];";
	foreach($res as $v){
		$obj = soycms_osm_module_build_feature($v, $blogUrl);
		if(!strlen($obj)) continue;
		$html[] = "features.push(".$obj.");";
	}
	
	$html[] = file_get_contents(OSM_PLUGIN_DIR."js/feature.js");
	$html[] = "</script>";
	return implode("\n", $html);
}
