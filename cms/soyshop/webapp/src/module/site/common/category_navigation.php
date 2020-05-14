<?php
/**
 * 20091126 新規作成
 *
 */
function soyshop_category_navigation(){
	try{
		$categories = SOY2DAOFactory::create("shop.SOYShop_CategoryDAO")->getByIsOpen(SOYShop_Category::IS_OPEN);
	}catch(Exception $e){
		$categories = array();
	}

	if(!count($categories)) return "";

	$tree = array();
	$root = array();

	foreach($categories as $obj){
		if($obj->getParent()){
			$parent = $obj->getParent();
			if(!isset($tree[$parent]))$tree[$parent] = array();
			$tree[$parent][] = $obj;
		}else{
			$root[] = $obj;
		}
	}

	//設定の読み込み
	$args = array(
		"config" => SOYShop_DataSets::get("common.category_navigation", array()),
		"urls" => SOYShop_DataSets::get("site.url_mapping", array())
	);

	return soyshop_category_navigation_build_tree($args,$root,$tree);

}

function soyshop_category_navigation_build_tree($args,$array,$tree){
	$html = array();

	$config = $args["config"];
	$urls = $args["urls"];

	$defaultUrl = "";
	foreach($urls as $map){
		if($map["type"] == "list"){
			$defaultUrl = $map["uri"];
			break;
		}
	}

	foreach($array as $obj){
		$id = (isset($config[$obj->getId()])) ? $config[$obj->getId()]["id"] : null;
		$parameter = (isset($config[$obj->getId()])) ? $config[$obj->getId()]["parameter"] : null;

		$url = (!isset($urls[$id])) ? $defaultUrl : $urls[$id]["uri"];
		$href = soyshop_get_page_url($url,$obj->getAlias());
		if(strlen($parameter)){
			$href .= "?" . $parameter;
		}

		$html[] = '<li><a href="'.$href.'" >' . $obj->getName() . '</a>';
		if(isset($tree[$obj->getId()])){
			$html[] = '<ul>';
			$html[] = soyshop_category_navigation_build_tree($args,$tree[$obj->getId()],$tree)."\n";
			$html[] = '</ul>';
		}
		$html[] = "</li>\n";
	}

	return implode("",$html);
}
