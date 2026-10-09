<?php 

class OSMEntryFormComponent {

	/**
	 * @param int
	 * @param array
	 * @return string
	 */
	public static function build(int $entryId, array $iconConfig){
		SOY2::import("site_include.plugin.openstreetmap.util.OSMUtil");
		$cfg = OSMUtil::organizeIconConfig($iconConfig);
	
		$obj = SOY2Logic::createInstance("site_include.plugin.openstreetmap.logic.OSMLogic")->getByEntryId($entryId);

		$lat = (string)$obj->getLatitude();
		$lng = (string)$obj->getLongitude();
		$zoom = $obj->getZoom();
		
		$html = array();
		$html[] = "<div class=\"alert alert-success\">";
		$html[] = "<h4>OpenStreetMap&nbsp</h4>";
		$html[] = "<div class=\"form-group form-inline\">";
		$html[] = "<label>緯度(lat)</label>";
		$html[] = "<input type=\"text\" class=\"form-control\" name=\"OpenStreetMap[latitude]\" value=\"".$lat."\" placeholder=\"00.00000\">";
		$html[] = "<label>経度(lon)</label>";
		$html[] = "<input type=\"text\" class=\"form-control\" name=\"OpenStreetMap[longitude]\" value=\"".$lng."\" placeholder=\"000.00000\">";
		$html[] = "<label>ズーム(zoom)</label>";
		$html[] = "<select class=\"form-control\" name=\"OpenStreetMap[zoom]\">";
		foreach(range(1, 20) as $z){
			if($z == $zoom){
				$html[] = "<option value=\"".$z."\" selected=\"selected\">".$z."</option>";
			}else{
				$html[] = "<option value=\"".$z."\">".$z."</option>";
			}
		}
		$html[] = "</select>";
		//$html[] = "<input type=\"text\" class=\"form-control\" name=\"OpenStreetMap[longitude]\" value=\"".$lng."\" placeholder=\"000.00000\">";
		$html[] = "</div>";

		$html[] = "<div class=\"form-group form-inline\">";
		$html[] = "<label>ラベル</label>";
		$html[] = "<input type=\"text\" class=\"form-control\" name=\"OpenStreetMap[label]\" value=\"".(string)$obj->getLabel()."\" placeholder=\"地名や構造物等\">";

		$html[] = "<label>アイコン</label>";
		$html[] = "<select class=\"form-control\" name=\"OpenStreetMap[icon]\">";
		$html[] = "<option value=\"\"></option>";
		if(count($cfg)){
			$selected = $obj->getIcon();
			foreach($cfg as $hash => $_arr){
				$label = (isset($_arr["label"]) && strlen($_arr["label"])) ? $_arr["label"] : $_arr["filename"];
				if($obj->getIcon() == $hash){
					$html[] = "<option value=\"".$hash."\" selected=\"selected\">".htmlspecialchars($label, ENT_QUOTES, "UTF-8")."</option>";
				}else{
					$html[] = "<option value=\"".$hash."\">".htmlspecialchars($label, ENT_QUOTES, "UTF-8")."</option>";
				}
			}
		}
		$html[] = "</select>";
		$html[] = "</div>";

		$osmUrl = "https://www.openstreetmap.org/";
		$isPreview = (strlen($lat) && strlen($lng));

		if($isPreview){
			if(!function_exists("soycms_osm_extract_width_and_height")){
				include_once(SOY2::RootDir()."site_include/module/openstreetmap/osm_link.php");
			}
			$html[] = "<div>";
			$html[] = "<label>プレビュー</label>";
			$html[] = "<span style=\"font-size:0.8em;\"><a href=\"".$osmUrl."/#map=19/".$lat."/".$lng."\" target=\"_blank\" rel=\"noopener\" style=\"text-decoration:underline;\">OpenStreetMapを開く</a></span><br>";

			$bbox = soycms_osm_calculate_bbox((float)$lat, (float)$lng, $zoom, 320, 240);
			$endpoint = "https://www.openstreetmap.org/export/embed.html?bbox=".rawurlencode($bbox)."&layer=mapnik&marker=".rawurlencode($lat.",".$lng);
			$html[] = "<iframe width=\"320\" height=\"240\" src=\"".$endpoint."\"></iframe>";
			$html[] = "</div>";
		}else{
			$html[] = "<div style=\"font-size:0.8em;\"><a href=\"".$osmUrl."\" target=\"_blank\" rel=\"noopener\" style=\"text-decoration:underline;\">OpenStreetMapを開く</a></div>";
		}
		$html[] = "</div>";
		return implode("\n", $html);
	}
}
