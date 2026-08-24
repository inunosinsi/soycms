<?php
function soycms_individual_desktop(string $html, WebPage $page){
	$html = array();
	$html[] = "<table class=\"w-full text-center border-collapse\">";
	$html[] = "<thead>";
	$html[] = "<tr class=\"bg-amber-50/50 border-b border-orange-100 text-sm font-bold text-slate-700\">";
	$html[] = "<th class=\"py-3 px-4 text-left\">時間</th>";
	$html[] = "<th class=\"py-3 px-2\">月</th>";
	$html[] = "<th class=\"py-3 px-2\">火</th>";
	$html[] = "<th class=\"py-3 px-2\">水</th>";
	$html[] = "<th class=\"py-3 px-2\">木</th>";
	$html[] = "<th class=\"py-3 px-2\">金</th>";
	$html[] = "<th class=\"py-3 px-2\">土</th>";
	$html[] = "<th class=\"py-3 px-2\">日</th>";
	$html[] = "</tr>";
	$html[] = "</thead>";
	$html[] = "<tbody class=\"divide-y divide-orange-50 text-sm text-slate-700\">";

	SOY2::import("site_include.plugin.cupris_availability.util.CAUtil");
	$csv = CAUtil::readCsv();
	
	foreach($csv as $n => $array){
		if($n === 0) continue;	//ラベルの行はスルー

		switch($n){
			case 1:
				$label = "9:30 ~";
				break;
			case 2:
				$label = "10:30 ~";
				break;
			case 3:
				$label = "11:30 ~";
				break;
		}

		$html[] = "<!-- ".$label." -->";
		$html[] = "<tr class=\"hover:bg-amber-50/20 transition-colors\">";
		$html[] = "<td class=\"py-4 px-4 font-bold text-slate-600 text-left\">".$label."</td>";

		for($i = 1; $i <= 7; $i++){
			switch(trim($array[$i])){
				case "休":
					$html[] = "<td class=\"p-2\"><span class=\"status-holiday inline-flex items-center justify-center w-8 h-8 rounded-full text-xs\">休</span></td>";
					break;
				case "0":
					$html[] = "<td class=\"p-2\"><span class=\"status-full inline-flex items-center justify-center w-8 h-8 rounded-full\">✕</span></td>";
					break;
				case "1":
					$html[] = "<td class=\"p-2\"><span class=\"status-vacant inline-flex items-center justify-center w-8 h-8 rounded-full shadow-sm\">◯</span></td>";
					break;
				default:
					$html[] = "<td class=\"p-2\"><span class=\"text-slate-300\">-</span></td>";
			}
		}
		$html[] = "</tr>";
	}
	
	$html[] = "</tbody>";
	$html[] = "</table>";
	return implode("\n", $html);
}
