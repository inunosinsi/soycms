<?php
/**
 * 月:1、火:2、水:3、木:4、金:5、土:6、日:7
 * @param int
 * @return string
 */
function build_group_mobile_day_of_week(int $w){
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
function build_group_mobile_afternoon_item(string $seat){
	if(is_numeric($seat)){
		$seat = (int)$seat;
		switch($seat){
			case 0:
				return "<span class=\"status-full inline-flex items-center justify-center w-8 h-8 rounded-full\">✕</span>";
			default:
				if($seat >= 3){
					return "<span class=\"status-vacant inline-flex items-center justify-center w-8 h-8 rounded-lg bg-emerald-100 text-emerald-800 font-bold\">".$seat."</span>";
				}else{
					return "<span class=\"status-vacant inline-flex items-center justify-center w-8 h-8 rounded-lg\">".$seat."</span>";
				}
		}
	}

	switch($seat){
		case "休":
			return "<span class=\"status-holiday inline-flex items-center justify-center w-8 h-8 rounded-full text-xs\">休</span>";
		default:
			return "<span class=\"text-slate-300\">-</span>";
	}
}
function soycms_group_mobile(string $html, WebPage $page){
	$html = array();
		
	SOY2::import("site_include.plugin.cupris_availability.util.CAUtil");
	$csv = CAUtil::readCsv(CAUtil::MODE_GROUP);
	
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
				$colorClass = "bg-emerald-500";
				break;
		}

		$html[] = "<!-- ".$dayOfWeek." -->";
		$html[] = "<div class=\"p-4\">";
		$html[] = "<div class=\"font-bold text-slate-700 mb-3 flex items-center gap-2\">";
		$html[] = "<span class=\"w-2 h-6 ".$colorClass." rounded-full\"></span>".$dayOfWeek;
		$html[] = "</div>";
		$html[] = "<div class=\"space-y-3\">";
		$html[] = "<div class=\"flex items-center justify-between p-3 bg-emerald-50/30 rounded-xl\">";
		$html[] = "<div>";
		$html[] = "<span class=\"text-sm font-medium text-slate-600\">午前枠</span>";
		$html[] = "<span class=\"text-[10px] text-slate-400 ml-1\">9:30〜11:00</span>";
		$html[] = "</div>";
		$html[] = "<span class=\"status-holiday inline-flex items-center justify-center w-8 h-8 rounded-full text-xs\">休</span>";
		$html[] = "</div>";
		$html[] = "<div class=\"p-3 bg-emerald-50/30 rounded-xl\">";
		$html[] = "<span class=\"text-sm font-medium text-slate-600\">午後枠</span>";
		$html[] = "<span class=\"text-[10px] text-slate-400 ml-1\">14:00〜16:40</span>";
		$html[] = "<div class=\"grid grid-cols-3 gap-2\">";
		
		
				
		for($l = 3; $l < 6; $l++){
			switch($l){
				case 1:
					$label = "年少";
					break;
				case 2:
					$label = "年中";
					break;
				case 3:
					$label = "年長";
					break;
			}

			$html[] = "<div class=\"flex flex-col items-center p-2 bg-emerald-50/10 rounded-lg\">";
			$html[] = "<span class=\"text-[10px] text-slate-400 mb-1\">".$label."</span>";
			$html[] = build_group_mobile_afternoon_item($csv[$l][$w]);
			$html[] = "</div>";
		}
		$html[] = "</div>";
		$html[] = "</div>";
		$html[] = "</div>";
		$html[] = "</div>";
	}

	return implode("\n", $html);
}

/**
<!-- 火曜日 -->
<div class="p-4">
<div class="font-bold text-slate-700 mb-3 flex items-center gap-2">
<span class="w-2 h-6 bg-emerald-500 rounded-full"></span>火曜日
</div>
<div class="space-y-3">
<div class="flex items-center justify-between p-3 bg-emerald-50/30 rounded-xl">
<div>
<span class="text-sm font-medium text-slate-600">午前枠</span>
<span class="text-[10px] text-slate-400 ml-1">9:30〜12:00</span>
<span class="block text-[9px] text-slate-400">※火曜日のみ12時まで</span>
</div>

</div>
<div class="p-3 bg-emerald-50/30 rounded-xl">
<span class="text-sm font-medium text-slate-600">午後枠</span>
<span class="text-[10px] text-slate-400 ml-1">14:00〜16:40</span>
<div class="grid grid-cols-3 gap-2">
<div class="flex flex-col items-center p-2 bg-emerald-50/10 rounded-lg">
<span class="text-[10px] text-slate-400 mb-1">年長</span>

</div>
<div class="flex flex-col items-center p-2 bg-emerald-50/10 rounded-lg">
<span class="text-[10px] text-slate-400 mb-1">年中</span>
<span class="status-full inline-flex items-center justify-center w-8 h-8 rounded-full">✕</span>
</div>
<div class="flex flex-col items-center p-2 bg-emerald-50/10 rounded-lg">
<span class="text-[10px] text-slate-400 mb-1">年少</span>

</div>
</div>
</div>
</div>
</div>
<!-- 水曜日 -->
<div class="p-4">
<div class="font-bold text-slate-700 mb-3 flex items-center gap-2">
<span class="w-2 h-6 bg-emerald-500 rounded-full"></span>水曜日
</div>
<div class="space-y-3">
<div class="flex items-center justify-between p-3 bg-emerald-50/30 rounded-xl">
<div>
<span class="text-sm font-medium text-slate-600">午前枠</span>
<span class="text-[10px] text-slate-400 ml-1">9:30〜11:00</span>
</div>
<span class="status-vacant inline-flex items-center justify-center w-8 h-8 rounded-lg">2</span>
</div>
<div class="p-3 bg-emerald-50/30 rounded-xl">
<span class="text-sm font-medium text-slate-600">午後枠</span>
<span class="text-[10px] text-slate-400 ml-1">14:00〜16:40</span>
<div class="grid grid-cols-3 gap-2">
<div class="flex flex-col items-center p-2 bg-emerald-50/10 rounded-lg">
<span class="text-[10px] text-slate-400 mb-1">年長</span>
<span class="status-full inline-flex items-center justify-center w-8 h-8 rounded-full">✕</span>
</div>
<div class="flex flex-col items-center p-2 bg-emerald-50/10 rounded-lg">
<span class="text-[10px] text-slate-400 mb-1">年中</span>
<span class="status-vacant inline-flex items-center justify-center w-8 h-8 rounded-lg">2</span>
</div>
<div class="flex flex-col items-center p-2 bg-emerald-50/10 rounded-lg">
<span class="text-[10px] text-slate-400 mb-1">年少</span>
<span class="status-full inline-flex items-center justify-center w-8 h-8 rounded-full">✕</span>
</div>
</div>
</div>
</div>
</div>
<!-- 木曜日 -->
<div class="p-4">
<div class="font-bold text-slate-700 mb-3 flex items-center gap-2">
<span class="w-2 h-6 bg-emerald-500 rounded-full"></span>木曜日
</div>
<div class="space-y-3">
<div class="flex items-center justify-between p-3 bg-emerald-50/30 rounded-xl">
<div>
<span class="text-sm font-medium text-slate-600">午前枠</span>
<span class="text-[10px] text-slate-400 ml-1">9:30〜11:00</span>
</div>
<span class="status-vacant inline-flex items-center justify-center w-8 h-8 rounded-lg">2</span>
</div>
<div class="p-3 bg-emerald-50/30 rounded-xl">
<span class="text-sm font-medium text-slate-600">午後枠</span>
<span class="text-[10px] text-slate-400 ml-1">14:00〜16:40</span>
<div class="grid grid-cols-3 gap-2">
<div class="flex flex-col items-center p-2 bg-emerald-50/10 rounded-lg">
<span class="text-[10px] text-slate-400 mb-1">年長</span>
<span class="status-full inline-flex items-center justify-center w-8 h-8 rounded-full">✕</span>
</div>
<div class="flex flex-col items-center p-2 bg-emerald-50/10 rounded-lg">
<span class="text-[10px] text-slate-400 mb-1">年中</span>
<span class="status-full inline-flex items-center justify-center w-8 h-8 rounded-full">✕</span>
</div>
<div class="flex flex-col items-center p-2 bg-emerald-50/10 rounded-lg">
<span class="text-[10px] text-slate-400 mb-1">年少</span>
<span class="status-full inline-flex items-center justify-center w-8 h-8 rounded-full">✕</span>
</div>
</div>
</div>
</div>
</div>
<!-- 金曜日 -->
<div class="p-4">
<div class="font-bold text-slate-700 mb-3 flex items-center gap-2">
<span class="w-2 h-6 bg-emerald-500 rounded-full"></span>金曜日
</div>
<div class="space-y-3">
<div class="flex items-center justify-between p-3 bg-emerald-50/30 rounded-xl">
<div>
<span class="text-sm font-medium text-slate-600">午前枠</span>
<span class="text-[10px] text-slate-400 ml-1">9:30〜11:00</span>
</div>
<span class="status-vacant inline-flex items-center justify-center w-8 h-8 rounded-lg">1</span>
</div>
<div class="p-3 bg-emerald-50/30 rounded-xl">
<span class="text-sm font-medium text-slate-600">午後枠</span>
<span class="text-[10px] text-slate-400 ml-1">14:00〜16:40</span>
<div class="grid grid-cols-3 gap-2">
<div class="flex flex-col items-center p-2 bg-emerald-50/10 rounded-lg">
<span class="text-[10px] text-slate-400 mb-1">年長</span>
<span class="status-vacant inline-flex items-center justify-center w-8 h-8 rounded-lg">1</span>
</div>
<div class="flex flex-col items-center p-2 bg-emerald-50/10 rounded-lg">
<span class="text-[10px] text-slate-400 mb-1">年中</span>
<span class="status-full inline-flex items-center justify-center w-8 h-8 rounded-full">✕</span>
</div>
<div class="flex flex-col items-center p-2 bg-emerald-50/10 rounded-lg">
<span class="text-[10px] text-slate-400 mb-1">年少</span>
<span class="status-full inline-flex items-center justify-center w-8 h-8 rounded-full">✕</span>
</div>
</div>
</div>
</div>
</div>
<!-- 土曜日 -->
<div class="p-4">
<div class="font-bold text-slate-700 mb-3 flex items-center gap-2">
<span class="w-2 h-6 bg-blue-400 rounded-full"></span>土曜日
</div>
<div class="space-y-3">
<div class="flex items-center justify-between p-3 bg-emerald-50/30 rounded-xl">
<div>
<span class="text-sm font-medium text-slate-600">午前枠</span>
<span class="text-[10px] text-slate-400 ml-1">9:30〜11:00</span>
</div>
<span class="text-slate-300">-</span>
</div>
<div class="p-3 bg-emerald-50/30 rounded-xl">
<span class="text-sm font-medium text-slate-600">午後枠</span>
<span class="text-[10px] text-slate-400 ml-1">14:00〜16:40</span>
<div class="grid grid-cols-3 gap-2">
<div class="flex flex-col items-center p-2 bg-emerald-50/10 rounded-lg">
<span class="text-[10px] text-slate-400 mb-1">年長</span>
<span class="status-vacant inline-flex items-center justify-center w-8 h-8 rounded-lg">2</span>
</div>
<div class="flex flex-col items-center p-2 bg-emerald-50/10 rounded-lg">
<span class="text-[10px] text-slate-400 mb-1">年中</span>
<span class="status-vacant inline-flex items-center justify-center w-8 h-8 rounded-lg">2</span>
</div>
<div class="flex flex-col items-center p-2 bg-emerald-50/10 rounded-lg">
<span class="text-[10px] text-slate-400 mb-1">年少</span>
<span class="status-full inline-flex items-center justify-center w-8 h-8 rounded-full">✕</span>
</div>
</div>
</div>
</div>
</div>
<!-- 日曜日 -->
<div class="p-4 bg-red-50/10">
<div class="font-bold text-slate-700 mb-3 flex items-center gap-2">
<span class="w-2 h-6 bg-red-400 rounded-full"></span>日曜日
</div>
<div class="space-y-3">
<div class="flex items-center justify-between p-3 bg-emerald-50/30 rounded-xl">
<div>
<span class="text-sm font-medium text-slate-600">午前枠</span>
<span class="text-[10px] text-slate-400 ml-1">9:30〜11:00</span>
</div>
<span class="status-holiday inline-flex items-center justify-center w-8 h-8 rounded-full text-xs">休</span>
</div>
<div class="p-3 bg-emerald-50/30 rounded-xl">
<span class="text-sm font-medium text-slate-800">午後枠</span>
<span class="text-[10px] text-slate-400 ml-1">14:00〜16:40</span>
<div class="grid grid-cols-3 gap-2">
<div class="flex flex-col items-center p-2 bg-red-50/10 rounded-lg">
<span class="text-[10px] text-slate-400 mb-1">年長</span>
<span class="status-holiday inline-flex items-center justify-center w-8 h-8 rounded-full text-xs">休</span>
</div>
<div class="flex flex-col items-center p-2 bg-red-50/10 rounded-lg">
<span class="text-[10px] text-slate-400 mb-1">年中</span>
<span class="status-holiday inline-flex items-center justify-center w-8 h-8 rounded-full text-xs">休</span>
</div>
<div class="flex flex-col items-center p-2 bg-red-50/10 rounded-lg">
<span class="text-[10px] text-slate-400 mb-1">年少</span>
<span class="status-holiday inline-flex items-center justify-center w-8 h-8 rounded-full text-xs">休</span>
</div>
</div>
</div>
</div>
</div>
**/
