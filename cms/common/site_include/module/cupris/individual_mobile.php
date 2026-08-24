<?php
/**
 * 月:1、火:2、水:3、木:4、金:5、土:6、日:7
 * @param int
 * @return string
 */
function build_individual_mobile_day_of_week(int $w){
	switch($w){
		case 1:
			return "月曜日";
		case 2:
			return "火曜日";
		case 3:
			return "水曜日";
		case 4:
			return "木曜日";
		case 5:
			return "金曜日";
		case 6:
			return "土曜日";
		case 7:
			return "日曜日";
	}
}
/**
 * @param string
 * @param int
 * @return string
 */
function build_individual_mobile_item(string $seat, int $w){
	switch($seat){
		case "休":
			return "<span class=\"status-holiday inline-flex items-center justify-center w-8 h-8 rounded-full text-xs\">休</span>";
		case "0":
			return "<span class=\"status-full inline-flex items-center justify-center w-8 h-8 rounded-full\">✕</span>";
		case "1":
			return "<span class=\"status-vacant inline-flex items-center justify-center w-8 h-8 rounded-full shadow-sm\">◯</span>";
		default:
			return "<span class=\"text-slate-300\">-</span>";
	}
}
function soycms_individual_mobile(string $html, WebPage $page){
	$html = array();
		
	SOY2::import("site_include.plugin.cupris_availability.util.CAUtil");
	$csv = CAUtil::readCsv();

	$line = 1;
	for($w = 1; $w < 8; $w++){
		$dayOfWeek = build_individual_mobile_day_of_week($w);

		switch($w){
			case 6:
				$colorClass = "bg-blue-400";
				break;
			case 7:
				$colorClass = "bg-red-400";
				break;
			default:
				$colorClass = "bg-orange-400";
				break;
		}

		$html[] = "<!-- ".$dayOfWeek." -->";
		$html[] = "<div class=\"p-4\">";
		$html[] = "<div class=\"font-bold text-slate-700 mb-3 flex items-center gap-2\">";
		$html[] = "<span class=\"w-2 h-6 ".$colorClass." rounded-full\"></span>".$dayOfWeek;
		$html[] = "</div>";
		$html[] = "<div class=\"grid grid-cols-3 gap-2\">";
		
		for($l = 1; $l < 4; $l++){
			switch($l){
				case 1:
					$label = "9:30";
					break;
				case 2:
					$label = "10:30";
					break;
				case 3:
					$label = "11:30";
					break;
			}
			$html[] = "<div class=\"flex flex-col items-center p-2 bg-amber-50/30 rounded-lg\">";
			$html[] = "<span class=\"text-[10px] text-slate-400 mb-1\">".$label."</span>";
			$html[] = build_individual_mobile_item($csv[$l][$w], $w);
			$html[] = "</div>";
		}
		$html[] = "</div>";
		if($w === 6){
			$html[] = "<p class=\"text-[10px] text-slate-500 mt-3\">＊土曜日は言語聴覚士による個別指導枠が別にあります。ご興味のある方はお問い合わせ下さい。</p>";
		}
		$html[] = "</div>";
	}

	return implode("\n", $html);
}
