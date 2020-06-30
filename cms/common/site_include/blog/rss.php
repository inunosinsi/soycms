<?php

/**
 * RSS2.0を出力
 */
function soy_cms_blog_output_rss($page, $entries, $title = null, $charset = "UTF-8"){
	function soy_cms_blog_output_rss_h($string){
		return htmlspecialchars($string, ENT_QUOTES, "UTF-8");
	}
	function soy_cms_blog_output_rss_cdata($html){
		//タグを除去してエンティティを戻す
		$text = SOY2HTML::ToText($html);
		// ]]> があったらそこで分割する
		$cdata = "<![CDATA[" . str_replace("]]>", "]]]]><![CDATA[>", $text) ."]]>";
		return $cdata;
	}

	$entry = @$entries[0];
	$update = ($entry) ? $entry->getUdate() : $page->page->getUdate();
	$entryPageUrl = $page->getEntryPageURL(true);

	if(is_null($title)) $title = $page->page->getTitle();

	$xml = array();

	$xml[] = '<?xml version="1.0" encoding="'.$charset.'" ?>';
	$xml[] = '<rss version="2.0">';
	$xml[] = '<channel>';
	$xml[] = '<title>'.soy_cms_blog_output_rss_h($title).'</title>';
	$xml[] = '<link>'.soy_cms_blog_output_rss_h($page->getTopPageURL(true)).'</link>';
	$xml[] = '<description>'.soy_cms_blog_output_rss_h($page->page->getDescription()).'</description>';
	$xml[] = '<pubDate>'.soy_cms_blog_output_rss_h(date('r',$update)).'</pubDate>';
	$xml[] = '<generator>'.'SOY CMS '.SOYCMS_VERSION.'</generator>';
	$xml[] = '<docs>http://blogs.law.harvard.edu/tech/rss</docs>';
	$xml[] = '<language>'.( defined("SOYCMS_LANGUAGE") ? SOYCMS_LANGUAGE : "ja" ).'</language>';

	foreach($entries as $entry){

		$buildDate = max($entry->getCdate(),$entry->getUdate());
		$update = max($buildDate, $update);

		$xml[] = '<item>';
		$xml[] = '<title>'.soy_cms_blog_output_rss_h($entry->getTitle()).'</title>';
		$xml[] = '<link>'.soy_cms_blog_output_rss_h($entryPageUrl . rawurlencode($entry->getAlias())) .'</link>';
		$xml[] = '<guid isPermaLink="false">'.soy_cms_blog_output_rss_h($entryPageUrl . $entry->getId()) .'</guid>';
		$xml[] = '<pubDate>'.soy_cms_blog_output_rss_h(date('r',$entry->getCdate())).'</pubDate>';
		//$xml[] = '<lastBuildDate>'.soy_cms_blog_output_rss_h(date('r',$buildDate)).'</lastBuildDate>';
		$xml[] = '<description>'. soy_cms_blog_output_rss_cdata( ( strlen($entry->getDescription()) >0 ) ? $entry->getDescription() : $entry->getContent() ) . '</description>';
		$xml[] = '</item>';

	}

	$xml[] = '<lastBuildDate>'.soy_cms_blog_output_rss_h(date('r',$update)).'</lastBuildDate>';

	$xml[] = '</channel>';
	$xml[] = '</rss>';

	echo implode("\n",$xml);

}

/*
 * ATOM出力
 */
function soy_cms_blog_output_atom($page, $entries, $title = null, $charset = "UTF-8"){
	function soy_cms_blog_output_atom_h($string){
		return htmlspecialchars($string, ENT_QUOTES, "UTF-8");
	}
	function soy_cms_blog_output_atom_cdata($string){
		// ]]> があったらそこで分割する
		$cdata = str_replace("]]>", "]]]]><![CDATA[>", $string);
		$cdata = "<![CDATA[" . $cdata ."]]>";
		return $cdata;
	}

	$entry = @$entries[0];
	$update = ($entry) ? $entry->getUdate() : $page->page->getUdate();
	$entryPageUrl = $page->getEntryPageURL(true);
	if(is_null($title)) $title = $page->page->getTitle();

	$xml = array();

	$xml[] = '<?xml version="1.0" encoding="'.$charset.'" ?>';
	$xml[] = '<feed xml:lang="ja" xmlns="http://www.w3.org/2005/Atom">';
	$xml[] = '<title>'.soy_cms_blog_output_atom_h($title).'</title>';
	$xml[] = '<subtitle type="html">'.soy_cms_blog_output_atom_h($page->page->getDescription()).'</subtitle>';
	$xml[] = '<link rel="alternate" href="'.soy_cms_blog_output_atom_h($page->getTopPageURL(true)).'" />';
	$xml[] = '<link rel="self" type="application/atom+xml" href="'.soy_cms_blog_output_atom_h($page->getRssPageURL(true)."?feed=atom").'" />';
	$xml[] = '<author><name>'.soy_cms_blog_output_atom_h($page->page->getAuthor()).'</name></author>';
	$xml[] = '<id>'.soy_cms_blog_output_atom_h($page->getTopPageURL(true)).'</id>';

	foreach($entries as $entry){
		$buildDate = max($entry->getCdate(),$entry->getUdate());
		$update = max($buildDate, $update);
	}
	$xml[] = '<updated>'.soy_cms_blog_output_atom_h(date('c',$update)).'</updated>';

	foreach($entries as $entry){

		$buildDate = max($entry->getCdate(),$entry->getUdate());

		$xml[] = '<entry>';
		$xml[] = '<title>'.soy_cms_blog_output_atom_h($entry->getTitle()).'</title>';
		$xml[] = '<link rel="alternate" href="'. soy_cms_blog_output_atom_h($entryPageUrl . rawurlencode($entry->getAlias())) .'" type="application/xhtml+xml"/>';
		$xml[] = '<published>'.soy_cms_blog_output_atom_h(date('c',$entry->getCdate())).'</published>';
		$xml[] = '<updated>'.soy_cms_blog_output_atom_h(date('c',$buildDate)).'</updated>';
		$xml[] = '<id>'.soy_cms_blog_output_atom_h($entryPageUrl.$entry->getId()).'</id>';
		if(strlen($entry->getDescription()) >0){
			$xml[] = '<summary>'.soy_cms_blog_output_atom_h($entry->getDescription()).'</summary>';
		}
		$xml[] = '<content type="html">' . soy_cms_blog_output_atom_cdata($entry->getContent()) . '</content>';
		$xml[] = '</entry>';
	}

	$xml[] = '</feed>';

	echo implode("\n",$xml);

}

/*
 * フィードのメタ情報を出力
 * <!-- b_block:id="meta_feed_link" --><!--/b_block:id="meta_feed_link" -->
 */
function soy_cms_blog_output_meta_feed_info($page){

	$url = $page->getRssPageURL();

	$hUrl = htmlspecialchars($url, ENT_QUOTES, "UTF-8");
	$hTitle = htmlspecialchars($page->page->getTitle(), ENT_QUOTES, "UTF-8");

	$text = '<link rel="alternate" type="application/rss+xml" title="'.$hTitle.'" href="'.$hUrl.'?feed=rss" />'."\n";
	$text .= '<link rel="alternate" type="application/atom+xml" title="'.$hTitle.'" href="'.$hUrl.'?feed=atom" />';

	$page->createAdd("meta_feed_link","HTMLLabel",array(
		"html" => $text,
		"visible" => $page->page->getGenerateRssFlag(),
		"soy2prefix" => "b_block"
	));
}

/*
 *  feedのリンクを表示
 *  <a b_block:id="rss_link">RSS</a>
 *  <a b_block:id="atom_link">ATOM</a>
 */
function soy_cms_blog_output_feed_link($page){

	$url = $page->getRssPageURL(true);

	$page->createAdd("rss_link","HTMLLink",array(
		"link" => $url ."?feed=rss",
		"visible" => $page->page->getGenerateRssFlag(),
		"soy2prefix" => "b_block"
	));

	$page->createAdd("atom_link","HTMLLink",array(
		"link" => $url ."?feed=atom",
		"visible" => $page->page->getGenerateRssFlag(),
		"soy2prefix" => "b_block"
	));
}
