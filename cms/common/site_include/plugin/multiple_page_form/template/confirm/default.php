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

$html[] = "<form action=\"" . $_SERVER["REQUEST_URI"] . "\" method=\"POST\" id=\"mpf_prev_form\">";
$html[] = "<input type=\"hidden\" name=\"soy2_token\" value=\"" . soy2_get_token() . "\">";

//戻るがある場合
if(isset($prev) && strlen($prev)){
	$html[] = "<input type=\"submit\" name=\"prev\" value=\"戻る\">";
}
$html[] = "<input type=\"submit\" name=\"next\" value=\"送信\">";

$html[] = "</form>";

echo implode("\n", $html);
