<?php
$html = array();

//説明文
$html[] = "<p class=\"multiple_page_form_plugin_description\">\n" . nl2br($description) . "\n</p>";

$values = MPFRouteUtil::getAllPageValues();
if(count($values)){
	foreach($values as $v){
		$html[] = "<div>";
		$html[] = "<label>" . $v["label"] . "</label><br>";

		$html[] = nl2br(htmlspecialchars($v["value"], ENT_QUOTES, "UTF-8"));;
		$html[] = "</div>";
	}
}

//選択肢の箇所
$url = $_SERVER["REQUEST_URI"];
$token = soy2_get_token();

foreach($items as $idx => $item){
	if(!isset($item["item"]) || !strlen($item["item"])) continue;
	$html[] = "<a href=\"" . $url . "?idx=" . $idx . "&soy2_token=" . $token . "\">" . $item["item"] . "</a>";
}

//戻るがある場合
if(isset($prev) && strlen($prev)){
	$html[] = "<br><a href=\"" . $url . "?prev=" . $prev . "&soy2_token=" . $token . "\">戻る</a>";
}

echo implode("\n", $html);
