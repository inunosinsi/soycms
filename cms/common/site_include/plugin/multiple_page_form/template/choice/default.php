<?php
/**
 * $description 説明文
 * $items 選択肢が格納された配列
 * $prev 前のページ
 */

$html = array();

//説明文
$html[] = "<p class=\"multiple_page_form_plugin_description\">\n" . nl2br($description) . "\n</p>";

//$html[] = "<form action=\"" . $_SERVER["REQUEST_URI"] . "\" method=\"POST\">";
//$html[] = "<input type=\"hidden\" name=\"soy2_token\" value=\"" . soy2_get_token() . "\">";
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


//$html[] = "</form>";

echo implode("\n", $html);
