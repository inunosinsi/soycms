<?php
/*
 * AjaxPage.php
 * Created: 2010/02/18
 */
class AjaxPage extends WebPage {

	function __construct($args){
		if(count($args) < 1)exit;

		$action = $args[0];

		if(method_exists($this,"get" . ucwords($action))){
			echo call_user_func(array($this,"get" . ucwords($action)));
			exit;
		}

	}

	function getCategories(){
		SOY2HTMLFactory::importWebPage("_base.TreeComponent");
		echo "<ul>" . TreeComponent::buildTree(soyshop_get_category_objects()) . "</ul>";
	}
}
