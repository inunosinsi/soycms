<?php
/**
 * @param string
 * @return string
 */
function build_group_desktop_item(string $seat){
	if(is_numeric($seat)){
		$seat = (int)$seat;
		switch($seat){
			case 0:
				return "<td class=\"p-2\"><span class=\"status-full inline-flex items-center justify-center w-8 h-8 rounded-full\">✕</span></td>";
			default:
				if($seat >= 3){
					return "<td class=\"p-2\"><span class=\"status-vacant inline-flex items-center justify-center w-8 h-8 rounded-lg bg-emerald-100 text-emerald-800 font-bold\">".$seat."</span></td>";
				}else{
					return "<td class=\"p-2\"><span class=\"status-vacant inline-flex items-center justify-center w-8 h-8 rounded-lg\">".$seat."</span></td>";
				}
		}
	}

	switch($seat){
		case "休":
			return "<td class=\"p-2\"><span class=\"status-holiday inline-flex items-center justify-center w-8 h-8 rounded-full text-xs\">休</span></td>";
		default:
			return "<td class=\"p-2\"><span class=\"text-slate-300\">-</span></td>";
	}
}

function soycms_group_desktop(string $html, WebPage $page){
	$html = array();
	$html[] = "<table class=\"w-full text-center border-collapse\">";
	$html[] = "<thead>";
	$html[] = "<tr class=\"bg-emerald-50/50 border-b border-emerald-100 text-sm font-bold text-slate-700\">";
	$html[] = "<th class=\"py-3 px-4 text-left\">支援枠 / クラス</th>";
	$html[] = "<th class=\"py-3 px-2\">月</th>";
	$html[] = "<th class=\"py-3 px-2\">火</th>";
	$html[] = "<th class=\"py-3 px-2\">水</th>";
	$html[] = "<th class=\"py-3 px-2\">木</th>";
	$html[] = "<th class=\"py-3 px-2\">金</th>";
	$html[] = "<th class=\"py-3 px-2\">土</th>";
	$html[] = "<th class=\"py-3 px-2\">日</th>";
	$html[] = "</tr>";
	$html[] = "</thead>";
	$html[] = "<tbody class=\"divide-y divide-emerald-50/60 text-sm text-slate-700\">";

	SOY2::import("site_include.plugin.cupris_availability.util.CAUtil");
	$csv = CAUtil::readCsv(CAUtil::MODE_GROUP);

	foreach($csv as $n => $array){
		if($n === 0) continue;	//ラベルの行はスルー

		switch($n){
			case 1:	//午前枠
				$html[] = "<!-- 午前枠 -->";
				$html[] = "<tr class=\"hover:bg-emerald-50/10 transition-colors\">";
				$html[] = "<td class=\"py-4 px-4 font-bold text-slate-600 text-left\">";
				$html[] = "<span class=\"block\">午前枠</span>";
				$html[] = "<span class=\"text-xs text-slate-400 font-normal\">9:30 ~ 11:00</span>";
				$html[] = "<span class=\"text-xs text-slate-400 font-normal\">火曜日のみ12時まで</span>";
				$html[] = "</td>";

				for($i = 1; $i <= 7; $i++){
					$html[] = build_group_desktop_item(trim($array[$i]));
				}
				$html[] = "</tr>";
				break;
			case 2:	//午後枠の行
				$html[] = "<!-- 午後枠 -->";
				$html[] = "<tr class=\"hover:bg-emerald-50/10 transition-colors bg-emerald-50/5\">";
				$html[] = "<td class=\"py-4 px-4 font-bold text-slate-600 text-left\">";
				$html[] = "<span class=\"block\">午後枠</span>";
				$html[] = "<span class=\"text-xs text-slate-500 font-normal\">14:00 ~ 16:40</span>";
				$html[] = "</td>";
				for($i = 1; $i <= 7; $i++){
					$html[] = "<td class=\"p-2\"></td>";
				}
				$html[] = "</tr>";
				break;
			default:
				switch($n){
					case 3:	//年長（午後枠グループ
						$html[] = "<!-- 年長（午後枠グループ） -->";
						$label  = "年長クラス";
						break;
					case 4:	//年中（午後枠グループ）
						$html[] = "<!-- 年中（午後枠グループ） -->";
						$label = "年中クラス";
						break;
					case 5:	//年少（午後枠グループ）
						$html[] = "<!-- 年少（午後枠グループ） -->";
						$label = "年少クラス";
				}
				
				$html[] = "<tr class=\"hover:bg-emerald-50/15 transition-colors bg-emerald-50/20\">";
				$html[] = "<td class=\"py-4 pl-10 pr-4 font-medium text-slate-600 text-left\">";
				$html[] = "<span class=\"text-emerald-400 font-bold mr-1\">└</span>".$label."</td>";
				for($i = 1; $i <= 7; $i++){
					$html[] = build_group_desktop_item(trim($array[$i]));
				}
				$html[] = "</tr>";
				break;
		}
	}
	
	$html[] = "</tbody>";
	$html[] = "</table>";

	return implode("\n", $html);
}
