<?php
/**
 * 使用できる変数
 * $items：各項目
 * $values：各項目毎の値
 * $isFirstView：このページが初回表示であるか？
 */

SOY2::import("site_include.plugin.multiple_page_form.util.MPFTypeFormUtil");

$html = array();

//説明文
$html[] = "<p class=\"multiple_page_form_plugin_description\">\n" . nl2br($description) . "\n</p>";

$html[] = "<form action=\"" . $_SERVER["REQUEST_URI"] . "\" method=\"POST\" id=\"mpf_prev_form\">";
$html[] = "<input type=\"hidden\" name=\"soy2_token\" value=\"" . soy2_get_token() . "\">";

foreach($items as $idx => $item){
	if(!isset($item["type"])) continue;
	$html[] = "<div>";
	$html[] = "<label>" . $item["name"] . "</label><br>";

	$v = (isset($values[$idx]["value"])) ? $values[$idx]["value"] : "";
	$html[] = MPFTypeFormUtil::getForm($idx, $item, $v, $isFirstView);
	$html[] = "</div>";
}

//戻るがある場合
if(isset($prev) && strlen($prev)){
	$html[] = "<input type=\"button\" name=\"prev\" value=\"戻る\" id=\"mpf_prev_button\">";
}
$html[] = "<input type=\"submit\" name=\"next\" value=\"次へ\">";


$html[] = "</form>";

$html[] = <<<HTML
<script>
document.getElementById("mpf_prev_button").addEventListener("click", function(){
	var form = document.getElementById("mpf_prev_form");
	form.noValidate = true;
	form.submit();
});
function mpf_checkbox_required(ele){
	var chks = document.getElementsByClassName(ele.className);
	if(chks.length){
		var on = false;	//一度でもチェックがあれば、チェックボックスからrequiredを外す
		for(var i = 0; i < chks.length; i++){
			if(!on && chks[i].checked) on = true;
		}

		for(var i = 0; i < chks.length; i++){
			chks[i].required = !on;
		}
	}
}
</script>
HTML;

echo implode("\n", $html);
