<?php
$html = array();

//説明文
$html[] = "<p class=\"multiple_page_form_plugin_description\">\n" . nl2br($description) . "\n</p>";

echo implode("\n", $html);
