<?php
/**
 * ページ出力前の共通処理
 * @param SOYShop_Page, array
 * @return webPage
 */
function common_process_before_output(SOYShop_Page $page, array $args){
	//ページ種別によって読み込むページクラスを変える
	include_page_class($page->getType());
	if(DEBUG_MODE) count_timer("Search");
	$webPage = $page->getWebPageObject($args);	
	if(is_null($webPage)) return null; //名前無しページオブジェクトの取得を試みることがあるので処理を停止する
		
	$webPage->setArguments($args);
		
	/* Event OnLoad */
	SOYShopPlugin::load("soyshop.site.onload");
	SOYShopPlugin::invoke("soyshop.site.onload", array("page" => $webPage));

	$webPage->build($args);
	if($webPage->getError() instanceof Exception) return $webPage;

	if(DEBUG_MODE) count_timer("Build");

	$webPage->main($args);
	$webPage->common_execute();
	
	if(DEBUG_MODE) count_timer("Main");
	if(DEBUG_MODE) append_debug_info($webPage);
	return $webPage;
}

/**
 * ページ出力
 * @param WebPage $webPage
 */
function output_page(WebPage $webPage){
	/* Event BeforeOutput */
	SOYShopPlugin::load("soyshop.site.beforeoutput");
	SOYShopPlugin::invoke("soyshop.site.beforeoutput", array("page" => $webPage));

	ob_start();
	$webPage->display();
	$html = ob_get_contents();
	ob_end_clean();

	if(DEBUG_MODE) count_timer("Render");
	if(DEBUG_MODE) replace_render_time($html);

	/* EVENT onOutput */
	SOYShopPlugin::load("soyshop.site.onoutput");
	echo  SOYShopPlugin::invoke("soyshop.site.onoutput", array("html" => $html, "page" => $webPage))->getHtml();
}
