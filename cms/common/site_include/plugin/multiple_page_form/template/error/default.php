<?php
$html = array();

//説明文
$html[] = "<p class=\"multiple_page_form_plugin_description\">error</p>";

$html[] = "<form action=\"" . $_SERVER["REQUEST_URI"] . "\" method=\"POST\" id=\"mpf_prev_form\">";
$html[] = "<input type=\"hidden\" name=\"soy2_token\" value=\"" . soy2_get_token() . "\">";
$html[] = "	<input type=\"submit\" name=\"prev\" value=\"戻る\">";
$html[] = "</form>";

echo implode("\n", $html);
