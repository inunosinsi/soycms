<?php
/**
 * ページ出力
 * @param String $uri
 * @param Array $args
 * @param WebPage $page
 */
function output_page($uri, $args, $page){
    if(DEBUG_MODE) count_timer("Search");

    $webPage = $page->getWebPageObject($args);
	$webPage->setArguments($args);

    /* Event OnLoad */
    SOYShopPlugin::load("soyshop.site.onload");
    SOYShopPlugin::invoke("soyshop.site.onload", array("page" => $webPage));

	$webPage->build($args);
    if(DEBUG_MODE) count_timer("Build");

    $webPage->main($args);
    $webPage->common_execute();

    if(DEBUG_MODE) count_timer("Main");
    if(DEBUG_MODE) append_debug_info($webPage);

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
    $delegate = SOYShopPlugin::invoke("soyshop.site.onoutput", array("html" => $html, "page" => $webPage));
    $html = $delegate->getHtml();

    echo $html;
}
