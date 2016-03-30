<?php
/*
 * AjaxPage.php
 * Created: 2010/02/18
 */
class AjaxPage extends WebPage {

	function AjaxPage($args){
		if(count($args) < 1)exit;

		$action = $args[0];

		if(method_exists($this,"get" . ucwords($action))){
			echo call_user_func(array($this,"get" . ucwords($action)));
			exit;
		}

	}

	function getCategories(){
		SOY2HTMLFactory::importWebPage("_base.TreeComponent");

		$dao = SOY2DAOFactory::create("shop.SOYShop_CategoryDAO");
		$array = $dao->get();

		echo "<ul>" . TreeComponent::buildTree($array) . "</ul>";

	}

}