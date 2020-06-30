<?php
/*
このブロックは全てのブログページでご利用になれます。

このブロックは、当該ブログのトップページへのリンクを出力します。

このブロックは必ずAタグに使用してください。
<a b_block:id="top_link">ブログのトップへ</a b_block:id="top_link">
*/
function soy_cms_blog_output_top_link($page){
	$page->addLink("top_link", array(
		"soy2prefix" => "b_block",
		"link" => $page->getTopPageURL(true)
	));
}
