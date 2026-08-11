<?php
SOY2::import("logic.site.page.PageLogic");

class PageRemoveLogic extends PageLogic{

	private $errors = array();

    function remove($id){
    	$uri = soyshop_get_page_object($id)->getUri();
    	$replace = str_replace(array("-", "/", "."), "_", $uri);
    	$page = SOYSHOP_SITE_DIRECTORY . ".page/" . $replace . "_page" ;

    	@unlink($page . ".php");
    	@unlink($page . ".conf");

    	SOY2DAOFactory::create("site.SOYShop_PageDAO")->delete($id);
    }
}
