<?php
function soyshop_parts_topic_path(string $html, HTMLPage $page){
	echo "<a href=\"".soyshop_get_site_url()."\">トップページ</a>&nbsp;&gt;&nbsp;".$page->getPageObject()->getName();
}
