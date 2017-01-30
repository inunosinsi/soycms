<?php
function soyshop_parts_topic_path($html, $page){

	$top_url = soyshop_get_site_url();
	$title = $page->getPageObject()->getName();

	echo <<<HTML
	<a href="${top_url}">トップページ</a>&nbsp;&gt;&nbsp;${title}
HTML;

}
?>